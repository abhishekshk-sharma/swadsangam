<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem, RestaurantTable, MenuItem, Employee, MenuCategory};
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
    private function branchId(): ?int
    {
        return auth()->guard('employee')->user()->branch_id ?? null;
    }

    private function scopeBranch($query): void
    {
        $branchId = $this->branchId();
        $branchId ? $query->where('branch_id', $branchId) : $query->whereNull('branch_id');
    }

    public function index()
    {
        $orders = Order::with(['table.category', 'items' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->whereDate('created_at', today())
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'checkout')
            ->where(fn($q) => $this->scopeBranch($q))
            ->latest()
            ->get();

        $menuItems = MenuItem::where('is_available', true)->get();

        return view('waiter.orders.index', compact('orders', 'menuItems'));
    }

    public function create()
    {
        $tables         = RestaurantTable::with('category')->where('is_occupied', false)->get()->groupBy(fn($t) => $t->category->name ?? 'Uncategorized');
        $allTables      = RestaurantTable::with('category')->get()->groupBy(fn($t) => $t->category->name ?? 'Uncategorized');
        $menuItems      = MenuItem::with('menuCategory')->where('is_available', true)->get();
        $menuCategories = MenuCategory::whereHas('menuItems', fn($q) => $q->where('is_available', true))->get();

        return view('waiter.orders.create', compact('tables', 'allTables', 'menuItems', 'menuCategories'));
    }

    public function store(Request $request)
    {
        $isParcel = $request->boolean('is_parcel');

        $request->validate([
            'table_id'             => $isParcel ? 'nullable' : 'required|exists:restaurant_tables,id',
            'items'                => 'required|array',
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
            'user_id'        => auth()->guard('employee')->id(),
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

        if (!$isParcel) {
            RestaurantTable::findOrFail($request->table_id)->update(['is_occupied' => true]);
        }

        event(new \App\Events\OrderCreated($order));
        try { $this->notifyChefs($order); } catch (\Exception $e) {}

        return redirect('/waiter/orders')->with('success', 'Order created successfully');
    }

    public function checkoutOrder($id)
    {
        $order = $this->findOrder($id);
        if ($order->status !== 'served') {
            return response()->json(['error' => 'Only served orders can be checked out.'], 422);
        }
        $order->update(['status' => 'checkout']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        return response()->json(['success' => true]);
    }

    public function markServed($id)
    {
        $order = $this->findOrder($id);
        $order->update(['status' => 'served']);
        return response()->json(['success' => true]);
    }

    protected function notifyChefs($order, $specificItems = null)
    {
        $senderBranchId = auth()->guard('employee')->user()->branch_id;

        $chefs = Employee::where('role', 'chef')
            ->where('tenant_id', $this->tenantId())
            ->where(function ($q) use ($senderBranchId) {
                if ($senderBranchId) {
                    $q->where('branch_id', $senderBranchId);
                } else {
                    $q->whereNull('branch_id');
                }
            })
            ->where('is_active', true)
            ->whereNotNull('telegram_chat_id')
            ->get();

        if ($chefs->isEmpty()) return;

        $telegram = new \App\Services\TelegramService();

        $itemsToNotify = $specificItems
            ? collect($specificItems)->map(fn($item) => [
                'name'     => $item->menuItem()->value('name') ?? $item->menu_item_id,
                'quantity' => $item->quantity,
              ])->toArray()
            : $order->items->map(fn($item) => [
                'name'     => $item->menuItem->name,
                'quantity' => $item->quantity,
              ])->toArray();

        $tableName = $order->is_parcel
            ? 'Parcel'
            : 'Table ' . ($order->table?->table_number ?? $order->table_id);

        $orderData = [
            'order_id'      => $order->id,
            'table_name'    => $tableName,
            'time'          => now()->format('h:i A'),
            'items'         => $itemsToNotify,
            'total'         => $order->total_amount,
            'is_additional' => $specificItems !== null,
        ];

        foreach ($chefs as $chef) {
            $telegram->sendOrderNotification($chef->telegram_chat_id, $orderData);
        }
    }

    public function addItems(Request $request, $id)
    {
        $order = $this->findOrder($id);

        if ($order->status === 'paid') {
            return redirect()->back()->with('error', 'This order has already been paid. Cannot add items.');
        }

        $request->validate([
            'items'                => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.notes'        => 'nullable|string|max:500',
        ]);

        $additionalTotal = 0;
        $newItems = [];

        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $additionalTotal += $menuItem->price * $item['quantity'];

            $orderItem = OrderItem::create([
                'tenant_id'    => $this->tenantId(),
                'branch_id'    => $this->branchId(),
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity'     => $item['quantity'],
                'price'        => $menuItem->price,
                'status'       => 'pending',
                'notes'        => $item['notes'] ?? null,
            ]);

            $newItems[] = $orderItem;
        }

        $order->update([
            'total_amount' => $order->total_amount + $additionalTotal,
            'status'       => in_array($order->status, ['ready', 'served']) ? 'preparing' : $order->status,
        ]);

        $order->refresh();
        try { $this->notifyChefs($order, $newItems); } catch (\Exception $e) {}

        return redirect('/waiter/orders')->with('success', 'Items added successfully');
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
        $newTotal = $item->price * $request->quantity;
        $item->order->increment('total_amount', $newTotal - $oldTotal);
        return back()->with('success', 'Item updated.');
    }

    public function cancelOrder($id)
    {
        $order = $this->findOrder($id);
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        return back()->with('success', 'Order cancelled.');
    }

    public function cancelItem($id)
    {
        $item = $this->findItem($id);
        if ($item->status !== 'pending') {
            return back()->with('error', 'Only pending items can be cancelled.');
        }
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
            if (!$order->is_parcel && $order->table) {
                $order->table->update(['is_occupied' => false]);
            }
        } elseif ($nonCancelled->where('status', '!=', 'prepared')->count() === 0) {
            $order->update(['status' => 'ready']);
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }
}
