<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderHistoryController extends Controller
{
    /**
     * Display the authenticated user's order history.
     */
    public function index()
    {
        // Resolve user ID, fallback to ID 1 for testing if not signed in
        $userId = auth()->id() ?? 1;

        $orders = Order::with(['items.product.category'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('orders.index', compact('orders'));
    }
}
