<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private function tenantId(): int
    {
        return (int) auth()->guard('employee')->user()->tenant_id;
    }

    private function findOrder(int $id): Order
    {
        return Order::where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->firstOrFail();
    }

    private function findItem(int $id): OrderItem
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        return $item;
    }

    public function pending()
    {
        $orders = Order::with(['table', 'orderItems.menuItem'])
            ->whereIn('status', ['pending', 'preparing'])
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cook.orders.pending', compact('orders'));
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

    public function updateItemStatus(Request $request, OrderItem $orderItem)
    {
        // Global scope on Order ensures tenant isolation; double-check via order
        abort_if($orderItem->order->tenant_id !== $this->tenantId(), 403);
        $request->validate(['status' => 'required|in:pending,prepared']);

        $orderItem->update(['status' => $request->status]);

        $order = $orderItem->order;
        $oldStatus = $order->status;

        $allPrepared = $order->orderItems()
            ->where('status', '!=', 'prepared')
            ->where('status', '!=', 'cancelled')
            ->count() === 0;

        if ($allPrepared) {
            $order->update(['status' => 'ready']);
            event(new \App\Events\OrderStatusUpdated($order, $oldStatus));
        } elseif (in_array($order->status, ['pending', 'ready'])) {
            $order->update(['status' => 'preparing']);
        }

        return back()->with('success', 'Item updated!');
    }

    public function updateItem(Request $request, $id)
    {
        $item = $this->findItem($id);
        if ($item->status !== 'pending') {
            return back()->with('error', 'Only pending items can be edited.');
        }
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);
        $oldTotal = $item->price * $item->quantity;
        $item->update(['quantity' => $request->quantity, 'notes' => $request->notes]);
        $item->order->increment('total_amount', ($item->price * $request->quantity) - $oldTotal);
        return back()->with('success', 'Item updated.');
    }

    public function cancelOrder($id)
    {
        $order = $this->findOrder($id);
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
        $item = $this->findItem($id);
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
            event(new \App\Events\OrderStatusUpdated($order, 'preparing'));
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }
}
