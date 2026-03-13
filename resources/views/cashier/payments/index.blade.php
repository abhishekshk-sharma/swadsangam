@extends('layouts.cashier')

@section('title', 'Pending Payments')

@section('content')
<div class="space-y-3">
    <h2 class="text-xl font-bold">Pending Payments ({{ $orders->count() }})</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-red-500">
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
                        <div class="py-2 border-b">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="font-semibold">{{ $item->menuItem->name }}</div>
                                    <div class="text-sm text-gray-600">Qty: {{ $item->quantity }}</div>
                                    @if($item->notes)
                                        <div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">
                                            → {{ $item->notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
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
                    <div class="font-bold text-xl text-green-600 mb-4">Total: ₹{{ number_format($order->total_amount, 2) }}</div>
                    
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

<script>
function selectPaymentMode(orderId, mode) {
    // Reset all buttons for this order
    document.querySelectorAll(`[data-order="${orderId}"]`).forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-300');
    });
    
    // Highlight selected button
    event.target.classList.remove('border-gray-300');
    event.target.classList.add('border-blue-500', 'bg-blue-50');
    
    // Set payment mode
    document.getElementById(`paymentMode${orderId}`).value = mode;
    
    // Show/hide cash section
    const cashSection = document.getElementById(`cashSection${orderId}`);
    const changeSection = document.getElementById(`changeSection${orderId}`);
    const submitBtn = document.getElementById(`submitBtn${orderId}`);
    
    if (mode === 'cash') {
        cashSection.style.display = 'block';
        changeSection.style.display = 'none';
        submitBtn.style.display = 'none';
        submitBtn.disabled = true;
    } else {
        cashSection.style.display = 'none';
        changeSection.style.display = 'none';
        submitBtn.style.display = 'block';
        submitBtn.disabled = false;
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
    document.getElementById(`submitBtn${orderId}`).style.display = 'block';
    document.getElementById(`submitBtn${orderId}`).disabled = false;
}
</script>
@endsection
