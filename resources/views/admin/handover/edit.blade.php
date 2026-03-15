@extends('layouts.admin')
@section('title', 'Edit Handover')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.handover.index') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
    <h2 class="fw-bold mb-0">✏️ Edit Handover #{{ $handover->id }}</h2>
</div>

<div class="content-card p-4" style="max-width:560px;">
    <p class="text-muted small mb-3">Cashier: <strong>{{ $handover->cashier?->name }}</strong> &nbsp;|&nbsp; Date: <strong>{{ $handover->handover_date->format('d M Y') }}</strong></p>

    <form action="{{ route('admin.handover.update', $handover) }}" method="POST">
        @csrf @method('PATCH')

        <div class="mb-4">
            @foreach(\App\Models\CashHandover::denominations() as $d)
            <div class="d-flex align-items-center gap-3 mb-2 p-2 bg-light rounded">
                <div style="width:50px;" class="text-center fw-bold">₹{{ $d }}</div>
                <span class="text-muted">×</span>
                <input type="number" name="denom_{{ $d }}" id="denom_{{ $d }}"
                       value="{{ old('denom_'.$d, $handover->{'denom_'.$d}) }}" min="0"
                       class="form-control text-center fw-bold" style="width:90px;"
                       oninput="calcTotal()">
                <span class="text-muted">=</span>
                <span class="fw-bold text-primary" id="sub_{{ $d }}" style="width:80px;text-align:right;">₹{{ number_format($d * $handover->{'denom_'.$d}, 0) }}</span>
            </div>
            @endforeach
        </div>

        <div class="alert alert-primary d-flex justify-content-between align-items-center py-2">
            <strong>Total Cash</strong>
            <strong class="fs-5" id="grandTotal">₹{{ number_format($handover->total_cash, 2) }}</strong>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Notes</label>
            <textarea name="notes" rows="2" class="form-control" placeholder="Any remarks...">{{ old('notes', $handover->notes) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold">Save Changes</button>
    </form>
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
    document.getElementById('grandTotal').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits:2});
}
document.addEventListener('DOMContentLoaded', calcTotal);
</script>
@endsection
