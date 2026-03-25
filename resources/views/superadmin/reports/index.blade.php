@extends('layouts.superadmin')
@section('title', 'Reports')
@section('header', 'Platform Reports')

@section('content')
{{-- Summary Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-400">
        <p class="text-gray-500 text-xs uppercase tracking-wide">Orders Today</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['orders_today'] }}</h3>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-400">
        <p class="text-gray-500 text-xs uppercase tracking-wide">Revenue Today</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">₹{{ number_format($stats['revenue_today'], 2) }}</h3>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-orange-400">
        <p class="text-gray-500 text-xs uppercase tracking-wide">{{ now()->format('F Y') }} Revenue</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">₹{{ number_format($stats['revenue_this_month'], 2) }}</h3>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow p-5 mb-6">
    <form method="GET" action="/superadmin/reports" id="filterForm">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-gray-600 text-xs font-semibold mb-1 uppercase">Tenant</label>
                <select name="tenant_id" id="tenantSelect" class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-600 text-xs font-semibold mb-1 uppercase">Branch</label>
                <select name="branch_id" id="branchSelect" class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-gray-600 text-xs font-semibold mb-1 uppercase">Filter Type</label>
                <select name="filter_type" id="filterType" class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select</option>
                    <option value="date"  {{ request('filter_type') === 'date'  ? 'selected' : '' }}>By Date</option>
                    <option value="month" {{ request('filter_type') === 'month' ? 'selected' : '' }}>By Month</option>
                    <option value="year"  {{ request('filter_type') === 'year'  ? 'selected' : '' }}>By Year</option>
                </select>
            </div>
            <div id="dateWrap" class="{{ request('filter_type') === 'date' ? '' : 'hidden' }}">
                <label class="block text-gray-600 text-xs font-semibold mb-1 uppercase">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div id="monthWrap" class="{{ request('filter_type') === 'month' ? '' : 'hidden' }}">
                <label class="block text-gray-600 text-xs font-semibold mb-1 uppercase">Month</label>
                <input type="month" name="month" value="{{ request('month') }}" class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div id="yearWrap" class="{{ request('filter_type') === 'year' ? '' : 'hidden' }}">
                <label class="block text-gray-600 text-xs font-semibold mb-1 uppercase">Year</label>
                <input type="number" name="year" value="{{ request('year') }}" min="2020" max="2099" class="w-full px-3 py-2 border rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Apply</button>
                <a href="/superadmin/reports" class="px-4 py-2 bg-gray-100 border rounded text-sm hover:bg-gray-200">Clear</a>
            </div>
        </div>
    </form>
</div>

@if(request()->has('filter_type') || request()->has('tenant_id'))
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-400">
        <p class="text-gray-500 text-xs uppercase">Total Orders (Filtered)</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">{{ $totalOrders }}</h3>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-400">
        <p class="text-gray-500 text-xs uppercase">Revenue (Paid, Filtered)</p>
        <h3 class="text-3xl font-bold text-gray-800 mt-1">₹{{ number_format($totalRevenue, 2) }}</h3>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Orders</h3>
        <a href="/superadmin/reports/export?{{ http_build_query(request()->only('tenant_id','branch_id','filter_type','date','month','year')) }}"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
            <i class="fas fa-download mr-1"></i> Export Excel
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Order #</th>
                    <th class="px-4 py-3 text-left">Tenant</th>
                    <th class="px-4 py-3 text-left">Table</th>
                    <th class="px-4 py-3 text-left">Items</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Payment</th>
                    <th class="px-4 py-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">#{{ $order->id }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $order->tenant->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $order->is_parcel ? '📦 Parcel' : 'Table ' . ($order->table?->table_number ?? '?') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($order->orderItems as $item)
                                <span class="bg-gray-100 border rounded px-2 py-0.5 text-xs">
                                    {{ $item->menuItem?->name ?? '[Deleted]' }} ×{{ $item->quantity }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right font-medium">₹{{ number_format($order->total_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        @php $sc = ['paid'=>'green','pending'=>'yellow','cancelled'=>'red','processing'=>'blue','ready'=>'purple','served'=>'teal']; $col = $sc[$order->status] ?? 'gray'; @endphp
                        <span class="px-2 py-1 text-xs rounded bg-{{ $col }}-100 text-{{ $col }}-800">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->payment_mode ? ucfirst($order->payment_mode) : '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $order->created_at->format('d-m-Y h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-400">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="bg-blue-50 border border-blue-200 text-blue-700 px-5 py-4 rounded">
    <i class="fas fa-info-circle mr-2"></i> Select a filter to view detailed reports.
</div>
@endif

<script>
const filterType = document.getElementById('filterType');
const wraps = { date: 'dateWrap', month: 'monthWrap', year: 'yearWrap' };

function toggleFilters(val) {
    Object.values(wraps).forEach(id => document.getElementById(id).classList.add('hidden'));
    if (wraps[val]) document.getElementById(wraps[val]).classList.remove('hidden');
}

filterType.addEventListener('change', () => toggleFilters(filterType.value));

document.getElementById('tenantSelect').addEventListener('change', function () {
    const tenantId = this.value;
    const branchSelect = document.getElementById('branchSelect');
    branchSelect.innerHTML = '<option value="">All Branches</option>';
    if (!tenantId) return;
    fetch('/superadmin/staff/branches/' + tenantId)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.name;
                branchSelect.appendChild(opt);
            });
        });
});
</script>
@endsection
