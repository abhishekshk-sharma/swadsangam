<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function pending()
    {
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('cook.orders.pending', compact('orders'));
    }

    public function processing()
    {
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->where('status', 'preparing')
            ->latest()
            ->get();

        return view('cook.orders.processing', compact('orders'));
    }

    public function completed()
    {
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->where('status', 'ready')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cook.orders.completed', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Broadcast event
        event(new \App\Events\OrderStatusUpdated($order, $oldStatus));

        return back()->with('success', 'Order status updated!');
    }
}
