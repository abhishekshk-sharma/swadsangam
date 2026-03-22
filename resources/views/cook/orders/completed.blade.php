@extends('layouts.cook')

@section('title', 'Completed Orders')

@section('content')
<div class="space-y-3">
    <h2 class="text-xl font-bold">Completed Today ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-green-500">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            @if($order->is_parcel)
                                <span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">📦 Parcel</span>
                            @else
                                <span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">T{{ $order->table?->table_number }}</span>
                                @if($order->table?->category)
                                    <span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;letter-spacing:0.02em;">{{ $order->table->category->name }}</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                        Ready
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <div class="font-semibold">{{ $item->menuItem?->name ?? '[Deleted Item]' }}</div>
                                <div class="text-sm text-gray-600">Qty: {{ $item->quantity }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
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
                    <div class="font-bold text-lg">Total: ₹{{ number_format($order->total_amount, 2) }}</div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">✓</div>
            <p class="text-gray-600">No completed orders today</p>
        </div>
    @endforelse
</div>
@endsection
