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
                        <p class="text-sm text-gray-500">Table {{ $order->table->table_number }} • {{ $order->paid_at ? $order->paid_at->format('h:i A') : $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                        Paid
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($order->orderItems as $item)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
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
                    <div>
                        <div class="font-bold text-xl text-green-600">₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ ucfirst($order->payment_mode ?? 'cash') }}</div>
                    </div>
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
@endsection
