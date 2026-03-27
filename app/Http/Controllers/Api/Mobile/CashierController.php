<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Events\{OrderCreated, OrderStatusUpdated};
use App\Models\{Order, OrderItem, MenuItem, MenuCategory};
use Illuminate\Http\Request;

class CashierController extends Controller
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

    private function branchScope($query): void
    {
        $b = $this->branchId();
        $b ? $query->where('branch_id', $b) : $query->whereNull('branch_id');
    }

    // GET /api/mobile/cashier/payments
    public function payments()
    {
        $orders = Order::with('table.category', 'orderItems.menuItem')
            ->where('tenant_id', $this->tenantId())
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            })
            ->where(fn($q) => $this->branchScope($q))
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $branchUpiId = null;
        if ($this->branchId()) {
            $branchUpiId = \App\Models\Branch::find($this->branchId())?->upi_id;
        }

        return response()->json([
            'upi_id' => $branchUpiId,
            'orders' => $orders->map(fn($o) => $this->formatOrder($o)),
        ]);
    }

    // PATCH /api/mobile/cashier/payments/{id}/process
    public function processPayment(Request $request, int $id)
    {
        $order = Order::where('id', $id)->where('tenant_id', $this->tenantId())->firstOrFail();

        if ($order->is_parcel) {
            abort_if(!in_array($order->status, ['ready', 'served', 'checkout']), 422);
        } else {
            abort_if(!in_array($order->status, ['served', 'checkout']), 422);
        }

        $request->validate([
            'payment_mode'  => 'required|in:cash,upi,card',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at'      => now(),
            'cashier_id'   => $this->employee()->id,
        ]);

        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }

        event(new OrderStatusUpdated($order, 'served'));

        $change = null;
        if ($request->payment_mode === 'cash' && $request->cash_received) {
            $change = $request->cash_received - $order->total_amount;
        }

        return response()->json([
            'message' => 'Payment processed.',
            'change'  => $change,
            'order'   => $this->formatOrder($order->fresh()),
        ]);
    }

    // GET /api/mobile/cashier/payments/history
    public function history()
    {
        $orders = Order::with('table', 'orderItems.menuItem')
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'paid')
            ->where(fn($q) => $this->branchScope($q))
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json($orders->map(fn($o) => $this->formatOrder($o)));
    }

    // GET /api/mobile/cashier/parcels
    public function parcels()
    {
        $orders = Order::with('orderItems.menuItem')
            ->where('tenant_id', $this->tenantId())
            ->where('is_parcel', true)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->where(fn($q) => $this->branchScope($q))
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json($orders->map(fn($o) => $this->formatOrder($o)));
    }

    // POST /api/mobile/cashier/parcels
    public function storeParcel(Request $request)
    {
        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.notes'        => 'nullable|string|max:500',
            'customer_notes'       => 'nullable|string|max:500',
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $total += $menuItem->price * $item['quantity'];
        }

        $order = Order::create([
            'tenant_id'      => $this->tenantId(),
            'branch_id'      => $this->branchId(),
            'table_id'       => null,
            'user_id'        => $this->employee()->id,
            'status'         => 'pending',
            'total_amount'   => $total,
            'customer_notes' => $request->customer_notes,
            'is_parcel'      => true,
        ]);

        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            OrderItem::create([
                'tenant_id'    => $this->tenantId(),
                'branch_id'    => $this->branchId(),
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity'     => $item['quantity'],
                'price'        => $menuItem->price,
                'status'       => 'pending',
                'notes'        => $item['notes'] ?? null,
            ]);
        }

        $order->load('orderItems.menuItem');
        event(new OrderCreated($order));
        return response()->json($this->formatOrder($order), 201);
    }

    // POST /api/mobile/cashier/parcels/{id}/add-items
    public function addParcelItems(Request $request, int $id)
    {
        $order = Order::where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->where('is_parcel', true)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Order already paid.'], 422);
        }

        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.notes'        => 'nullable|string|max:500',
        ]);

        $extra = 0;
        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $extra += $menuItem->price * $item['quantity'];
            OrderItem::create([
                'tenant_id'    => $this->tenantId(),
                'branch_id'    => $this->branchId(),
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity'     => $item['quantity'],
                'price'        => $menuItem->price,
                'status'       => 'pending',
                'notes'        => $item['notes'] ?? null,
            ]);
        }

        $order->update([
            'total_amount' => $order->total_amount + $extra,
            'status'       => $order->status === 'ready' ? 'preparing' : $order->status,
        ]);

        $order->refresh()->load('orderItems.menuItem');
        event(new OrderStatusUpdated($order, $order->status === 'preparing' ? 'ready' : $order->status));
        return response()->json($this->formatOrder($order));
    }

    // PATCH /api/mobile/cashier/parcels/{id}/cancel
    public function cancelParcel(int $id)
    {
        $order = Order::where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->where('is_parcel', true)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Cannot cancel a paid order.'], 422);
        }
        if ($order->orderItems()->where('status', 'prepared')->exists()) {
            return response()->json(['message' => 'Some items are already prepared.'], 422);
        }
        $oldStatus = $order->status;
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        event(new OrderStatusUpdated($order, $oldStatus));
        return response()->json(['message' => 'Parcel order cancelled.']);
    }

    // PATCH /api/mobile/cashier/parcel-items/{id}/cancel
    public function cancelParcelItem(int $id)
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        abort_if(!$item->order->is_parcel, 403);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        return response()->json(['message' => 'Item cancelled.']);
    }

    // GET /api/mobile/cashier/menu
    public function menu()
    {
        $items = MenuItem::with('category')
            ->where('tenant_id', $this->tenantId())
            ->where('is_available', true)
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'name'        => $i->name,
                'description' => $i->description,
                'price'       => $i->price,
                'image'       => $i->image ? asset($i->image) : null,
                'category'    => $i->category?->name,
            ]);

        return response()->json($items);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'status'         => $order->status,
            'is_parcel'      => $order->is_parcel,
            'total_amount'   => $order->total_amount,
            'payment_mode'   => $order->payment_mode,
            'paid_at'        => $order->paid_at,
            'customer_notes' => $order->customer_notes,
            'created_at'     => $order->created_at,
            'table'          => $order->table ? [
                'id'           => $order->table->id,
                'table_number' => $order->table->table_number,
            ] : null,
            'items' => ($order->items ?? $order->orderItems)->map(fn($i) => [
                'id'       => $i->id,
                'name'     => $i->menuItem?->name,
                'quantity' => $i->quantity,
                'price'    => $i->price,
                'status'   => $i->status,
                'notes'    => $i->notes,
            ]),
        ];
    }
}
