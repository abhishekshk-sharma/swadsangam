<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function pending()
    {
        $orders = Order::with(['table', 'orderItems' => function($query) {
                $query->where('status', 'pending');
            }, 'orderItems.menuItem'])
            ->whereHas('orderItems', function($query) {
                $query->where('status', 'pending');
            })
            ->where('status', '!=', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cook.orders.pending', compact('orders'));
    }

    public function processing()
    {
        $orders = Order::with(['table', 'orderItems' => function($query) {
                $query->where('status', 'preparing');
            }, 'orderItems.menuItem'])
            ->whereHas('orderItems', function($query) {
                $query->where('status', 'preparing');
            })
            ->where('status', '!=', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cook.orders.processing', compact('orders'));
    }

    public function completed()
    {
        $orders = Order::with(['table', 'orderItems' => function($query) {
                $query->where('status', 'ready');
            }, 'orderItems.menuItem'])
            ->whereHas('orderItems', function($query) {
                $query->where('status', 'ready');
            })
            ->where('status', '!=', 'paid')
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cook.orders.completed', compact('orders'));
    }

    public function updateItemStatus(Request $request, OrderItem $orderItem)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready'
        ]);

        $orderItem->update(['status' => $request->status]);

        // Update order status based on all items
        $order = $orderItem->order;
        $allItemsReady = $order->orderItems()->where('status', '!=', 'ready')->count() === 0;
        
        if ($allItemsReady) {
            $order->update(['status' => 'ready']);
        } elseif ($order->orderItems()->where('status', 'preparing')->count() > 0) {
            $order->update(['status' => 'preparing']);
        }

        return back()->with('success', 'Item status updated!');
    }

    public function updateAllItems(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready'
        ]);

        // Get current status to determine which items to update
        $currentStatus = $request->status === 'preparing' ? 'pending' : 'preparing';
        
        // Update all items with current status to new status
        $order->orderItems()->where('status', $currentStatus)->update(['status' => $request->status]);

        // Update order status
        $order->update(['status' => $request->status]);

        event(new \App\Events\OrderStatusUpdated($order, $currentStatus));

        return back()->with('success', 'All items updated!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // Update all pending items to preparing when order is marked as preparing
        if ($request->status === 'preparing') {
            $order->orderItems()->where('status', 'pending')->update(['status' => 'preparing']);
        }

        // Broadcast event
        event(new \App\Events\OrderStatusUpdated($order, $oldStatus));

        return back()->with('success', 'Order status updated!');
    }
}
