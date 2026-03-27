<?php

namespace App\Http\Controllers\Manager;

use App\Models\CashHandover;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CashHandoverController extends BaseManagerController
{
    public function index(Request $request)
    {
        $query = CashHandover::with('cashier')
            ->whereHas('cashier', fn($q) => $q->where('branch_id', $this->branchId()))
            ->latest();

        if ($request->filter_type === 'today') {
            $query->whereDate('handover_date', today());
        } elseif ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('handover_date', substr($request->month, 0, 4))
                  ->whereMonth('handover_date', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'custom' && $request->date_from && $request->date_to) {
            $query->whereBetween('handover_date', [$request->date_from, $request->date_to]);
        }

        $handovers  = $query->paginate(20)->withQueryString();
        $cashierIds = $handovers->pluck('cashier_id')->filter()->unique();
        $dates      = $handovers->pluck('handover_date')->filter()->unique()->map(fn($d) => $d->toDateString());

        $systemTotals = Order::whereIn('cashier_id', $cashierIds)
            ->whereIn(DB::raw('DATE(paid_at)'), $dates)
            ->where('status', 'paid')->where('payment_mode', 'cash')
            ->selectRaw('cashier_id, DATE(paid_at) as paid_date, SUM(total_amount) as total')
            ->groupBy('cashier_id', 'paid_date')
            ->get()
            ->keyBy(fn($r) => $r->cashier_id . '_' . $r->paid_date);

        return view('manager.handover.index', compact('handovers', 'systemTotals'));
    }

    public function edit(CashHandover $handover)
    {
        abort_if($handover->status === 'approved', 403, 'Approved handovers cannot be edited.');
        abort_if($handover->tenant_id !== $this->tenantId(), 403);
        return view('manager.handover.edit', compact('handover'));
    }

    public function update(Request $request, CashHandover $handover)
    {
        abort_if($handover->status === 'approved', 403);
        abort_if($handover->tenant_id !== $this->tenantId(), 403);

        $request->validate([
            'denom_1'   => 'nullable|integer|min:0',
            'denom_2'   => 'nullable|integer|min:0',
            'denom_5'   => 'nullable|integer|min:0',
            'denom_10'  => 'nullable|integer|min:0',
            'denom_20'  => 'nullable|integer|min:0',
            'denom_50'  => 'nullable|integer|min:0',
            'denom_100' => 'nullable|integer|min:0',
            'denom_200' => 'nullable|integer|min:0',
            'denom_500' => 'nullable|integer|min:0',
            'notes'     => 'nullable|string|max:500',
        ]);

        $handover->denom_1   = (int) ($request->denom_1   ?? 0);
        $handover->denom_2   = (int) ($request->denom_2   ?? 0);
        $handover->denom_5   = (int) ($request->denom_5   ?? 0);
        $handover->denom_10  = (int) ($request->denom_10  ?? 0);
        $handover->denom_20  = (int) ($request->denom_20  ?? 0);
        $handover->denom_50  = (int) ($request->denom_50  ?? 0);
        $handover->denom_100 = (int) ($request->denom_100 ?? 0);
        $handover->denom_200 = (int) ($request->denom_200 ?? 0);
        $handover->denom_500 = (int) ($request->denom_500 ?? 0);
        $handover->notes     = $request->notes;
        $handover->recalcTotal();
        $handover->save();

        return redirect()->route('manager.handover.index')->with('success', 'Handover #' . $handover->id . ' updated.');
    }

    public function approve(Request $request, CashHandover $handover)
    {
        abort_if($handover->status === 'approved', 403, 'Already approved.');
        $request->validate(['password' => 'required|string']);

        $manager = $this->manager();

        if (!Hash::check($request->password, $manager->password)) {
            return back()->with('error', 'Incorrect password. Approval denied.');
        }

        $handover->update([
            'status'      => 'approved',
            'approved_by' => $manager->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('manager.handover.index')->with('success', "Handover #{$handover->id} approved.");
    }
}
