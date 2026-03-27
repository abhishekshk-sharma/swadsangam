@extends('layouts.manager')
@section('title', 'Edit Handover')
@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="{{ route('manager.handover.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        ← Back
    </a>
    <h2 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;">
        <i class="fas fa-edit" style="margin-right:8px;color:#2563eb;"></i>Edit Handover #{{ $handover->id }}
    </h2>
</div>

@if($errors->any())
<div style="background:#fee2e2;border:1px solid #dc2626;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
    <ul style="margin:0;padding-left:16px;">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="content-card" style="max-width:560px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <div style="font-size:13px;color:#6b7280;">
            Cashier: <strong style="color:#374151;">{{ $handover->cashier?->name }}</strong>
            &nbsp;·&nbsp;
            Date: <strong style="color:#374151;">{{ $handover->handover_date->format('d M Y') }}</strong>
        </div>
    </div>
    <div style="padding:20px;">
        <form action="{{ route('manager.handover.update', $handover) }}" method="POST">
            @csrf @method('PATCH')

            {{-- Denomination rows --}}
            <div style="margin-bottom:20px;">
                @foreach(\App\Models\CashHandover::denominations() as $d)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;padding:10px 12px;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="width:52px;text-align:center;font-weight:700;font-size:15px;color:#111827;">₹{{ $d }}</div>
                    <span style="color:#9ca3af;font-size:16px;">×</span>
                    <input type="number" name="denom_{{ $d }}" id="denom_{{ $d }}"
                           value="{{ old('denom_'.$d, $handover->{'denom_'.$d}) }}" min="0"
                           oninput="calcTotal()"
                           style="width:90px;padding:8px 10px;border:2px solid #d1d5db;border-radius:8px;font-size:15px;font-weight:700;text-align:center;color:#111827;">
                    <span style="color:#9ca3af;font-size:16px;">=</span>
                    <span style="font-weight:700;color:#2563eb;width:90px;text-align:right;font-size:14px;" id="sub_{{ $d }}">
                        ₹{{ number_format($d * $handover->{'denom_'.$d}, 0) }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Total --}}
            <div style="background:#2563eb;color:#fff;border-radius:10px;padding:14px 18px;display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <span style="font-size:15px;font-weight:600;">Total Cash</span>
                <span style="font-size:22px;font-weight:700;" id="grandTotal">₹{{ number_format($handover->total_cash, 2) }}</span>
            </div>

            {{-- Notes --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Notes (optional)</label>
                <textarea name="notes" rows="2" placeholder="Any remarks..."
                          style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#374151;resize:vertical;box-sizing:border-box;">{{ old('notes', $handover->notes) }}</textarea>
            </div>

            <button type="submit"
                    style="width:100%;background:#2563eb;color:#fff;border:none;padding:13px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;">
                <i class="fas fa-save" style="margin-right:8px;"></i>Save Changes
            </button>
        </form>
    </div>
</div>

<script>
const DENOMS = @json(\App\Models\CashHandover::denominations());
function calcTotal() {
    let total = 0;
    DENOMS.forEach(d => {
        const qty = parseInt(document.getElementById('denom_' + d)?.value) || 0;
        const sub = qty * d;
        total += sub;
        const el = document.getElementById('sub_' + d);
        if (el) el.textContent = '₹' + sub.toLocaleString('en-IN');
    });
    document.getElementById('grandTotal').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
}
document.addEventListener('DOMContentLoaded', calcTotal);
</script>
@endsection
