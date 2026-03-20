<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashHandover;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CashHandoverController extends Controller
{
    private function admin()
    {
        return Auth::guard('admin')->user() ?? Auth::guard('employee')->user();
    }

    public function index(Request $request)
    {
        $tenantId = app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
        $query = CashHandover::with('cashier')->latest();

        if ($request->filled('branch_id')) {
            $query->whereHas('cashier', fn($q) => $q->where('branch_id', $request->branch_id));
        }
        if ($request->filter_type === 'today') {
            $query->whereDate('handover_date', today());
        } elseif ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('handover_date', substr($request->month, 0, 4))
                  ->whereMonth('handover_date', substr($request->month, 5, 2));
        } elseif ($request->filter_type === 'custom' && $request->date_from && $request->date_to) {
            $query->whereBetween('handover_date', [$request->date_from, $request->date_to]);
        }

        $handovers = $query->paginate(20)->withQueryString();

        $cashierIds = $handovers->pluck('cashier_id')->filter()->unique();
        $dates      = $handovers->pluck('handover_date')->filter()->unique()->map(fn($d) => $d->toDateString());

        $systemTotals = Order::whereIn('cashier_id', $cashierIds)
            ->whereIn(DB::raw('DATE(paid_at)'), $dates)
            ->where('status', 'paid')
            ->where('payment_mode', 'cash')
            ->selectRaw('cashier_id, DATE(paid_at) as paid_date, SUM(total_amount) as total')
            ->groupBy('cashier_id', 'paid_date')
            ->get()
            ->keyBy(fn($r) => $r->cashier_id . '_' . $r->paid_date);

        $branches       = \App\Models\Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $selectedBranch = $request->branch_id;

        return view('admin.handover.index', compact('handovers', 'systemTotals', 'branches', 'selectedBranch'));
    }

    public function export(Request $request)
    {
        $query = CashHandover::with('cashier')->latest();

        if ($request->filled('branch_id')) {
            $query->whereHas('cashier', fn($q) => $q->where('branch_id', $request->branch_id));
        }
        if ($request->filter_type === 'today') {
            $query->whereDate('handover_date', today());
            $filename = 'handovers_today_' . today()->format('d-m-Y') . '.xlsx';
        } elseif ($request->filter_type === 'month' && $request->month) {
            $query->whereYear('handover_date', substr($request->month, 0, 4))
                  ->whereMonth('handover_date', substr($request->month, 5, 2));
            $filename = 'handovers_' . $request->month . '.xlsx';
        } elseif ($request->filter_type === 'custom' && $request->date_from && $request->date_to) {
            $query->whereBetween('handover_date', [$request->date_from, $request->date_to]);
            $filename = 'handovers_' . $request->date_from . '_to_' . $request->date_to . '.xlsx';
        } else {
            $filename = 'handovers_all.xlsx';
        }

        $handovers = $query->get();

        // Build system totals for export
        $cashierIds = $handovers->pluck('cashier_id')->filter()->unique();
        $dates      = $handovers->pluck('handover_date')->filter()->unique()->map(fn($d) => $d->toDateString());
        $systemTotals = Order::whereIn('cashier_id', $cashierIds)
            ->whereIn(DB::raw('DATE(paid_at)'), $dates)
            ->where('status', 'paid')->where('payment_mode', 'cash')
            ->selectRaw('cashier_id, DATE(paid_at) as paid_date, SUM(total_amount) as total')
            ->groupBy('cashier_id', 'paid_date')
            ->get()
            ->keyBy(fn($r) => $r->cashier_id . '_' . $r->paid_date);

        return Excel::download(
            new class($handovers, $systemTotals) implements FromCollection, WithHeadings, WithMapping {
                public function __construct(private $handovers, private $systemTotals) {}
                public function collection() { return $this->handovers; }
                public function headings(): array {
                    return ['#', 'Cashier', 'Date', 'Submitted (₹)', 'System Cash (₹)', 'Difference (₹)', 'Status',
                            '₹1', '₹2', '₹5', '₹10', '₹20', '₹50', '₹100', '₹200', '₹500', 'Notes'];
                }
                public function map($h): array {
                    $key  = $h->cashier_id . '_' . $h->handover_date->toDateString();
                    $sys  = $this->systemTotals[$key]->total ?? 0;
                    $diff = $h->total_cash - $sys;
                    return [
                        $h->id, $h->cashier?->name ?? '-', $h->handover_date->format('d-m-Y'),
                        number_format($h->total_cash, 2), number_format($sys, 2), number_format($diff, 2),
                        ucfirst($h->status),
                        $h->denom_1, $h->denom_2, $h->denom_5, $h->denom_10, $h->denom_20,
                        $h->denom_50, $h->denom_100, $h->denom_200, $h->denom_500,
                        $h->notes ?? '',
                    ];
                }
            },
            $filename
        );
    }

    public function edit(CashHandover $handover)
    {
        abort_if($handover->status === 'approved', 403, 'Approved handovers cannot be edited.');
        return view('admin.handover.edit', compact('handover'));
    }

    public function update(Request $request, CashHandover $handover)
    {
        abort_if($handover->status === 'approved', 403);

        $data = $request->validate([
            'denom_1'   => 'required|integer|min:0',
            'denom_2'   => 'required|integer|min:0',
            'denom_5'   => 'required|integer|min:0',
            'denom_10'  => 'required|integer|min:0',
            'denom_20'  => 'required|integer|min:0',
            'denom_50'  => 'required|integer|min:0',
            'denom_100' => 'required|integer|min:0',
            'denom_200' => 'required|integer|min:0',
            'denom_500' => 'required|integer|min:0',
            'notes'     => 'nullable|string|max:500',
        ]);

        $handover->fill($data);
        $handover->recalcTotal();
        $handover->save();

        return redirect()->route('admin.handover.index')
            ->with('success', 'Handover updated.');
    }

    public function approve(Request $request, CashHandover $handover)
    {
        abort_if($handover->status === 'approved', 403, 'Already approved.');

        $request->validate(['password' => 'required|string']);

        $admin = $this->admin();

        if (!Hash::check($request->password, $admin->password)) {
            return back()->with('error', 'Incorrect password. Approval denied.');
        }

        $handover->update([
            'status'      => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.handover.index')
            ->with('success', "Handover #{$handover->id} approved.");
    }
}
