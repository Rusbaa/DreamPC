<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CompatibilityEngine;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;
    protected CompatibilityEngine $compatibilityEngine;

    public function __construct(CartService $cartService, CompatibilityEngine $compatibilityEngine)
    {
        $this->cartService = $cartService;
        $this->compatibilityEngine = $compatibilityEngine;
    }

    /**
     * Display the interactive shopping cart manager view.
     */
    public function index()
    {
        $cart = $this->cartService->getOrCreateCart();
        $cart->load(['items.product.category']);

        $productIds = $cart->items->pluck('product_id')->toArray();
        $compatCheck = $this->compatibilityEngine->checkCompatibility($productIds);

        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $tax = round($subtotal * 0.05, 2);
        $shipping = count($productIds) > 0 ? 15.00 + (count($productIds) - 1) * 2.00 : 0.00;
        $total = $subtotal + $tax + $shipping;

        // Fetch compatible alternatives for each item in cart
        $alternativesMap = [];
        foreach ($cart->items as $item) {
            $alternativesMap[$item->id] = $this->cartService->getCompatibleAlternatives($item->product_id);
        }

        return view('cart.index', compact('cart', 'compatCheck', 'subtotal', 'tax', 'shipping', 'total', 'alternativesMap'));
    }

    /**
     * Add single product or entire build array to the cart.
     */
    public function add(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        if (is_numeric($productIds)) {
            $productIds = [$productIds];
        }

        if (empty($productIds) && $request->has('product_id')) {
            $productIds = [$request->input('product_id')];
        }

        $quantity = max(1, (int)$request->input('quantity', 1));

        $this->cartService->addProductsToCart($productIds, $quantity);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Added to cart successfully.']);
        }

        return redirect()->route('cart.index')->with('success', 'Build added to cart successfully!');
    }

    /**
     * Update cart item quantity via AJAX.
     */
    public function update(Request $request, int $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $updatedItem = $this->cartService->updateQuantity($itemId, (int)$request->quantity);

        $cart = $this->cartService->getOrCreateCart();
        $cart->load(['items.product']);
        
        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $tax = round($subtotal * 0.05, 2);
        $shipping = count($cart->items) > 0 ? 15.00 + (count($cart->items) - 1) * 2.00 : 0.00;
        $total = $subtotal + $tax + $shipping;

        return response()->json([
            'status' => 'success',
            'item_quantity' => $updatedItem ? $updatedItem->quantity : 0,
            'item_subtotal' => $updatedItem ? '$' . number_format($updatedItem->quantity * $updatedItem->unit_price, 2) : '$0.00',
            'cart_subtotal' => '$' . number_format($subtotal, 2),
            'cart_tax' => '$' . number_format($tax, 2),
            'cart_shipping' => '$' . number_format($shipping, 2),
            'cart_total' => '$' . number_format($total, 2),
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(int $itemId)
    {
        $this->cartService->removeItem($itemId);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Swap item with a compatible alternative.
     */
    public function swap(Request $request, int $itemId)
    {
        $request->validate([
            'new_product_id' => 'required|integer|exists:products,id',
        ]);

        $this->cartService->swapItem($itemId, (int)$request->new_product_id);

        return redirect()->route('cart.index')->with('success', 'Component swapped with compatible alternative!');
    }
}
