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
        <div class="bg-green-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">₹{{ number_format($stats['today_revenue'], 2) }}</div>
            <div class="text-sm opacity-90">Today Revenue</div>
        </div>
        <div class="bg-blue-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">{{ $stats['today_orders'] }}</div>
            <div class="text-sm opacity-90">Orders Closed</div>
        </div>
        <div class="bg-purple-500 text-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold">₹{{ number_format($stats['cash_collected'], 2) }}</div>
            <div class="text-sm opacity-90">Cash Collected</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="font-bold mb-3 text-sm">Today's Revenue (Hourly)</h3>
        <canvas id="revenueChart" style="max-height: 200px;"></canvas>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h3 class="font-bold text-sm">Recent Payments</h3>
        </div>
        <div class="divide-y">
            @forelse($recentPayments as $order)
                <div class="p-3">
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            <span class="font-bold text-sm">Table {{ $order->table->table_number }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $order->paid_at ? $order->paid_at->format('h:i A') : 'N/A' }}</span>
                        </div>
                        <span class="font-bold text-green-600">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="text-xs text-gray-600">{{ ucfirst($order->payment_mode ?? 'cash') }}</div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500 text-sm">No payments today</div>
            @endforelse
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($hours),
            datasets: [{
                label: 'Revenue (₹)',
                data: @json($revenues),
                backgroundColor: 'rgba(147, 51, 234, 0.7)',
                borderColor: 'rgb(147, 51, 234)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });
</script>
@endsection
