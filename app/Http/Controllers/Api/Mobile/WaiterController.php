<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem, MenuItem, MenuCategory, RestaurantTable};
use Illuminate\Http\Request;

class WaiterController extends Controller
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

    private function findOrder(int $id): Order
    {
        return Order::where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->firstOrFail();
    }

    // GET /api/mobile/waiter/orders
    public function orders()
    {
        $orders = Order::with('table.category', 'items.menuItem')
            ->where('tenant_id', $this->tenantId())
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['paid', 'checkout'])
            ->where(fn($q) => $this->branchScope($q))
            ->latest()
            ->get();

        return response()->json($orders->map(fn($o) => $this->formatOrder($o)));
    }

    // GET /api/mobile/waiter/menu
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

    // GET /api/mobile/waiter/tables
    public function tables()
    {
        $tables = RestaurantTable::with('category')
            ->where('tenant_id', $this->tenantId())
            ->where(fn($q) => $this->branchScope($q))
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'table_number' => $t->table_number,
                'capacity'     => $t->capacity,
                'is_occupied'  => $t->is_occupied,
                'category'     => $t->category?->name,
            ]);

        return response()->json($tables);
    }

    // POST /api/mobile/waiter/orders
    public function store(Request $request)
    {
        $isParcel = $request->boolean('is_parcel');

        $request->validate([
            'table_id'             => $isParcel ? 'nullable' : 'required|exists:restaurant_tables,id',
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
            'table_id'       => $isParcel ? null : $request->table_id,
            'user_id'        => $this->employee()->id,
            'status'         => 'pending',
            'total_amount'   => $total,
            'customer_notes' => $request->customer_notes,
            'is_parcel'      => $isParcel,
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

        if (!$isParcel && $request->table_id) {
            RestaurantTable::find($request->table_id)?->update(['is_occupied' => true]);
        }

        event(new \App\Events\OrderCreated($order));

        $order->load('table.category', 'items.menuItem');
        return response()->json($this->formatOrder($order), 201);
    }

    // POST /api/mobile/waiter/orders/{id}/add-items
    public function addItems(Request $request, int $id)
    {
        $order = $this->findOrder($id);

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
        $newItems = [];
        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $extra += $menuItem->price * $item['quantity'];
            $newItems[] = OrderItem::create([
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
            'status'       => in_array($order->status, ['ready', 'served']) ? 'preparing' : $order->status,
        ]);

        $order->refresh()->load('table.category', 'items.menuItem');
        return response()->json($this->formatOrder($order));
    }

    // PATCH /api/mobile/waiter/orders/{id}/serve
    public function markServed(int $id)
    {
        $order = $this->findOrder($id);
        $order->update(['status' => 'served']);
        return response()->json(['message' => 'Order marked as served.']);
    }

    // PATCH /api/mobile/waiter/orders/{id}/checkout
    public function checkout(int $id)
    {
        $order = $this->findOrder($id);
        if ($order->status !== 'served') {
            return response()->json(['message' => 'Only served orders can be checked out.'], 422);
        }
        $order->update(['status' => 'checkout']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        return response()->json(['message' => 'Order checked out.']);
    }

    // PATCH /api/mobile/waiter/orders/{id}/cancel
    public function cancelOrder(int $id)
    {
        $order = $this->findOrder($id);
        if ($order->status === 'paid') {
            return response()->json(['message' => 'Cannot cancel a paid order.'], 422);
        }
        if ($order->orderItems()->where('status', 'prepared')->exists()) {
            return response()->json(['message' => 'Some items are already prepared.'], 422);
        }
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        return response()->json(['message' => 'Order cancelled.']);
    }

    // PATCH /api/mobile/waiter/order-items/{id}/cancel
    public function cancelItem(int $id)
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        return response()->json(['message' => 'Item cancelled.']);
    }

    // PATCH /api/mobile/waiter/order-items/{id}
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

    private function formatOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'status'         => $order->status,
            'is_parcel'      => $order->is_parcel,
            'total_amount'   => $order->total_amount,
            'customer_notes' => $order->customer_notes,
            'created_at'     => $order->created_at,
            'table'          => $order->table ? [
                'id'           => $order->table->id,
                'table_number' => $order->table->table_number,
                'category'     => $order->table->category?->name,
            ] : null,
            'items' => $order->items->map(fn($i) => [
                'id'        => $i->id,
                'name'      => $i->menuItem?->name,
                'quantity'  => $i->quantity,
                'price'     => $i->price,
                'status'    => $i->status,
                'notes'     => $i->notes,
            ]),
        ];
    }
}
