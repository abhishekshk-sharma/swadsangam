<?php

namespace App\Http\Controllers\Manager;

use App\Models\{Order, OrderItem, RestaurantTable, MenuItem, MenuCategory, Branch, Employee, OrderAssignmentLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class OrderController extends BaseManagerController
{
    // public function index(Request $request)
    // {
    //     $branchId = $this->branchId();

    //     $orders = Order::with(['table.category', 'user', 'assignedTo', 'items' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
    //         ->where('tenant_id', $this->tenantId())
    //         ->whereDate('created_at', today())
    //         ->whereNotIn('status', ['paid', 'checkout'])
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->latest()
    //         ->get();

    //     $paymentOrders = Order::with(['table.category', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
    //         ->where('tenant_id', $this->tenantId())
    //         ->whereDate('created_at', today())
    //         ->where(function ($q) {
    //             $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
    //               ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
    //         })
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->latest()
    //         ->get();

    //     $menuItems   = MenuItem::where('is_available', true)->where('tenant_id', $this->tenantId())->get();
    //     $branchUpiId = $branchId ? Branch::find($branchId)?->upi_id : null;

    //     // Waiters keyed by branch_id for assign modal
    //     $waitersByBranch = Employee::where('role', 'waiter')
    //         ->where('tenant_id', $this->tenantId())
    //         ->where('is_active', true)
    //         ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
    //         ->get()
    //         ->groupBy('branch_id')
    //         ->map(fn($w) => $w->map(fn($e) => ['id' => $e->id, 'name' => $e->name]));

    //     return view('manager.orders.index', compact('orders', 'paymentOrders', 'menuItems', 'branchUpiId', 'waitersByBranch', 'branchId'));
    // }

    public function index(Request $request)
    {
        $branchId = $this->branchId();

        $orders = Order::with(['table.category', 'user', 'assignedTo', 'items' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->where('tenant_id', $this->tenantId())
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['paid', 'checkout'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        $paymentOrders = Order::with(['table.category', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->where('tenant_id', $this->tenantId())
            ->whereDate('created_at', today())
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            })
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->get();

        $menuItems   = MenuItem::where('is_available', true)->where('tenant_id', $this->tenantId())->get();
        $selectedBranch = $branchId ? \App\Models\Branch::with('gstSlab')->find($branchId) : null;
        $branchUpiId = $selectedBranch?->upi_id;
        $branchGst   = $this->computeBranchGst($selectedBranch);

        $waitersByBranch = Employee::where('role', 'waiter')
            ->where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get()
            ->groupBy('branch_id')
            ->map(fn($w) => $w->map(fn($e) => ['id' => $e->id, 'name' => $e->name]));

        return view('manager.orders.index', compact('orders', 'paymentOrders', 'menuItems', 'branchUpiId', 'branchGst', 'waitersByBranch', 'branchId'));
    }

    private function computeBranchGst(?\App\Models\Branch $branch): array
    {
        if (!$branch) return ['enabled' => false];
        $slab = $branch->gstSlab;
        $mode = $branch->gst_mode;
        if (!$slab || !$mode) return ['enabled' => false];
        return [
            'enabled'   => true,
            'mode'      => $mode,
            'cgst_pct'  => (float) $slab->cgst_rate,
            'sgst_pct'  => (float) $slab->sgst_rate,
            'total_pct' => (float) ($slab->cgst_rate + $slab->sgst_rate),
        ];
    }


    public function create(Request $request)
    {
        $branchId = $this->branchId();

        $allTables = RestaurantTable::with(['category', 'orders' => fn($q) => $q->whereIn('status', ['pending','preparing','ready','served'])->latest()->limit(1)])
            ->where('tenant_id', $this->tenantId())
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get()
            ->groupBy(fn($t) => $t->category->name ?? 'Uncategorized');

        $menuItems      = MenuItem::with('menuCategory')->where('is_available', true)->where('tenant_id', $this->tenantId())->get();
        $menuCategories = MenuCategory::whereHas('menuItems', fn($q) => $q->where('is_available', true))->get();

        return view('manager.orders.create', compact('allTables', 'menuItems', 'menuCategories', 'branchId'));
    }

    public function store(Request $request)
    {
        $isParcel = $request->boolean('is_parcel');
        $branchId = $this->branchId();

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
            'branch_id'      => $branchId,
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
                'branch_id'    => $branchId,
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

        return redirect()->route('manager.orders.index')->with('success', 'Order #' . $order->id . ' created.');
    }

    public function addItems(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);
        abort_if($order->status === 'paid', 422);

        $request->validate([
            'items'                => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.notes'        => 'nullable|string|max:500',
        ]);

        $additionalTotal = 0;
        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            $additionalTotal += $menuItem->price * $item['quantity'];
            OrderItem::create([
                'tenant_id'    => $this->tenantId(),
                'branch_id'    => $order->branch_id,
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity'     => $item['quantity'],
                'price'        => $menuItem->price,
                'status'       => 'pending',
                'notes'        => $item['notes'] ?? null,
            ]);
        }

        $order->update([
            'total_amount' => $order->total_amount + $additionalTotal,
            'status'       => in_array($order->status, ['ready', 'served']) ? 'preparing' : $order->status,
        ]);

        return back()->with('success', 'Items added to Order #' . $order->id . '.');
    }

    public function cancelOrder($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        abort_if($order->status === 'paid', 422);
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        return back()->with('success', 'Order cancelled.');
    }

    public function cancelItem($id)
    {
        $item = $this->findTenantItem($id);
        abort_if(in_array($item->order->status, ['paid', 'cancelled']), 422);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        return back()->with('success', 'Item cancelled.');
    }

    public function updateItem(Request $request, $id)
    {
        $item = $this->findTenantItem($id);
        abort_if(in_array($item->order->status, ['paid', 'cancelled']), 422);
        $request->validate(['quantity' => 'required|integer|min:1', 'notes' => 'nullable|string|max:500']);
        $oldTotal = $item->price * $item->quantity;
        $item->update(['quantity' => $request->quantity, 'notes' => $request->notes]);
        $item->order->increment('total_amount', ($item->price * $request->quantity) - $oldTotal);
        return back()->with('success', 'Item updated.');
    }

    public function assign(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);
        abort_if(in_array($order->status, ['paid', 'cancelled', 'checkout']), 422);

        $request->validate(['to_user_id' => 'required|exists:employees,id', 'note' => 'nullable|string|max:255']);

        $toUser = Employee::where('id', $request->to_user_id)
            ->where('tenant_id', $this->tenantId())
            ->where('role', 'waiter')
            ->firstOrFail();

        $fromUserId = $order->user_id;
        $order->update(['user_id' => $toUser->id, 'assigned_to' => null]);

        OrderAssignmentLog::create([
            'tenant_id'    => $this->tenantId(),
            'order_id'     => $order->id,
            'from_user_id' => $fromUserId,
            'to_user_id'   => $toUser->id,
            'note'         => $request->note,
        ]);

        return back()->with('success', 'Order #' . $order->id . ' assigned to ' . $toUser->name . '.');
    }

    public function processPayment(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);

        if ($order->is_parcel) {
            abort_if(!in_array($order->status, ['ready', 'served', 'checkout']), 422);
        } else {
            abort_if(!in_array($order->status, ['served', 'checkout']), 422);
        }

        $request->validate(['payment_mode' => 'required|in:cash,upi,card']);

        $grandTotal = $request->filled('grand_total')
            ? (float) $request->grand_total
            : (float) $order->total_amount;

        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
            'grand_total'  => $grandTotal,
            'paid_at'      => now(),
            'cashier_id'   => auth()->guard('employee')->id(),
        ]);

        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }

        $billUrl = URL::signedRoute('bill.show', ['orderId' => $order->id]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'bill_url' => $billUrl, 'order_id' => $order->id]);
        }

        return redirect()->route('manager.orders.index')->with('success', 'Payment received for Order #' . $order->id);
    }

    private function findTenantItem(int $id): OrderItem
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        return $item;
    }
}
