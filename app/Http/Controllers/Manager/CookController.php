<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

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

        $branchUpiId = null;
        $branchGst   = ['enabled' => false];
        $managerBranchId = auth()->guard('employee')->user()?->branch_id;
        if ($managerBranchId) {
            $branch      = \App\Models\Branch::with('gstSlab')->find($managerBranchId);
            $branchUpiId = $branch?->upi_id;
            $branchGst   = $this->computeGst($branch);
        }

        return view('manager.cook.index', compact('orders', 'branchUpiId', 'branchGst'));
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
            'grand_total'   => 'nullable|numeric|min:0',
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

        return redirect()->route('manager.cook.index')->with('success', 'Payment received! Order #' . ($order->daily_number ?? $order->id) . ' closed.');
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
        } elseif (in_array($order->status, ['cancelled', 'pending'])) {
            $order->update(['status' => 'preparing']);
        }
    }
}
