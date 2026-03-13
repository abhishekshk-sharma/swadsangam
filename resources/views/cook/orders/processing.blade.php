@extends('layouts.cook')

@section('title', 'Processing Orders')

@section('content')
<div class="space-y-3">
    <h2 class="text-xl font-bold">Processing Orders ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-blue-500">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <p class="text-sm text-gray-500">Table {{ $order->table->table_number }} • {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                        Preparing
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div class="flex-1">
                                <div class="font-semibold">{{ $item->menuItem->name }}</div>
                                <div class="text-sm text-gray-600">Qty: {{ $item->quantity }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center pt-3 border-t">
                    <div class="font-bold text-lg">Total: ₹{{ number_format($order->total_amount, 2) }}</div>
                    <form action="{{ route('cook.orders.updateAllItems', $order) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="ready">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                            Mark Ready
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">◐</div>
            <p class="text-gray-600">No orders in processing</p>
        </div>
    @endforelse
</div>
@endsection
