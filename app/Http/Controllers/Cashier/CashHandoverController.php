<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\CashHandover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashHandoverController extends Controller
{
    private function cashier()
    {
        return Auth::guard('employee')->user();
    }

    private function tenantId(): int
    {
        return (int) $this->cashier()->tenant_id;
    }

    public function create()
    {
        $cashier = $this->cashier();

        $pending = CashHandover::withoutGlobalScopes()
            ->where('cashier_id', $cashier->id)
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'pending')
            ->latest()
            ->first();

        $latest = CashHandover::withoutGlobalScopes()
            ->where('cashier_id', $cashier->id)
            ->where('tenant_id', $this->tenantId())
            ->latest()
            ->first();

        return view('cashier.handover.create', compact('pending', 'latest'));
    }

    public function store(Request $request)
    {
        $cashier = $this->cashier();

        // Block if a pending handover already exists
        $hasPending = CashHandover::withoutGlobalScopes()
            ->where('cashier_id', $cashier->id)
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'You already have a pending handover awaiting admin approval.');
        }

        $data = $request->validate([
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

        $handover = new CashHandover();
        $handover->tenant_id     = $this->tenantId();
        $handover->branch_id     = $cashier->branch_id;
        $handover->cashier_id    = $cashier->id;
        $handover->handover_date = today();
        $handover->status        = 'pending';
        $handover->denom_1   = (int) ($data['denom_1']   ?? 0);
        $handover->denom_2   = (int) ($data['denom_2']   ?? 0);
        $handover->denom_5   = (int) ($data['denom_5']   ?? 0);
        $handover->denom_10  = (int) ($data['denom_10']  ?? 0);
        $handover->denom_20  = (int) ($data['denom_20']  ?? 0);
        $handover->denom_50  = (int) ($data['denom_50']  ?? 0);
        $handover->denom_100 = (int) ($data['denom_100'] ?? 0);
        $handover->denom_200 = (int) ($data['denom_200'] ?? 0);
        $handover->denom_500 = (int) ($data['denom_500'] ?? 0);
        $handover->notes     = $data['notes'] ?? null;
        $handover->recalcTotal();
        $handover->save();

        return redirect()->route('cashier.handover.create')
            ->with('success', 'Cash handover submitted! Awaiting admin approval.');
    }
}
