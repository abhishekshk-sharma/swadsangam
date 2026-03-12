@extends('layouts.waiter')

@section('title', 'Today\'s Orders')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Today's Orders</h1>
    <p class="text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-3">
    @forelse($orders as $order)
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex justify-between items-start mb-3">
            <div>
                <h3 class="text-lg font-bold">Order #{{ $order->id }}</h3>
                <p class="text-xs text-gray-500">Table {{ $order->table->table_number }}</p>
                <p class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
            </div>
            <span class="px-2 py-1 rounded text-xs font-semibold 
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}
                {{ $order->status === 'paid' ? 'bg-gray-100 text-gray-800' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>
        
        <div class="mb-3">
            <h4 class="font-semibold mb-1 text-xs text-gray-600">Items:</h4>
            <ul class="space-y-1">
                @foreach($order->items as $item)
                <li class="text-sm">{{ $item->quantity }}x {{ $item->menuItem->name }}</li>
                @endforeach
            </ul>
        </div>
        
        <div class="border-t pt-2">
            <div class="flex justify-between items-center">
                <div class="font-bold">
                    <span>Total:</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                @if($order->status === 'ready')
                <button onclick="markServed({{ $order->id }})" class="bg-green-500 text-white px-4 py-2 rounded text-sm font-semibold">
                    Mark as Served
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-12 text-gray-500">
        <p class="text-lg">No orders today</p>
        <a href="/waiter/orders/create" class="text-blue-500 hover:underline mt-2 inline-block text-sm">Create your first order</a>
    </div>
    @endforelse
</div>

<script>
function markServed(orderId) {
    if (!confirm('Mark this order as served?')) return;
    
    fetch(`/waiter/orders/${orderId}/serve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(() => location.reload());
}
</script>
@endsection
