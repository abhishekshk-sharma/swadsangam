<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Order, OrderItem, RestaurantTable, MenuItem, MenuCategory, Branch, Employee, OrderAssignmentLog};
use Illuminate\Http\Request;

class OrderController extends BaseAdminController
{
    private function branchScope($query, ?int $branchId): void
    {
        $branchId ? $query->where('branch_id', $branchId) : $query->whereNull('branch_id');
    }

    private function resolvedBranchId(Request $request): ?int
    {
        if ($request->filled('branch_id')) {
            $id = (int) $request->branch_id;
            session(['admin_branch_id' => $id]);
            return $id;
        }
        return session('admin_branch_id') ? (int) session('admin_branch_id') : null;
    }

    public function index(Request $request)
    {
        $branchId = $this->resolvedBranchId($request);

        $orders = Order::with(['table.category', 'user', 'assignedTo', 'items' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->whereDate('created_at', today())
            ->whereNotIn('status', ['paid', 'checkout'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId), fn($q) => $q)
            ->latest()
            ->get();

        // Payment section: served/checkout for dine-in, ready for parcel
        $paymentOrders = Order::with(['table.category', 'branch.gstSlab', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->whereDate('created_at', today())
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            })
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId), fn($q) => $q)
            ->latest()
            ->get();

        $menuItems = MenuItem::where('is_available', true)->get();
        $branches  = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();

        // UPI ID and GST for the selected branch
        $selectedBranch = $branchId ? Branch::with('gstSlab')->find($branchId) : null;
        $branchUpiId    = $selectedBranch?->upi_id;
        $branchGst      = $this->computeBranchGst($selectedBranch);

        // Build a map of branch_id => waiters for the assign modal
        $branchIds = $orders->pluck('branch_id')->filter()->unique()->values();
        $waitersByBranch = Employee::where('tenant_id', $this->tenantId())
            ->where('role', 'waiter')
            ->where('is_active', true)
            ->whereIn('branch_id', $branchIds)
            ->get()
            ->groupBy('branch_id')
            ->map(fn($w) => $w->map(fn($e) => ['id' => $e->id, 'name' => $e->name]));

        return view('admin.orders.index', compact('orders', 'paymentOrders', 'menuItems', 'branches', 'branchId', 'waitersByBranch', 'branchUpiId', 'branchGst'));
    }

    public function create(Request $request)
    {
        $branchId   = $this->resolvedBranchId($request);
        $preTableId = $request->filled('table_id') ? (int) $request->table_id : null;

        $allTables = RestaurantTable::with(['category', 'orders' => fn($q) => $q->whereIn('status', ['pending','preparing','ready','served'])->latest()->limit(1)])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get()
            ->sort(function($a, $b) {
                preg_match('/^(\D*)(\d*)(.*)$/', $a->table_number, $am);
                preg_match('/^(\D*)(\d*)(.*)$/', $b->table_number, $bm);
                $prefixCmp = strcmp($am[1], $bm[1]);
                if ($prefixCmp !== 0) return $prefixCmp;
                $numA = (int)($am[2] ?? 0);
                $numB = (int)($bm[2] ?? 0);
                if ($numA !== $numB) return $numA - $numB;
                return strcmp($am[3] ?? '', $bm[3] ?? '');
            })
            ->groupBy(fn($t) => $t->category->name ?? 'Uncategorized');

        $menuItems      = MenuItem::with('menuCategory')->where('is_available', true)->get();
        $menuCategories = MenuCategory::whereHas('menuItems', fn($q) => $q->where('is_available', true))->get();
        $branches       = Branch::where('tenant_id', $this->tenantId())->where('is_active', true)->get();

        return view('admin.orders.create', compact('allTables', 'menuItems', 'menuCategories', 'branches', 'branchId', 'preTableId'));
    }

    public function store(Request $request)
    {
        $isParcel = $request->boolean('is_parcel');
        $branchId = $this->resolvedBranchId($request);

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
            'user_id'        => auth()->guard('admin')->id() ?? auth()->guard('employee')->id(),
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

        return redirect()->route('admin.orders.index', $branchId ? ['branch_id' => $branchId] : [])
            ->with('success', 'Order #' . $order->id . ' created.');
    }

    public function assign(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);

        abort_if(in_array($order->status, ['paid', 'cancelled', 'checkout']), 422);

        $request->validate([
            'to_user_id' => 'required|exists:employees,id',
            'note'       => 'nullable|string|max:255',
        ]);

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

    public function addItems(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);

        if ($order->status === 'paid') {
            return back()->with('error', 'Cannot add items to a paid order.');
        }

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

    public function updateItem(Request $request, $id)
    {
        $item = $this->findTenantItem($id);
        if (in_array($item->status, ['cancelled', 'paid']) || in_array($item->order->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Cannot edit this item.');
        }
        $request->validate(['quantity' => 'required|integer|min:1', 'notes' => 'nullable|string|max:500']);
        $oldTotal = $item->price * $item->quantity;
        $item->update(['quantity' => $request->quantity, 'notes' => $request->notes]);
        $item->order->increment('total_amount', ($item->price * $request->quantity) - $oldTotal);
        return back()->with('success', 'Item updated.');
    }

    public function cancelItem($id)
    {
        $item = $this->findTenantItem($id);
        if (in_array($item->order->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel item on this order.');
        }
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        $this->syncOrderStatus($item->order);
        return back()->with('success', 'Item cancelled.');
    }

    public function cancelOrder($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        if ($order->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid order.');
        }
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }
        return back()->with('success', 'Order cancelled.');
    }

    public function processPayment(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);

        if ($order->is_parcel) {
            abort_if(!in_array($order->status, ['ready', 'served', 'checkout']), 422);
        } else {
            abort_if(!in_array($order->status, ['served', 'checkout']), 422);
        }

        $request->validate([
            'payment_mode'  => 'required|in:cash,upi,card',
            'cash_received' => 'nullable|numeric|min:0',
            'grand_total'   => 'nullable|numeric|min:0',
        ]);

        // Verify grand total matches expected (GST check)
        if ($request->filled('grand_total')) {
            $branch      = $order->branch;
            $slab        = $branch?->gstSlab;
            $mode        = $branch?->gst_mode;
            $base        = (float) $order->total_amount;
            $expected    = $base;
            if ($slab && $mode === 'excluded') {
                $expected = round($base + ($base * $slab->cgst_rate / 100) + ($base * $slab->sgst_rate / 100), 2);
            }
            $submitted = round((float) $request->grand_total, 2);
            if (abs($submitted - $expected) > 0.02) {
                return response()->json(['success' => false, 'message' => 'Bill total mismatch. Please refresh and try again.'], 422);
            }
        }

        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at'      => now(),
            'cashier_id'   => auth()->guard('admin')->id() ?? auth()->guard('employee')->id(),
        ]);

        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }

        $billUrl = \Illuminate\Support\Facades\URL::signedRoute('bill.show', ['orderId' => $order->id]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'bill_url' => $billUrl, 'order_id' => $order->id]);
        }

        return redirect()->route('admin.orders.index', array_filter(['branch_id' => $this->resolvedBranchId($request)]))
            ->with('success', 'Payment received for Order #' . $order->id);
    }

    private function computeBranchGst(?Branch $branch): array
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

    private function findTenantItem(int $id): OrderItem
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== $this->tenantId(), 403);
        return $item;
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
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }
}
