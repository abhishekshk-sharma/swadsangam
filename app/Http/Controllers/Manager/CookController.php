<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Events\OrderStatusUpdated;

class CookController extends BaseManagerController
{
    public function index(Request $request)
    {
        $query = Order::with(['table.category', 'items.menuItem'])
            ->whereIn('status', ['pending', 'preparing', 'ready', 'served', 'checkout', 'paid', 'cancelled'])
            ->orderBy('created_at', 'asc');

        $this->scopeBranch($query);

        if ($request->filled('table_id')) $query->where('table_id', $request->table_id);
        if ($request->filled('status'))   $query->where('status', $request->status);

        $orders = $query->get();
        return view('manager.cook.index', compact('orders'));
    }

    public function startPreparing($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $order->update(['status' => 'preparing']);
        event(new OrderStatusUpdated($order, 'pending'));
        return response()->json(['success' => true]);
    }

    public function markReady($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $order->update(['status' => 'ready']);
        event(new OrderStatusUpdated($order, 'preparing'));
        return response()->json(['success' => true]);
    }

    public function markServed($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $order->update(['status' => 'served']);
        event(new OrderStatusUpdated($order, 'ready'));
        return response()->json(['success' => true]);
    }

    public function processPayment(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $request->validate(['payment_mode' => 'required|in:cash,upi,card']);

        $order->update(['status' => 'paid', 'payment_mode' => $request->payment_mode, 'paid_at' => now()]);

        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        event(new OrderStatusUpdated($order, 'served'));

        return redirect()->route('manager.cook.index')->with('success', 'Payment received! Order closed.');
    }

    public function cancelOrder($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        if ($order->status === 'paid') return back()->with('error', 'Cannot cancel a paid order.');
        if ($order->orderItems()->where('status', 'prepared')->exists()) return back()->with('error', 'Cannot cancel — some items are already prepared.');

        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        if (!$order->is_parcel && $order->table) $order->table->update(['is_occupied' => false]);
        return back()->with('success', 'Order cancelled.');
    }

    public function cancelItem($id)
    {
        $item = $this->findTenantItem($id);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        $this->syncOrderStatus($item->order);
        return back()->with('success', 'Item cancelled.');
    }

    public function updateItem(Request $request, $id)
    {
        $item = $this->findTenantItem($id);
        if ($item->status !== 'pending') return back()->with('error', 'Only pending items can be edited.');
        $request->validate(['quantity' => 'required|integer|min:1', 'notes' => 'nullable|string|max:500']);
        $oldTotal = $item->price * $item->quantity;
        $item->update(['quantity' => $request->quantity, 'notes' => $request->notes]);
        $item->order->increment('total_amount', ($item->price * $request->quantity) - $oldTotal);
        return back()->with('success', 'Item updated.');
    }

    private function findTenantItem(int $id): OrderItem
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        return $item;
    }

    private function syncOrderStatus(Order $order)
    {
        $order->refresh();
        $nonCancelled = $order->orderItems()->where('status', '!=', 'cancelled');
        if ($nonCancelled->count() === 0) {
            $order->update(['status' => 'cancelled']);
            if (!$order->is_parcel && $order->table) $order->table->update(['is_occupied' => false]);
        } elseif ($nonCancelled->where('status', '!=', 'prepared')->count() === 0) {
            $order->update(['status' => 'ready']);
            event(new OrderStatusUpdated($order, 'preparing'));
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }
}
