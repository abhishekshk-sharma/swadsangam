@extends('layouts.cook')

@section('title', 'Orders')

@section('content')
<div class="space-y-3">
    <h2 class="text-xl font-bold">Active Orders ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        @php
            $pendingItems = $order->orderItems->where('status', 'pending');
            $preparedItems = $order->orderItems->where('status', 'prepared');
            $hasNewItems = $order->orderItems->where('status', 'pending')->count() > 0 && $preparedItems->count() > 0;
        @endphp
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 {{ $hasNewItems ? 'border-red-500' : ($order->status === 'preparing' ? 'border-orange-500' : 'border-yellow-500') }}"
             data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <p class="text-sm text-gray-500">Table {{ $order->table->table_number }} • {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    @if($hasNewItems)
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">+ New Items</span>
                    @elseif($order->status === 'preparing')
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">Cooking</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">New</span>
                    @endif
                </div>

                @if($order->customer_notes)
                <div class="mb-3 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <p class="text-sm text-yellow-800 font-semibold">Customer Note:</p>
                    <p class="text-sm text-gray-700 italic">{{ $order->customer_notes }}</p>
                </div>
                @endif

                <div class="space-y-2">
                    @foreach($order->orderItems as $item)
                        <div class="py-2 border-b last:border-0"
                             data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold {{ $item->status === 'prepared' ? 'line-through text-gray-400' : ($item->status === 'cancelled' ? 'line-through text-red-300' : '') }}" data-item-name>
                                            {{ $item->menuItem->name }}
                                        </span>
                                        @if($item->status === 'cancelled')
                                            <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>
                                        @elseif($item->status === 'pending' && $preparedItems->count() > 0)
                                            <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-semibold">NEW</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">Qty: {{ $item->quantity }}</div>
                                    @if($item->notes)
                                        <div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ {{ $item->notes }}</div>
                                    @endif
                                </div>
                                <div class="ml-3 flex gap-1" data-item-actions>
                                    @if($item->status === 'pending')
                                        <form action="{{ route('cook.orderItems.updateStatus', $item) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="prepared">
                                            <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">Prepared ✓</button>
                                        </form>
                                        <button onclick="toggleCookEdit('cedit-{{ $item->id }}')"
                                                class="bg-blue-100 text-blue-700 px-2 py-1.5 rounded-lg text-sm">Edit</button>
                                        <form action="{{ route('cook.orderItems.cancel', $item->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button class="bg-red-100 text-red-600 px-2 py-1.5 rounded-lg text-sm">Cancel</button>
                                        </form>
                                    @elseif($item->status === 'cancelled')
                                        <span class="text-xs text-red-400 px-2">Cancelled</span>
                                    @else
                                        <span class="bg-green-100 text-green-700 px-3 py-1.5 rounded-lg text-sm font-semibold">✓ Done</span>
                                    @endif
                                </div>
                            </div>
                            @if($item->status === 'pending')
                            <div id="cedit-{{ $item->id }}" class="hidden mt-2 bg-gray-50 rounded p-2">
                                <form action="{{ route('cook.orderItems.update', $item->id) }}" method="POST" class="flex flex-col gap-1">
                                    @csrf @method('PATCH')
                                    <div class="flex gap-2 items-center">
                                        <label class="text-xs text-gray-500">Qty:</label>
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                               class="w-16 border rounded px-2 py-0.5 text-sm">
                                    </div>
                                    <div class="flex gap-2 items-center">
                                        <label class="text-xs text-gray-500">Note:</label>
                                        <input type="text" name="notes" value="{{ $item->notes }}"
                                               class="flex-1 border rounded px-2 py-0.5 text-sm" placeholder="Special request...">
                                    </div>
                                    <button class="self-end bg-blue-600 text-white px-3 py-0.5 rounded text-xs">Save</button>
                                </form>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-2 border-t flex justify-between items-center">
                    <span class="text-sm text-gray-500">{{ $preparedItems->count() }} / {{ $order->orderItems->where('status','!=','cancelled')->count() }} items prepared</span>
                    @if($preparedItems->count() === 0)
                    <form action="{{ route('cook.orders.cancel', $order->id) }}" method="POST"
                          onsubmit="return confirm('Cancel entire order #{{ $order->id }}?')">
                        @csrf @method('PATCH')
                        <button class="bg-red-500 text-white px-3 py-1 rounded text-sm">Cancel Order</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">✓</div>
            <p class="text-gray-600">No active orders</p>
        </div>
    @endforelse
</div>
@endsection

<script>
function toggleCookEdit(id) {
    document.getElementById(id).classList.toggle('hidden');
}
</script>

<script>window.ORDER_POLL = { panel: 'cook' };</script>
<script src="/js/order-poll.js"></script>
