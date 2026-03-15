@extends('layouts.cashier')

@section('title', 'Payment History')

@section('content')
<div class="space-y-3">
    <h2 class="text-xl font-bold">Today's History ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-green-500">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            @if($order->is_parcel)
                                <span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">📦 Parcel</span>
                            @else
                                <span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">T{{ $order->table->table_number }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $order->paid_at ? $order->paid_at->format('h:i A') : $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                        Paid
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <div class="font-semibold {{ $item->status === 'cancelled' ? 'line-through text-gray-400' : '' }}">
                                    {{ $item->menuItem->name }}
                                    @if($item->status === 'cancelled')
                                        <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded ml-1">Cancelled</span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">Qty: {{ $item->quantity }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold {{ $item->status === 'cancelled' ? 'line-through text-gray-400' : '' }}">
                                    ₹{{ number_format($item->price * $item->quantity, 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center pt-3 border-t">
                    <div>
                        <div class="font-bold text-xl text-green-600">₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ strtoupper($order->payment_mode ?? 'CASH') }}</div>
                    </div>
                    <button onclick="showQrModal({{ $order->id }})"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Bill QR
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">📋</div>
            <p class="text-gray-600">No payment history today</p>
        </div>
    @endforelse
</div>

{{-- QR Modal --}}
<div id="qrModal" class="fixed inset-0 bg-black bg-opacity-60 items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-6 mx-4 w-full max-w-sm text-center">
        <h2 class="text-xl font-bold mb-1">Bill QR Code</h2>
        <p class="text-gray-500 text-sm mb-4">Customer can scan to view &amp; download their bill</p>

        <div class="bg-gray-50 rounded-xl p-4 mb-4 flex justify-center">
            <div id="qrCodeContainer"></div>
        </div>

        <p class="text-xs text-gray-400 mb-1">Or share this link:</p>
        <a id="billLink" href="#" target="_blank"
           class="text-blue-600 text-sm underline break-all block mb-4"></a>

        <div class="flex gap-2">
            <button onclick="closeQrModal()"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold">
                Close
            </button>
            <a id="openBillBtn" href="#" target="_blank"
               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold text-center">
                Open Bill
            </a>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let currentQr = null;

function showQrModal(orderId) {
    const billUrl = `{{ url('/bill') }}/${orderId}`;
    document.getElementById('billLink').textContent = billUrl;
    document.getElementById('billLink').href        = billUrl;
    document.getElementById('openBillBtn').href     = billUrl;

    const container = document.getElementById('qrCodeContainer');
    container.innerHTML = '';
    currentQr = new QRCode(container, {
        text:   billUrl,
        width:  200,
        height: 200,
        colorDark:  '#111827',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
    });

    document.getElementById('qrModal').classList.remove('hidden');
    document.getElementById('qrModal').classList.add('flex');
}

function closeQrModal() {
    document.getElementById('qrModal').classList.add('hidden');
    document.getElementById('qrModal').classList.remove('flex');
}

// Close on backdrop click
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) closeQrModal();
});
</script>
@endsection
