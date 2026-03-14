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
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">Preparing</span>
                </div>

                @if($order->customer_notes)
                <div class="mb-3 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <p class="text-sm text-yellow-800 font-semibold">Customer Note:</p>
                    <p class="text-sm text-gray-700 italic">{{ $order->customer_notes }}</p>
                </div>
                @endif

                <div class="space-y-2">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div class="flex-1">
                                <div class="font-semibold {{ $item->status === 'ready' ? 'line-through text-gray-400' : '' }}">
                                    {{ $item->menuItem->name }}
                                </div>
                                <div class="text-sm text-gray-600">Qty: {{ $item->quantity }}</div>
                                @if($item->notes)
                                    <div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ {{ $item->notes }}</div>
                                @endif
                            </div>
                            <div class="ml-3">
                                @if($item->status === 'pending')
                                    <form action="{{ route('cook.orderItems.updateStatus', $item) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="preparing">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-semibold">
                                            Start Cooking
                                        </button>
                                    </form>
                                @elseif($item->status === 'preparing')
                                    <form action="{{ route('cook.orderItems.updateStatus', $item) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="ready">
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-semibold">
                                            Prepared ✓
                                        </button>
                                    </form>
                                @else
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded text-sm font-semibold">✓ Ready</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-3 border-t text-sm text-gray-500">
                    {{ $order->orderItems->where('status', 'ready')->count() }} / {{ $order->orderItems->count() }} items prepared
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
