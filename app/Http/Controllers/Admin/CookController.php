<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class CookController extends BaseAdminController
{
    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $selectedBranch = $request->branch_id;
        $selectedDate   = $request->filled('date') ? $request->date : today()->toDateString();

        $query = Order::with(['table.category', 'items.menuItem'])
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at', 'asc');

        if ($selectedBranch) $query->where('branch_id', $selectedBranch);
        if ($request->filled('status')) $query->where('status', $request->status);

        $orders   = $query->get();
        $branches = \App\Models\Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();

        // Day stats
        $statsQuery = Order::where('tenant_id', $tenantId)->whereDate('created_at', $selectedDate);
        if ($selectedBranch) $statsQuery->where('branch_id', $selectedBranch);
        $allToday = $statsQuery->get();

        $stats = [
            'total'     => $allToday->count(),
            'pending'   => $allToday->where('status', 'pending')->count(),
            'preparing' => $allToday->where('status', 'preparing')->count(),
            'ready'     => $allToday->where('status', 'ready')->count(),
            'served'    => $allToday->where('status', 'served')->count(),
            'paid'      => $allToday->where('status', 'paid')->count(),
            'cancelled' => $allToday->where('status', 'cancelled')->count(),
            'revenue'   => $allToday->where('status', 'paid')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
        ];

        $paymentOrders = Order::with(['table.category', 'orderItems' => fn($q) => $q->with('menuItem')])
            ->where('tenant_id', $tenantId)
            ->whereDate('created_at', $selectedDate)
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('is_parcel', false)->whereIn('status', ['served', 'checkout']))
                  ->orWhere(fn($q2) => $q2->where('is_parcel', true)->where('status', 'ready'));
            })
            ->when($selectedBranch, fn($q) => $q->where('branch_id', $selectedBranch))
            ->latest()->get();

        return view('admin.cook.index', compact('orders', 'branches', 'selectedBranch', 'paymentOrders', 'stats', 'selectedDate'));
    }

    public function startPreparing($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $order->update(['status' => 'preparing']);
        return response()->json(['success' => true]);
    }

    public function markReady($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $order->update(['status' => 'ready']);
        return response()->json(['success' => true]);
    }

    public function markServed($id)
    {
        $order = $this->findForTenant(Order::class, $id);
        $order->update(['status' => 'served']);
        return response()->json(['success' => true]);
    }

    public function processPayment(Request $request, $id)
    {
        $order = $this->findForTenant(Order::class, $id);

        $request->validate([
            'payment_mode'  => 'required|in:cash,upi,card',
            'cash_received' => 'nullable|numeric|min:0',
        ]);

        $employeeId = auth()->guard('employee')->id();
        $order->update([
            'status'       => 'paid',
            'payment_mode' => $request->payment_mode,
            'paid_at'      => now(),
            'cashier_id'   => $employeeId && \App\Models\Employee::withoutGlobalScopes()->where('id', $employeeId)->exists() ? $employeeId : null,
        ]);

        if (!$order->is_parcel && $order->table) {
            $order->table->update(['is_occupied' => false]);
        }

        $billUrl = \Illuminate\Support\Facades\URL::signedRoute('bill.show', ['orderId' => $order->id]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'bill_url' => $billUrl, 'order_id' => $order->id]);
        }

        return redirect('/admin/cook')->with('success', 'Payment received! Order #' . ($order->daily_number ?? $order->id) . ' closed.');
    }

    public function updateItem(Request $request, $id)
    {
        $item = $this->findTenantItem($id);
        if (in_array($item->status, ['cancelled', 'paid'])) {
            return back()->with('error', 'Cannot edit a cancelled or paid item.');
        }
        if (in_array($item->order->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Cannot edit items on a paid or cancelled order.');
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
        $order = $this->findForTenant(Order::class, $id);
        if ($order->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid order.');
        }
        if ($order->orderItems()->where('status', 'prepared')->exists()) {
            return back()->with('error', 'Cannot cancel order — some items are already prepared.');
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
        $item = $this->findTenantItem($id);
        if (in_array($item->order->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel items on a paid or cancelled order.');
        }
        $item->update(['status' => 'cancelled']);
        $item->order->decrement('total_amount', $item->price * $item->quantity);
        $this->syncOrderStatus($item->order);
        return back()->with('success', 'Item cancelled.');
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
