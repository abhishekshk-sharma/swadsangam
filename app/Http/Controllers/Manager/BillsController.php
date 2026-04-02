<?php

namespace App\Http\Controllers\Manager;

use App\Models\Order;
use Illuminate\Http\Request;

class BillsController extends BaseManagerController
{
    public function index(Request $request)
    {
        $filterType = $request->filter_type;
        $activeMode = $request->payment_mode ?? 'all';

        $query = Order::where('tenant_id', $this->tenantId())
            ->where('status', 'paid')
            ->with(['table', 'user']);

        $this->scopeBranch($query);
        $this->applyDateFilter($query, $request);
        if ($activeMode !== 'all') $query->where('payment_mode', $activeMode);

        $bills       = $query->orderBy('paid_at', 'desc')->get();
        $totalBills  = $bills->count();
        $totalAmount = $bills->sum(fn($o) => $o->grand_total ?? $o->total_amount);
        $hiddenCount = $bills->where('bill_hidden', true)->count();

        // Chart data
        $chartQuery = Order::where('tenant_id', $this->tenantId())->where('status', 'paid');
        $this->scopeBranch($chartQuery);
        $this->applyDateFilter($chartQuery, $request);

        $chartData = $chartQuery->selectRaw('DATE(paid_at) as date, COUNT(*) as count, SUM(COALESCE(grand_total, total_amount)) as revenue')
            ->groupBy('date')->orderBy('date')->get();

        // Payment totals
        $allQuery = Order::where('tenant_id', $this->tenantId())->where('status', 'paid');
        $this->scopeBranch($allQuery);
        $this->applyDateFilter($allQuery, $request);
        $allBills = $allQuery->get();

        $paymentTotals = [
            'cash' => $allBills->where('payment_mode', 'cash')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'upi'  => $allBills->where('payment_mode', 'upi')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
            'card' => $allBills->where('payment_mode', 'card')->sum(fn($o) => $o->grand_total ?? $o->total_amount),
        ];

        return view('manager.bills.index', compact(
            'bills', 'totalBills', 'totalAmount', 'hiddenCount',
            'filterType', 'activeMode', 'chartData', 'paymentTotals'
        ));
    }

    public function toggleHidden(Request $request, int $id)
    {
        $order = $this->findForTenant(Order::class, $id);
        abort_if($order->status !== 'paid', 403);
        $order->update(['bill_hidden' => !$order->bill_hidden]);
        return back()->with('success', 'Bill ' . ($order->bill_hidden ? 'hidden' : 'restored') . ' successfully.');
    }

    public function bulkHide(Request $request)
    {
        $request->validate(['filter_type' => 'required|in:day,month,year,custom', 'action' => 'required|in:hide,restore']);

        $query = Order::where('tenant_id', $this->tenantId())->where('status', 'paid');
        $this->scopeBranch($query);
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
