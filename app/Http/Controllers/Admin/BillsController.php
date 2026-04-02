<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;

class BillsController extends BaseAdminController
{
    public function index(Request $request)
    {
        $tenantId       = $this->tenantId();
        $branches       = Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $selectedBranch = $request->branch_id;
        $filterType     = $request->filter_type;
        $activeMode     = $request->payment_mode ?? 'all';

        $query = Order::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->with(['table', 'branch', 'user']);

        if ($selectedBranch) $query->where('branch_id', $selectedBranch);
        $this->applyDateFilter($query, $request);
        if ($activeMode !== 'all') $query->where('payment_mode', $activeMode);

        $bills       = $query->orderBy('paid_at', 'desc')->get();
        $totalBills  = $bills->count();
        $totalAmount = $bills->sum(fn($o) => $o->grand_total ?? $o->total_amount);
        $hiddenCount = $bills->where('bill_hidden', true)->count();

        // Chart data — group by date
        $chartQuery = Order::where('tenant_id', $tenantId)->where('status', 'paid');
        if ($selectedBranch) $chartQuery->where('branch_id', $selectedBranch);
        $this->applyDateFilter($chartQuery, $request);

        $chartData = $chartQuery->selectRaw('DATE(paid_at) as date, COUNT(*) as count, SUM(COALESCE(grand_total, total_amount)) as revenue')
            ->groupBy('date')->orderBy('date')->get();

        // Payment mode totals (all, no mode filter)
        $allQuery = Order::where('tenant_id', $tenantId)->where('status', 'paid');
        if ($selectedBranch) $allQuery->where('branch_id', $selectedBranch);
        $this->applyDateFilter($allQuery, $request);
        $allBills = $allQuery->get();

        $paymentTotals = [
            'cash' => $allBills->where('payment_mode', 'cash')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'upi'  => $allBills->where('payment_mode', 'upi')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'card' => $allBills->where('payment_mode', 'card')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
        ];

        return view('admin.bills.index', compact(
            'bills', 'totalBills', 'totalAmount', 'hiddenCount',
            'branches', 'selectedBranch', 'filterType', 'activeMode',
            'chartData', 'paymentTotals'
        ));
    }

    public function toggleHidden(Request $request, int $id)
    {
        $order = Order::where('tenant_id', $this->tenantId())->where('status', 'paid')->findOrFail($id);
        $order->update(['bill_hidden' => !$order->bill_hidden]);
        return back()->with('success', 'Bill ' . ($order->bill_hidden ? 'hidden' : 'restored') . ' successfully.');
    }

    public function bulkHide(Request $request)
    {
        $request->validate(['filter_type' => 'required|in:day,month,year,custom', 'action' => 'required|in:hide,restore']);

        $query = Order::where('tenant_id', $this->tenantId())->where('status', 'paid');
        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);
        $this->applyDateFilter($query, $request);

        $count = $query->count();
        $query->update(['bill_hidden' => $request->action === 'hide']);

        return back()->with('success', $count . ' bill(s) ' . ($request->action === 'hide' ? 'hidden' : 'restored') . '.');
    }

    private function applyDateFilter($query, Request $request): void
    {
        if ($request->filter_type === 'day' && $request->filled('day')) {
            $query->whereDate('paid_at', $request->day);
        } elseif ($request->filter_type === 'month' && $request->filled('month')) {
            $query->whereYear('paid_at', substr($request->month, 0, 4))
                  ->whereMonth('paid_at', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'year' && $request->filled('year')) {
            $query->whereYear('paid_at', $request->year);
        } elseif ($request->filter_type === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            $query->whereDate('paid_at', '>=', $request->date_from)
                  ->whereDate('paid_at', '<=', $request->date_to);
        }
    }
}
