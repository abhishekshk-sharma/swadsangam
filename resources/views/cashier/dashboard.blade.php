@extends('layouts.cashier')

@section('title', 'Cashier Dashboard')

@section('content')
<div class="space-y-4">
    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-red-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['pending_payment'] }}</div>
            <div class="text-sm opacity-90">Pending Payment</div>
        </div>
        <div class="bg-blue-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['today_orders'] }}</div>
            <div class="text-sm opacity-90">Orders Closed</div>
        </div>
        <div class="bg-orange-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['pending_parcels'] }}</div>
            <div class="text-sm opacity-90">Active Parcels</div>
        </div>
        <div class="bg-green-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['paid_today'] }}</div>
            <div class="text-sm opacity-90">Paid Today</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="font-bold mb-3 text-sm">Today's Orders (Hourly)</h3>
        <canvas id="ordersChart" style="max-height: 200px;"></canvas>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b flex justify-between items-center">
            <h3 class="font-bold text-sm">Recent Payments</h3>
            <a href="{{ route('cashier.handover.create') }}"
               class="flex items-center gap-1 bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">
                💵 Cash Handover
            </a>
        </div>
        <div class="divide-y">
            @forelse($recentPayments as $order)
                <div class="p-3">
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            @if($order->is_parcel)
                                <span style="background:#ea580c;color:#fff;font-size:12px;font-weight:800;padding:1px 8px;border-radius:6px;">📦 Parcel</span>
                            @else
                                <span class="font-bold text-sm">Table {{ $order->table?->table_number }}</span>
                            @endif
                            <span class="text-xs text-gray-500 ml-2">{{ $order->paid_at ? $order->paid_at->format('h:i A') : 'N/A' }}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">
                            {{ ucfirst($order->payment_mode ?? 'cash') }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-600">{{ $order->orderItems->count() }} items</div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 text-sm">No payments today</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('ordersChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($hours),
            datasets: [{
                label: 'Orders',
                data: @json($counts),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });
</script>
@endsection
