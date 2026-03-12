@extends('layouts.waiter')

@section('title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <p class="text-sm text-gray-600">Welcome back!</p>
</div>

<div class="grid grid-cols-2 gap-3 mb-6">
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs">Available Tables</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['tables_available'] }}</p>
            </div>
            <div class="text-3xl">▦</div>
        </div>
    </div>
    
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs">Orders Today</p>
                <p class="text-2xl font-bold">{{ $stats['orders_today'] }}</p>
            </div>
            <div class="text-3xl">■</div>
        </div>
    </div>
    
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs">Revenue Today</p>
                <p class="text-xl font-bold">₹{{ number_format($stats['revenue_today'], 2) }}</p>
            </div>
            <div class="text-3xl">₹</div>
        </div>
    </div>
    
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-xs">My Orders</p>
                <p class="text-2xl font-bold text-blue-600">{{ $stats['my_orders_today'] }}</p>
            </div>
            <div class="text-3xl">◆</div>
        </div>
    </div>
</div>

<div class="space-y-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold mb-3">Last 7 Days Activity</h2>
        <canvas id="activityChart" class="w-full" style="max-height: 200px;"></canvas>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-bold mb-3">Revenue Trend</h2>
        <canvas id="revenueChart" class="w-full" style="max-height: 200px;"></canvas>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-lg font-bold">My Recent Orders</h2>
    </div>
    <div class="p-4">
        @if($recentOrders->count())
            <div class="space-y-3">
                @foreach($recentOrders as $order)
                <div class="border-b pb-3 last:border-0">
                    <div class="flex justify-between items-start mb-1">
                        <div>
                            <p class="font-semibold">#{{ $order->id }} - Table {{ $order->table->table_number ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">{{ $order->status }}</span>
                    </div>
                    <p class="text-sm font-bold">₹{{ number_format($order->total_amount, 2) }}</p>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8 text-sm">No orders yet</p>
        @endif
    </div>
</div>

<script>
const chartData = @json($chartData);

// Activity Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: chartData.map(d => new Date(d.date).toLocaleDateString('en-IN', {month: 'short', day: 'numeric'})),
        datasets: [{
            label: 'Orders',
            data: chartData.map(d => d.count),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: chartData.map(d => new Date(d.date).toLocaleDateString('en-IN', {month: 'short', day: 'numeric'})),
        datasets: [{
            label: 'Revenue (₹)',
            data: chartData.map(d => d.revenue),
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endsection
