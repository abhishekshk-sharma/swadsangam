@extends('layouts.waiter')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-4">
    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-yellow-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['pending'] }}</div>
            <div class="text-sm opacity-90">Pending Orders</div>
        </div>
        <div class="bg-blue-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['preparing'] }}</div>
            <div class="text-sm opacity-90">Preparing</div>
        </div>
        <div class="bg-green-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['ready'] }}</div>
            <div class="text-sm opacity-90">Ready to Serve</div>
        </div>
        <div class="bg-purple-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['total_today'] }}</div>
            <div class="text-sm opacity-90">Total Today</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="font-bold mb-3 text-sm">Today's Orders (Hourly)</h3>
        <canvas id="ordersChart" style="max-height: 200px;"></canvas>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm">My Recent Orders</h3>
        </div>
        <div class="divide-y">
            @forelse($recentOrders as $order)
                <div class="p-3">
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            <span class="font-bold text-sm">Table {{ $order->table->table_number }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $order->created_at->format('h:i A') }}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $order->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-600">{{ $order->items ? $order->items->count() : 0 }} items</div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 text-sm">No active orders</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctx, {
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
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });
</script>
@endsection
