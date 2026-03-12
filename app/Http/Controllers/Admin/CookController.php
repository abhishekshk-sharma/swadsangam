<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Events\OrderStatusUpdated;

class CookController extends Controller
{
    public function index()
    {
        $orders = Order::with(['table', 'items.menuItem'])
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served', 'paid'])
            ->orderBy('created_at', 'asc')
            ->get();
        return view('admin.cook.index', compact('orders'));
    }

    public function startPreparing($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'preparing']);
        
        event(new OrderStatusUpdated($order, 'pending'));
        
        return response()->json(['success' => true]);
    }

    public function markReady($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'ready']);
        
        event(new OrderStatusUpdated($order, 'preparing'));
        
        return response()->json(['success' => true]);
    }

    public function markServed($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'served']);
        
        event(new OrderStatusUpdated($order, 'ready'));
        
        return response()->json(['success' => true]);
    }
}
