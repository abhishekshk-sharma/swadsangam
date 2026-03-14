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
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served', 'paid', 'cancelled'])
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

    public function processPayment(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        
        $request->validate([
            'payment_mode' => 'required|in:cash,upi,card',
            'cash_received' => 'nullable|numeric|min:0'
        ]);

        $order->update([
            'status' => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at' => now()
        ]);

        $order->table->update(['is_occupied' => false]);

        event(new OrderStatusUpdated($order, 'served'));

        return redirect('/admin/cook')->with('success', 'Payment received! Order closed.');
    }

    public function updateItem(Request $request, $id)
    {
        $item = \App\Models\OrderItem::findOrFail($id);
        if ($item->status !== 'pending') {
            return back()->with('error', 'Only pending items can be edited.');
        }
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);
        $oldTotal = $item->price * $item->quantity;
        $item->update(['quantity' => $request->quantity, 'notes' => $request->notes]);
        $newTotal = $item->price * $request->quantity;
        $item->order->increment('total_amount', $newTotal - $oldTotal);
        return back()->with('success', 'Item updated.');
    }

    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid order.');
        }
        if ($order->orderItems()->where('status', 'prepared')->exists()) {
            return back()->with('error', 'Cannot cancel order — some items are already prepared. Cancel individual items instead.');
        }
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        $order->table->update(['is_occupied' => false]);
        return back()->with('success', 'Order cancelled.');
    }

    public function cancelItem($id)
    {
        $item = \App\Models\OrderItem::findOrFail($id);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        $this->syncOrderStatus($item->order);
        return back()->with('success', 'Item cancelled.');
    }


    private function syncOrderStatus(Order $order)
    {
        $order->refresh();
        $nonCancelled = $order->orderItems()->where('status', '!=', 'cancelled');
        if ($nonCancelled->count() === 0) {
            $order->update(['status' => 'cancelled']);
            $order->table->update(['is_occupied' => false]);
        } elseif ($nonCancelled->where('status', '!=', 'prepared')->count() === 0) {
            $order->update(['status' => 'ready']);
            event(new OrderStatusUpdated($order, 'preparing'));
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }
}
