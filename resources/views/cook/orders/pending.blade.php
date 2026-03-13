@extends('layouts.cook')

@section('title', 'Pending Orders')

@section('content')
<div class="space-y-3">
    <h2 class="text-xl font-bold">Pending Orders ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-yellow-500">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <p class="text-sm text-gray-500">Table {{ $order->table->table_number }} • {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-semibold">
                        New Items
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

                <div class="flex justify-between items-center pt-3 border-t">
                    <div class="font-bold text-lg">Total: ₹{{ number_format($order->total_amount, 2) }}</div>
                    <form action="{{ route('cook.orders.updateAllItems', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                            Start Cooking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">✓</div>
            <p class="text-gray-600">No pending orders</p>
        </div>
    @endforelse
</div>
@endsection
