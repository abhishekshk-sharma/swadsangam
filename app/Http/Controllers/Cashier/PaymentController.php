<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem, MenuItem, MenuCategory, Employee};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    private function branchId(): ?int
    {
        return auth()->guard('employee')->user()->branch_id ?? null;
    }

    private function branchScope(): \Closure
    {
        $branchId = $this->branchId();
        return fn($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereNull('branch_id');
    }

    public function index()
    {
        $orders = Order::with(['table.category', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->where(function ($q) {
                $q->where(function ($q2) {
                      $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']);
                  })
                  ->orWhere(function ($q2) {
                      $q2->where('is_parcel', true)->where('status', 'ready');
                  });
            })
            ->where($this->branchScope())
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $branchUpiId = null;
        $branchGst   = ['enabled' => false];
        if ($this->branchId()) {
            $branch      = \App\Models\Branch::with('gstSlab')->find($this->branchId());
            $branchUpiId = $branch?->upi_id;
            $branchGst   = $this->computeGst($branch);
        }

        return view('cashier.payments.index', compact('orders', 'branchUpiId', 'branchGst'));
    }

public function processPayment(Request $request, Order $order)
    {
        abort_if($order->tenant_id !== (int) $this->currentTenantId(), 403);

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

        // Verify grand total matches expected GST calculation
        if ($request->filled('grand_total')) {
            $branch   = \App\Models\Branch::with('gstSlab')->find($order->branch_id);
            $slab     = $branch?->gstSlab;
            $mode     = $branch?->gst_mode;
            $base     = (float) $order->total_amount;
            $expected = $base;
            if ($slab && $mode === 'excluded') {
                $expected = round($base + ($base * $slab->cgst_rate / 100) + ($base * $slab->sgst_rate / 100), 2);
            }
            if (abs(round((float) $request->grand_total, 2) - $expected) > 0.02) {
                return response()->json(['success' => false, 'message' => 'Bill total mismatch. Please refresh and try again.'], 422);
            }
        }

        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
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

        return redirect()->route('cashier.payments.index', ['paid_order' => $order->id])
            ->with('success', 'Payment received! Order closed.')
            ->with('bill_url', $billUrl);
    }

    public function history()
    {
        $orders = Order::with(['table', 'orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->where('status', 'paid')
            ->where($this->branchScope())
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('cashier.payments.history', compact('orders'));
    }

    // ── Parcel order creation ──────────────────────────────────────────────

    public function createParcel()
    {
        $menuItems      = MenuItem::with('menuCategory')->where('is_available', true)->get();
        $menuCategories = MenuCategory::whereHas('menuItems', fn($q) => $q->where('is_available', true))->get();

        return view('cashier.parcels.create', compact('menuItems', 'menuCategories'));
    }

    public function storeParcel(Request $request)
    {
        $request->validate([
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
            'tenant_id'      => $this->currentTenantId(),
            'branch_id'      => $this->branchId(),
            'table_id'       => null,
            'user_id'        => auth()->guard('employee')->id(),
            'status'         => 'pending',
            'total_amount'   => $total,
            'customer_notes' => $request->customer_notes,
            'is_parcel'      => true,
        ]);

        foreach ($request->items as $item) {
            $menuItem = MenuItem::findOrFail($item['menu_item_id']);
            OrderItem::create([
                'tenant_id'    => $this->currentTenantId(),
                'branch_id'    => $this->branchId(),
                'order_id'     => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity'     => $item['quantity'],
                'price'        => $menuItem->price,
                'status'       => 'pending',
                'notes'        => $item['notes'] ?? null,
            ]);
        }

        $this->notifyChefs($order);

        return redirect()->route('cashier.parcels.index')->with('success', 'Parcel order created!');
    }

    // ── Parcel order management ────────────────────────────────────────────

    public function parcelsIndex()
    {
        $orders = Order::with(['orderItems' => fn($q) => $q->withoutGlobalScopes()->with(['menuItem' => fn($q2) => $q2->withoutGlobalScopes()])])
            ->where('is_parcel', true)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->where($this->branchScope())
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        $menuItems = MenuItem::with('category')->where('is_available', true)->get();

        return view('cashier.parcels.index', compact('orders', 'menuItems'));
    }

    public function addParcelItems(Request $request, $id)
    {
        $order = $this->findParcelOrder($id);

        if ($order->status === 'paid') {
            return back()->with('error', 'This order has already been paid.');
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

            $newItems[] = OrderItem::create([
                'tenant_id'    => $this->currentTenantId(),
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
            'total_amount' => $order->total_amount + $additionalTotal,
            'status'       => in_array($order->status, ['ready']) ? 'preparing' : $order->status,
        ]);

        $order->refresh();
        $this->notifyChefs($order, $newItems);

        return back()->with('success', 'Items added.');
    }

    public function updateParcelItem(Request $request, $id)
    {
        $item = $this->findParcelItem($id);
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

    public function cancelParcelOrder($id)
    {
        $order = $this->findParcelOrder($id);
        if ($order->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid order.');
        }
        if ($order->orderItems()->where('status', 'prepared')->exists()) {
            return back()->with('error', 'Cannot cancel — some items are already prepared.');
        }
        $order->orderItems()->update(['status' => 'cancelled']);
        $order->update(['status' => 'cancelled']);
        return back()->with('success', 'Parcel order cancelled.');
    }

    public function cancelParcelItem($id)
    {
        $item = $this->findParcelItem($id);
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        $this->syncParcelOrderStatus($item->order);
        return back()->with('success', 'Item cancelled.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function findParcelOrder(int $id): Order
    {
        return Order::where('id', $id)
            ->where('is_parcel', true)
            ->where('tenant_id', $this->currentTenantId())
            ->where($this->branchScope())
            ->firstOrFail();
    }

    private function findParcelItem(int $id): OrderItem
    {
        $item = OrderItem::with('order')->findOrFail($id);
        abort_if($item->order->tenant_id !== (int) $this->currentTenantId(), 403);
        abort_if(!$item->order->is_parcel, 403);
        return $item;
    }

    private function syncParcelOrderStatus(Order $order)
    {
        $order->refresh();
        $nonCancelled = $order->orderItems()->where('status', '!=', 'cancelled');
        if ($nonCancelled->count() === 0) {
            $order->update(['status' => 'cancelled']);
        } elseif ($nonCancelled->where('status', '!=', 'prepared')->count() === 0) {
            $order->update(['status' => 'ready']);
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }

    private function notifyChefs($order, $specificItems = null)
    {
        $senderBranchId = auth()->guard('employee')->user()->branch_id ?? null;

        $chefs = Employee::where('role', 'chef')
            ->where('tenant_id', $this->currentTenantId())
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

    private function computeGst(?\App\Models\Branch $branch): array
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

    private function currentTenantId(): ?int
    {
        $user = \Illuminate\Support\Facades\Auth::guard('employee')->user()
            ?? \Illuminate\Support\Facades\Auth::guard('admin')->user();
        return $user ? (int) $user->tenant_id : (int) session('tenant_id');
    }
}
