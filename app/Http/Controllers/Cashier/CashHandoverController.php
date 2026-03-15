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
        $pending = CashHandover::where('cashier_id', $this->cashier()->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $latest = CashHandover::where('cashier_id', $this->cashier()->id)
            ->latest()
            ->first();

        return view('cashier.handover.create', compact('pending', 'latest'));
    }

    public function store(Request $request)
    {
        // Block if a pending handover already exists
        $hasPending = CashHandover::where('cashier_id', $this->cashier()->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->with('error', 'You already have a pending handover awaiting admin approval.');
        }

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

        $handover = new CashHandover($data);
        $handover->tenant_id    = $this->tenantId();
        $handover->cashier_id   = $this->cashier()->id;
        $handover->handover_date = today();
        $handover->status       = 'pending';
        $handover->recalcTotal();
        $handover->save();

        return redirect()->route('cashier.handover.create')
            ->with('success', 'Cash handover submitted! Awaiting admin approval.');
    }
}
