@extends('layouts.manager')
@section('title', 'Edit Handover')
@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('manager.handover.index') }}" class="btn btn-secondary btn-sm">← Back</a>
    <h2 style="font-size:1.2rem;font-weight:600;margin:0;">✏️ Edit Handover #{{ $handover->id }}</h2>
</div>

<div class="content-card" style="max-width:560px;">
    <div class="card-body">
        <p style="color:#666;font-size:13px;margin-bottom:1rem;">
            Cashier: <strong>{{ $handover->cashier?->name }}</strong> &nbsp;|&nbsp;
            Date: <strong>{{ $handover->handover_date->format('d M Y') }}</strong>
        </p>

        <form action="{{ route('manager.handover.update', $handover) }}" method="POST">
            @csrf @method('PATCH')

            <div class="mb-4">
                @foreach(\App\Models\CashHandover::denominations() as $d)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;padding:8px;background:#f9fafb;border-radius:6px;">
                    <div style="width:50px;text-align:center;font-weight:700;">₹{{ $d }}</div>
                    <span style="color:#666;">×</span>
                    <input type="number" name="denom_{{ $d }}" id="denom_{{ $d }}"
                           value="{{ old('denom_'.$d, $handover->{'denom_'.$d}) }}" min="0"
                           class="form-control" style="width:90px;text-align:center;font-weight:700;"
                           oninput="calcTotal()">
                    <span style="color:#666;">=</span>
                    <span style="font-weight:700;color:#2563eb;width:80px;text-align:right;" id="sub_{{ $d }}">₹{{ number_format($d * $handover->{'denom_'.$d}, 0) }}</span>
                </div>
                @endforeach
            </div>

            <div style="background:#dbeafe;border:1px solid #93c5fd;border-radius:6px;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <strong>Total Cash</strong>
                <strong style="font-size:1.2rem;" id="grandTotal">₹{{ number_format($handover->total_cash, 2) }}</strong>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-control" placeholder="Any remarks...">{{ old('notes', $handover->notes) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="margin-top:1rem;">Save Changes</button>
        </form>
    </div>
</div>

<script>
const DENOMS = @json(\App\Models\CashHandover::denominations());
function calcTotal() {
    let total = 0;
    DENOMS.forEach(d => {
        const qty = parseInt(document.getElementById('denom_' + d)?.value) || 0;
        const sub = qty * d; total += sub;
        const el = document.getElementById('sub_' + d);
        if (el) el.textContent = '₹' + sub.toLocaleString('en-IN');
    });
    document.getElementById('grandTotal').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits:2});
}
document.addEventListener('DOMContentLoaded', calcTotal);
</script>
@endsection
