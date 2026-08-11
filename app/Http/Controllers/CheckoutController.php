<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\SpecExtractorService;
use App\Services\StockCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected StockCheckerService $stockChecker;
    protected SpecExtractorService $specExtractor;

    public function __construct(
        CartService $cartService, 
        StockCheckerService $stockChecker,
        SpecExtractorService $specExtractor
    ) {
        $this->cartService = $cartService;
        $this->stockChecker = $stockChecker;
        $this->specExtractor = $specExtractor;
    }

    /**
     * Display the multi-step checkout form.
     */
    public function index()
    {
        $cart = $this->cartService->getOrCreateCart();
        $cart->load(['items.product.category', 'items.product.specifications']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your shopping cart is empty. Add products before checking out.');
        }

        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);

        // Calculate Coupon Discount
        $appliedCoupon = session('applied_coupon');
        $discount = 0.0;
        if ($appliedCoupon) {
            $couponCheck = app(CouponService::class)->validateAndCalculate($appliedCoupon['code'], $subtotal);
            if ($couponCheck['valid']) {
                $discount = $couponCheck['discount'];
            } else {
                session()->forget('applied_coupon');
                $appliedCoupon = null;
            }
        }

        $discountedSubtotal = max(0, $subtotal - $discount);
        $tax = round($discountedSubtotal * 0.05, 2);
        $shipping = count($cart->items) > 0 ? 15.00 + (count($cart->items) - 1) * 2.00 : 0.00;
        $total = $discountedSubtotal + $tax + $shipping;

        return view('checkout.index', compact('cart', 'subtotal', 'appliedCoupon', 'discount', 'tax', 'shipping', 'total'));
    }

    /**
     * Process checkout submission, decrement stock, and place order inside DB transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fulfillment_type' => 'required|in:delivery,pickup',
            'delivery_date' => 'required|date|after_or_equal:today',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'address' => 'required_if:fulfillment_type,delivery|nullable|string|max:500',
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $cart->load(['items.product.specifications']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);

        // Coupon calculation
        $appliedCoupon = session('applied_coupon');
        $discount = 0.0;
        if ($appliedCoupon) {
            $couponCheck = app(CouponService::class)->validateAndCalculate($appliedCoupon['code'], $subtotal);
            if ($couponCheck['valid']) {
                $discount = $couponCheck['discount'];
            }
        }

        $discountedSubtotal = max(0, $subtotal - $discount);
        $tax = round($discountedSubtotal * 0.05, 2);
        $shipping = $request->fulfillment_type === 'delivery' 
            ? (count($cart->items) > 0 ? 15.00 + (count($cart->items) - 1) * 2.00 : 0.00)
            : 0.00;
        $total = $discountedSubtotal + $tax + $shipping;

        // Resolve User ID for database foreign key constraint
        $userId = auth()->id() ?? User::first()->id ?? 1;

        try {
            $order = DB::transaction(function () use ($cart, $userId, $total, $discount, $request) {
                
                // 1. Verify Stock
                $productQuantities = $cart->items->pluck('quantity', 'product_id')->toArray();
                $stockReport = $this->stockChecker->verifyBatchStock($productQuantities);

                foreach ($stockReport as $productId => $info) {
                    if (!$info['has_enough']) {
                        throw new \Exception("Product ID {$productId} has insufficient stock.");
                    }
                }

                // 2. Create Order Record
                $order = Order::create([
                    'user_id' => $userId,
                    'total_amount' => $total,
                    'discount_amount' => $discount,
                    'fulfillment_type' => $request->fulfillment_type,
                    'delivery_date' => $request->delivery_date,
                    'status' => 'confirmed',
                ]);

                // 3. Move items to order_items & Decrement product stock
                foreach ($cart->items as $item) {
                    $specSnapshot = $this->specExtractor->getProductSpecsDictionary($item->product);

                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price_at_purchase' => $item->unit_price,
                        'spec_snapshot_json' => $specSnapshot,
                    ]);

                    // Decrement inventory stock
                    $item->product->decrement('stock_quantity', $item->quantity);
                }

                // 4. Clear Cart and Session State
                $cart->items()->delete();
                session()->forget('applied_coupon');

                return $order;
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Checkout failed: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('checkout.confirmation', $order->id)->with('success', 'Order placed successfully!');
    }

    /**
     * Display order confirmation page (Step 3).
     */
    public function confirmation(int $orderId)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($orderId);

        return view('checkout.confirmation', compact('order'));
    }
}
