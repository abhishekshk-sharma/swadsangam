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
        $branch = $this->branchId() ? \App\Models\Branch::with('gstSlab')->find($this->branchId()) : null;

        $orders = Order::with(['table.category', 'orderItems.menuItem', 'branch.gstSlab'])
            ->where('tenant_id', $this->tenantId())
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            })
            ->where(fn($q) => $this->branchScope($q))
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json([
            'upi_id'       => $branch?->upi_id,
            'instant_mode' => false,
            'orders'       => $orders->map(fn($o) => $this->formatOrder($o)),
        ]);
    }

    // GET /api/mobile/cashier/payments/instant
    // Returns ALL today's non-paid orders — bypasses kitchen flow
    public function paymentsInstant()
    {
        $branch = $this->branchId() ? \App\Models\Branch::with('gstSlab')->find($this->branchId()) : null;

        $orders = Order::with(['table.category', 'orderItems.menuItem', 'branch.gstSlab'])
            ->where('tenant_id', $this->tenantId())
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->where(fn($q) => $this->branchScope($q))
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return response()->json([
            'upi_id'       => $branch?->upi_id,
            'instant_mode' => true,
            'orders'       => $orders->map(fn($o) => $this->formatOrder($o)),
        ]);
    }

    // PATCH /api/mobile/cashier/payments/{id}/process
    public function processPayment(Request $request, $id)
    {
        $order = Order::with('branch.gstSlab')
            ->where('id', $id)
            ->where('tenant_id', $this->tenantId())
            ->firstOrFail();

        if ($order->is_parcel) {
            abort_if(!in_array($order->status, ['pending', 'preparing', 'ready', 'served', 'checkout']), 422);
        } else {
            abort_if(!in_array($order->status, ['pending', 'preparing', 'ready', 'served', 'checkout']), 422);
        }

        $request->validate([
            'payment_mode'  => 'required|in:cash,upi',
            'cash_received' => 'nullable|numeric|min:0',
            'grand_total'   => 'nullable|numeric|min:0',
        ]);

        // Verify grand_total matches expected (GST check)
        if ($request->filled('grand_total')) {
            $gst      = $this->computeGst($order);
            $expected = $gst['enabled'] ? $gst['grand'] : (float) $order->total_amount;
            if (abs(round((float) $request->grand_total, 2) - round($expected, 2)) > 0.02) {
                return response()->json(['message' => 'Bill total mismatch. Please refresh and try again.'], 422);
            }
        }

        $cashierId = $this->employee()->id;
        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at'      => now(),
            'cashier_id'   => \App\Models\Employee::withoutGlobalScopes()->where('id', $cashierId)->exists() ? $cashierId : null,
        ]);

        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }

        event(new OrderStatusUpdated($order, 'served'));

        $gst    = $this->computeGst($order->fresh('branch.gstSlab'));
        $change = null;
        if ($request->payment_mode === 'cash' && $request->filled('cash_received')) {
            $grand  = $gst['enabled'] ? $gst['grand'] : (float) $order->total_amount;
            $change = round((float) $request->cash_received - $grand, 2);
        }

        $billUrl = \Illuminate\Support\Facades\URL::signedRoute('bill.show', ['orderId' => $order->id]);

        return response()->json([
            'message'  => 'Payment processed.',
            'change'   => $change,
            'bill_url' => $billUrl,
            'order'    => $this->formatOrder($order->fresh()),
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
    public function addParcelItems(Request $request, $id)
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
    public function cancelParcel($id)
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
    public function cancelParcelItem($id)
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
        $tenantId = $this->tenantId();
        $branchId = $this->branchId();

        $categories = MenuCategory::withoutGlobalScopes()
            ->with(['menuItems' => function ($q) use ($tenantId, $branchId) {
                $q->where('tenant_id', $tenantId)
                  ->where('is_available', true)
                  ->when($branchId, fn($q2) =>
                      $q2->where(fn($q3) => $q3->whereNull('branch_id')->orWhere('branch_id', $branchId))
                  );
            }])
            ->where(fn($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))
            ->when($branchId, fn($q) =>
                $q->where(fn($q2) => $q2->whereNull('branch_id')->orWhere('branch_id', $branchId))
            )
            ->orderByRaw('COALESCE(sort_order, 9999)')
            ->get()
            ->filter(fn($cat) => $cat->menuItems->isNotEmpty());

        $result = [];
        foreach ($categories as $cat) {
            foreach ($cat->menuItems as $i) {
                $result[] = [
                    'id'                  => $i->id,
                    'name'                => $i->name,
                    'description'         => $i->description,
                    'price'               => $i->price,
                    'image'               => $i->image ? asset($i->image) : null,
                    'category'            => $cat->name,
                    'category_id'         => $cat->id,
                    'category_sort_order' => $cat->sort_order ?? 9999,
                ];
            }
        }

        return response()->json($result);
    }

    // GET /api/mobile/cashier/bill/{orderId}
    public function bill($orderId)
    {
        $order = Order::withoutGlobalScope('tenant')
            ->with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()]), 'branch.gstSlab'])
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'paid')
            ->findOrFail($orderId);

        $gst     = $this->computeGst($order);
        $billUrl = \Illuminate\Support\Facades\URL::signedRoute('bill.show', ['orderId' => $order->id]);

        return response()->json([
            'order'    => $this->formatOrder($order),
            'bill_url' => $billUrl,
            'gst'      => $gst,
        ]);
    }

    private function formatOrder(Order $order): array
    {
        $gst    = $this->computeGst($order);
        $branch = $order->branch ?? ($this->branchId() ? \App\Models\Branch::find($this->branchId()) : null);

        // Build UPI QR URI if UPI is configured
        $upiId  = $branch?->upi_id;
        $grand  = $gst['enabled'] ? $gst['grand'] : (float) $order->total_amount;
        $upiUri = $upiId
            ? 'upi://pay?pa=' . urlencode($upiId) . '&am=' . number_format($grand, 2, '.', '') . '&cu=INR'
            : null;

        return [
            'id'             => $order->id,
            'daily_number'   => $order->daily_number ?? $order->id,
            'status'         => $order->status,
            'is_parcel'      => (bool) $order->is_parcel,
            'subtotal'       => (float) $order->total_amount,
            'grand_total'    => $grand,
            'payment_mode'   => $order->payment_mode,
            'paid_at'        => $order->paid_at,
            'customer_notes' => $order->customer_notes,
            'created_at'     => $order->created_at,
            'upi_id'         => $upiId,
            'upi_uri'        => $upiUri,
            'gst'            => $gst,
            'table'          => $order->table ? [
                'id'           => $order->table->id,
                'table_number' => $order->table->table_number,
            ] : null,
            'items' => ($order->items ?? $order->orderItems)->map(fn($i) => [
                'id'        => $i->id,
                'name'      => $i->menuItem?->name,
                'quantity'  => $i->quantity,
                'price'     => (float) $i->price,
                'subtotal'  => (float) ($i->price * $i->quantity),
                'status'    => $i->status,
                'notes'     => $i->notes,
            ])->values(),
        ];
    }

    private function computeGst(Order $order): array
    {
        $branch  = $order->relationLoaded('branch') ? $order->branch : \App\Models\Branch::with('gstSlab')->find($order->branch_id);
        $slab    = $branch?->gstSlab;
        $mode    = $branch?->gst_mode;

        if (!$slab || !$mode) {
            return ['enabled' => false];
        }

        $base    = (float) $order->total_amount;
        $cgstPct = (float) $slab->cgst_rate;
        $sgstPct = (float) $slab->sgst_rate;

        if ($mode === 'excluded') {
            $cgst  = round($base * $cgstPct / 100, 2);
            $sgst  = round($base * $sgstPct / 100, 2);
            $grand = $base + $cgst + $sgst;
        } else {
            $totalPct = $cgstPct + $sgstPct;
            $base     = round($base * 100 / (100 + $totalPct), 2);
            $cgst     = round($base * $cgstPct / 100, 2);
            $sgst     = round($base * $sgstPct / 100, 2);
            $grand    = (float) $order->total_amount;
        }

        return [
            'enabled'   => true,
            'mode'      => $mode,
            'cgst_pct'  => $cgstPct,
            'sgst_pct'  => $sgstPct,
            'total_pct' => $cgstPct + $sgstPct,
            'base'      => $base,
            'cgst'      => $cgst,
            'sgst'      => $sgst,
            'grand'     => $grand,
        ];
    }
}
