@extends('layouts.cashier')

@section('title', 'Pending Payments')
@php use Illuminate\Support\Facades\URL; @endphp

@section('content')
<div class="space-y-3">
<div class="flex justify-between items-center">
    <h2 class="text-xl font-bold">Pending Payments ({{ $orders->count() }})</h2>
    <button onclick="location.reload()" class="flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold">
        🔄 Refresh
    </button>
</div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-red-500"
             data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <p class="text-sm text-gray-500">Table {{ $order->table->table_number }} • {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($order->orderItems as $item)
                        <div class="py-2 border-b" data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold {{ $item->status === 'cancelled' ? 'line-through text-gray-400' : '' }}">{{ $item->menuItem->name }}</span>
                                        @if($item->status === 'cancelled')
                                            <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-{{ $item->status === 'cancelled' ? '400' : '600' }}">Qty: {{ $item->quantity }}</div>
                                    @if($item->notes)
                                        <div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">
                                            → {{ $item->notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($item->status === 'cancelled')
                                        <div class="text-gray-400 line-through text-sm">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                    @else
                                        <div class="font-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($order->customer_notes)
                <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <h4 class="font-semibold mb-1 text-sm text-yellow-800 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                        </svg>
                        Customer Request:
                    </h4>
                    <p class="text-sm text-gray-700 italic">{{ $order->customer_notes }}</p>
                </div>
                @endif

                <div class="pt-3 border-t">
                    <div class="font-bold text-xl text-green-600 mb-4" data-order-total>Total: ₹{{ number_format($order->total_amount, 2) }}</div>

                    <form action="{{ route('cashier.payments.process', $order) }}" method="POST" id="paymentForm{{ $order->id }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-semibold mb-2">Payment Method</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="selectPaymentMode({{ $order->id }}, 'cash')"
                                    class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500"
                                    data-order="{{ $order->id }}" data-mode="cash">
                                    💵 Cash
                                </button>
                                <button type="button" onclick="selectPaymentMode({{ $order->id }}, 'upi')"
                                    class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500"
                                    data-order="{{ $order->id }}" data-mode="upi">
                                    📱 UPI
                                </button>
                                <button type="button" onclick="selectPaymentMode({{ $order->id }}, 'card')"
                                    class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500"
                                    data-order="{{ $order->id }}" data-mode="card">
                                    💳 Card
                                </button>
                            </div>
                            <input type="hidden" name="payment_mode" id="paymentMode{{ $order->id }}" required>
                        </div>

                        <div id="cashSection{{ $order->id }}" class="mb-4" style="display: none;">
                            <label class="block text-sm font-semibold mb-2">Cash Received</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" min="0"
                                    id="cashReceived{{ $order->id }}"
                                    class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 text-lg"
                                    placeholder="Enter amount">
                                <button type="button" onclick="calculateChange({{ $order->id }}, {{ $order->total_amount }})"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">
                                    OK
                                </button>
                            </div>
                        </div>

                        <div id="changeSection{{ $order->id }}" class="mb-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4" style="display: none;">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-1">Change to Return</p>
                                <p class="text-3xl font-bold text-green-600" id="changeAmount{{ $order->id }}">₹0.00</p>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn{{ $order->id }}"
                            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg"
                            style="display: none;" disabled>
                            Complete Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">✓</div>
            <p class="text-gray-600">All payments cleared!</p>
        </div>
    @endforelse
</div>

{{-- QR Modal --}}
<div id="qrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" style="display:none !important;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 mx-4 w-full max-w-sm text-center">
        <div class="text-green-500 text-5xl mb-2">✅</div>
        <h2 class="text-xl font-bold mb-1">Payment Complete!</h2>
        <p class="text-gray-500 text-sm mb-4">Customer can scan this QR to download their bill</p>

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
const BILL_URLS = {
    @foreach($orders as $order)
    {{ $order->id }}: "{{ URL::signedRoute('bill.show', ['orderId' => $order->id]) }}",
    @endforeach
};

function selectPaymentMode(orderId, mode) {
    document.querySelectorAll(`[data-order="${orderId}"]`).forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-300');
    });
    event.target.classList.remove('border-gray-300');
    event.target.classList.add('border-blue-500', 'bg-blue-50');
    document.getElementById(`paymentMode${orderId}`).value = mode;
    const cashSection  = document.getElementById(`cashSection${orderId}`);
    const changeSection = document.getElementById(`changeSection${orderId}`);
    const submitBtn    = document.getElementById(`submitBtn${orderId}`);
    if (mode === 'cash') {
        cashSection.style.display  = 'block';
        changeSection.style.display = 'none';
        submitBtn.style.display    = 'none';
        submitBtn.disabled         = true;
    } else {
        cashSection.style.display  = 'none';
        changeSection.style.display = 'none';
        submitBtn.style.display    = 'block';
        submitBtn.disabled         = false;
    }
}

function calculateChange(orderId, totalAmount) {
    const cashReceived = parseFloat(document.getElementById(`cashReceived${orderId}`).value);
    if (!cashReceived || cashReceived < totalAmount) {
        alert(`Cash received must be at least ₹${totalAmount.toFixed(2)}`);
        return;
    }
    const change = cashReceived - totalAmount;
    document.getElementById(`changeAmount${orderId}`).textContent = `₹${change.toFixed(2)}`;
    document.getElementById(`changeSection${orderId}`).style.display = 'block';
    document.getElementById(`submitBtn${orderId}`).style.display    = 'block';
    document.getElementById(`submitBtn${orderId}`).disabled         = false;
}

function showQrModal(orderId) {
    const billUrl = BILL_URLS[orderId] || `{{ url('/bill') }}/${orderId}`;
    document.getElementById('billLink').textContent = billUrl;
    document.getElementById('billLink').href        = billUrl;
    document.getElementById('openBillBtn').href     = billUrl;

    // Clear previous QR and generate new one
    const container = document.getElementById('qrCodeContainer');
    container.innerHTML = '';
    new QRCode(container, {
        text:   billUrl,
        width:  200,
        height: 200,
        colorDark:  '#111827',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
    });

    const modal = document.getElementById('qrModal');
    modal.style.display = 'flex';
    modal.style.removeProperty('display'); // remove the !important none
    modal.style.display = 'flex';
}

function closeQrModal() {
    document.getElementById('qrModal').style.display = 'none';
    // Remove paid_order from URL without reload
    const url = new URL(window.location);
    url.searchParams.delete('paid_order');
    window.history.replaceState({}, '', url);
}

// Auto-open QR modal if redirected after payment
document.addEventListener('DOMContentLoaded', function () {
    const params   = new URLSearchParams(window.location.search);
    const paidOrder = params.get('paid_order');
    if (paidOrder) {
        showQrModal(paidOrder);
    }
});
</script>

<script>window.ORDER_POLL = { panel: 'cashier' };</script>
<script src="/js/order-poll.js"></script>
@endsection
