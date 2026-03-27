@extends('layouts.cashier')
@section('title', 'Cash Handover')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-bold">💵 Cash Handover</h2>
    <p class="text-sm text-gray-500">Count your cash and submit for admin approval.</p>
</div>

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

{{-- Pending notice --}}
@if($pending)
<div class="bg-yellow-50 border border-yellow-400 rounded-lg p-4 mb-4">
    <div class="flex items-center gap-2 mb-2">
        <span class="text-yellow-700 font-bold text-sm">⏳ Pending Handover — awaiting admin approval</span>
    </div>
    <div class="text-xs text-gray-600">Submitted: {{ $pending->created_at->format('d M Y, h:i A') }}</div>
    <div class="text-lg font-bold text-yellow-800 mt-1">Total: ₹{{ number_format($pending->total_cash, 2) }}</div>
    <div class="mt-3 grid grid-cols-3 gap-2 text-xs text-gray-700">
        @foreach(\App\Models\CashHandover::denominations() as $d)
            @if($pending->{"denom_$d"} > 0)
            <div class="bg-white rounded px-2 py-1 border">₹{{ $d }} × {{ $pending->{"denom_$d"} }} = <strong>₹{{ $d * $pending->{"denom_$d"} }}</strong></div>
            @endif
        @endforeach
    </div>
    @if($pending->notes)
        <p class="text-xs text-gray-500 mt-2 italic">Note: {{ $pending->notes }}</p>
    @endif
</div>
@endif

{{-- Form — disabled if pending exists --}}
@if(!$pending)
<form action="{{ route('cashier.handover.store') }}" method="POST" class="space-y-4">
    @csrf

    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-bold text-sm text-gray-700 mb-3">Enter denomination counts:</h3>
        <div class="space-y-2" id="denomForm">
            @foreach(\App\Models\CashHandover::denominations() as $d)
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-16 text-center">
                    <span class="text-base font-bold text-gray-800">₹{{ $d }}</span>
                </div>
                <span class="text-gray-400">×</span>
                <input type="number" name="denom_{{ $d }}" id="denom_{{ $d }}"
                       value="{{ old('denom_'.$d, 0) }}" min="0"
                       class="w-24 border-2 border-gray-200 rounded-lg px-3 py-2 text-center text-lg font-bold focus:border-blue-500 focus:outline-none"
                       oninput="calcTotal()">
                <span class="text-gray-400">=</span>
                <span class="font-bold text-blue-700 w-20 text-right" id="sub_{{ $d }}">₹0</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-blue-600 text-white rounded-lg p-4 flex justify-between items-center">
        <span class="text-lg font-bold">Total Cash</span>
        <span class="text-2xl font-bold" id="grandTotal">₹0.00</span>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Notes (optional)</label>
        <textarea name="notes" rows="2" placeholder="Any remarks..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
    </div>

    <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-bold text-lg">
        Submit Handover Report
    </button>
</form>
@else
<div class="text-center py-6 text-gray-500 text-sm">
    Your previous handover is pending approval. You can submit a new one once it is approved.
</div>
@endif

{{-- Last approved --}}
@if($latest && $latest->status === 'approved')
<div class="mt-6 bg-green-50 border border-green-300 rounded-lg p-4">
    <div class="font-bold text-green-800 text-sm mb-1">✅ Last Approved Handover — {{ $latest->handover_date->format('d M Y') }}</div>
    <div class="text-lg font-bold text-green-700">₹{{ number_format($latest->total_cash, 2) }}</div>
    <div class="text-xs text-gray-500 mt-1">Approved by {{ $latest->approvedBy?->name ?? 'Admin' }} at {{ $latest->approved_at?->format('h:i A') }}</div>
</div>
@endif

<script>
const denoms = @json(\App\Models\CashHandover::denominations());

function calcTotal() {
    let total = 0;
    denoms.forEach(d => {
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
