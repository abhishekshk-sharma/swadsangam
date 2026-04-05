<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Events\OrderStatusUpdated;
use App\Models\{Order, OrderItem, Employee};
use Illuminate\Http\Request;

class ChefController extends Controller
{
    private function employee()
    {
        return request()->user();
    }

    private function tenantId(): int
    {
        return (int) $this->employee()->tenant_id;
    }

    private function branchId(): ?int
    {
        return $this->employee()->branch_id ?? null;
    }

    private function waiterIds(): array
    {
        $b = $this->branchId();
        return Employee::withoutGlobalScopes()
            ->whereIn('role', ['waiter', 'manager', 'cashier'])
            ->where('tenant_id', $this->tenantId())
            ->where(fn($q) => $b ? $q->where('branch_id', $b) : $q->whereNull('branch_id'))
            ->pluck('id')->all();
    }

    // GET /api/mobile/chef/orders/pending
    public function pending()
    {
        $orders = Order::with('table.category', 'orderItems.menuItem')
            ->whereIn('status', ['pending', 'preparing'])
            ->whereIn('user_id', $this->waiterIds())
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json($orders->map(fn($o) => $this->formatOrder($o)));
    }

    // GET /api/mobile/chef/orders/completed
    public function completed()
    {
        $orders = Order::with('table.category', 'orderItems.menuItem')
            ->where('status', 'ready')
            ->whereIn('user_id', $this->waiterIds())
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json($orders->map(fn($o) => $this->formatOrder($o)));
    }

    // PATCH /api/mobile/chef/order-items/{id}/status
    public function updateItemStatus(Request $request, int $id)
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);

        $request->validate(['status' => 'required|in:pending,prepared']);
        $item->update(['status' => $request->status]);

        $order    = $item->order;
        $oldStatus = $order->status;

        $allPrepared = $order->orderItems()
            ->whereNotIn('status', ['prepared', 'cancelled'])
            ->count() === 0;

        if ($allPrepared) {
            $order->update(['status' => 'ready']);
            event(new OrderStatusUpdated($order, $oldStatus));
        } elseif (in_array($order->status, ['pending', 'ready'])) {
            $order->update(['status' => 'preparing']);
            event(new OrderStatusUpdated($order, $oldStatus));
        }

        return response()->json(['message' => 'Item status updated.', 'order_status' => $order->fresh()->status]);
    }

    // PATCH /api/mobile/chef/orders/{id}/ready
    public function markOrderReady(int $id)
    {
        $order = Order::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Cannot change a paid order.'], 422);
        }

        // Mark all remaining pending items as prepared
        $order->orderItems()->where('status', 'pending')->update(['status' => 'prepared']);

        $oldStatus = $order->status;
        $order->update(['status' => 'ready']);
        event(new OrderStatusUpdated($order, $oldStatus));

        return response()->json(['message' => 'Order marked as ready.']);
    }

    // PATCH /api/mobile/chef/orders/{id}/cancel
    public function cancelOrder(int $id)
    {
        $order = Order::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();
        if ($order->status === 'paid') {
            return response()->json(['message' => 'Cannot cancel a paid order.'], 422);
        }
        if ($order->orderItems()->where('status', 'prepared')->exists()) {
            return response()->json(['message' => 'Some items are already prepared.'], 422);
        }
        $oldStatus = $order->status;
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        event(new OrderStatusUpdated($order, $oldStatus));
        return response()->json(['message' => 'Order cancelled.']);
    }

    // PATCH /api/mobile/chef/order-items/{id}
    public function updateItem(Request $request, int $id)
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        if ($item->status !== 'pending') {
            return response()->json(['message' => 'Only pending items can be edited.'], 422);
        }
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes'    => 'nullable|string|max:500',
        ]);
        $oldTotal = $item->price * $item->quantity;
        $item->update(['quantity' => $request->quantity, 'notes' => $request->notes]);
        $item->order->increment('total_amount', ($item->price * $request->quantity) - $oldTotal);
        return response()->json(['message' => 'Item updated.']);
    }

    // PATCH /api/mobile/chef/order-items/{id}/cancel
    public function cancelItem(int $id)
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        $this->syncOrderStatus($item->order);
        return response()->json(['message' => 'Item cancelled.']);
    }

    private function syncOrderStatus(Order $order): void
    {
        $order->refresh();
        $nonCancelled = $order->orderItems()->where('status', '!=', 'cancelled');
        if ($nonCancelled->count() === 0) {
            $order->update(['status' => 'cancelled']);
            if (!$order->is_parcel && $order->table) {
                $order->table->update(['is_occupied' => false]);
            }
        } elseif ($nonCancelled->where('status', '!=', 'prepared')->count() === 0) {
            $order->update(['status' => 'ready']);
            event(new OrderStatusUpdated($order, 'preparing'));
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'daily_number'   => $order->daily_number ?? $order->id,
            'status'         => $order->status,
            'is_parcel'      => (bool) $order->is_parcel,
            'total_amount'   => (float) $order->total_amount,
            'customer_notes' => $order->customer_notes,
            'created_at'     => $order->created_at,
            'table'          => $order->table ? [
                'id'           => $order->table->id,
                'table_number' => $order->table->table_number,
                'category'     => $order->table->category?->name,
            ] : null,
            'items' => $order->orderItems->map(fn($i) => [
                'id'       => $i->id,
                'name'     => $i->menuItem?->name,
                'quantity' => $i->quantity,
                'price'    => (float) $i->price,
                'subtotal' => (float) ($i->price * $i->quantity),
                'status'   => $i->status,
                'notes'    => $i->notes,
            ])->values(),
        ];
    }
}
