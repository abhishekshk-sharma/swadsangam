@extends('layouts.manager')
@section('title', 'Reports')
@section('content')

<div class="content-card mb-4">
    <div class="card-header"><div class="card-title"><i class="fas fa-filter"></i> Filter Orders</div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('manager.reports.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter Type</label>
                    <select name="filter_type" class="form-select" id="filterType">
                        <option value="">Select Filter</option>
                        <option value="date"  {{ request('filter_type') === 'date'  ? 'selected' : '' }}>By Date</option>
                        <option value="month" {{ request('filter_type') === 'month' ? 'selected' : '' }}>By Month</option>
                        <option value="year"  {{ request('filter_type') === 'year'  ? 'selected' : '' }}>By Year</option>
                    </select>
                </div>
                <div class="col-md-3" id="dateFilter" style="display:none;">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3" id="monthFilter" style="display:none;">
                    <label class="form-label">Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-3" id="yearFilter" style="display:none;">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" min="2020" max="2099" value="{{ request('year') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <a href="{{ route('manager.reports.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(request()->has('filter_type'))
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div style="background:#fff;border-radius:8px;padding:24px;border:1px solid #e3e6e8;border-left:4px solid #4facfe;text-align:center;">
            <div style="font-size:13px;color:#666;text-transform:uppercase;margin-bottom:12px;">Total Orders</div>
            <div style="font-size:32px;font-weight:700;color:#232f3e;">{{ $totalOrders }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div style="background:#fff;border-radius:8px;padding:24px;border:1px solid #e3e6e8;border-left:4px solid #43e97b;text-align:center;">
            <div style="font-size:13px;color:#666;text-transform:uppercase;margin-bottom:12px;">Total Revenue</div>
            <div style="font-size:32px;font-weight:700;color:#232f3e;">₹{{ number_format($totalRevenue, 2) }}</div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-table"></i> Orders Report</div>
        <form method="GET" action="{{ route('manager.reports.export') }}" class="d-inline">
            <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
            <input type="hidden" name="date"        value="{{ request('date') }}">
            <input type="hidden" name="month"       value="{{ request('month') }}">
            <input type="hidden" name="year"        value="{{ request('year') }}">
            <button type="submit" class="btn btn-sm" style="background:#067d62;color:#fff;border:none;padding:6px 16px;border-radius:4px;font-weight:600;">
                <i class="fas fa-download me-1"></i> Export Excel
            </button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Order ID</th><th>Table</th><th>Items</th><th>Amount</th><th>Status</th><th>Payment</th><th>Created By</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>#{{ $order->id }}</strong></td>
                    <td>{{ $order->is_parcel ? '📦 Parcel' : 'Table ' . ($order->table?->table_number ?? 'N/A') }}</td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($order->orderItems as $item)
                            <span style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:5px;padding:2px 8px;font-size:12.5px;">
                                {{ $item->menuItem->name }} <span style="background:#3b82f6;color:#fff;border-radius:3px;padding:0 5px;font-size:11px;font-weight:700;">×{{ $item->quantity }}</span>
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
                    <td><span class="badge badge-info">{{ ucfirst($order->status) }}</span></td>
                    <td>{{ $order->payment_mode ? ucfirst($order->payment_mode) : '-' }}</td>
                    <td>{{ $order->user?->name ?? '-' }}</td>
                    <td style="font-size:13px;color:#666;">{{ $order->created_at->format('d-m-Y h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5" style="color:#666;"><i class="fas fa-inbox" style="font-size:48px;color:#ddd;display:block;margin-bottom:16px;"></i>No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Select a filter type to view reports</div>
@endif

<script>
const ft = document.getElementById('filterType');
function toggleFields() {
    ['dateFilter','monthFilter','yearFilter'].forEach(id => document.getElementById(id).style.display = 'none');
    const map = { date:'dateFilter', month:'monthFilter', year:'yearFilter' };
    if (map[ft.value]) document.getElementById(map[ft.value]).style.display = 'block';
}
ft.addEventListener('change', toggleFields);
document.addEventListener('DOMContentLoaded', toggleFields);
</script>
@endsection
