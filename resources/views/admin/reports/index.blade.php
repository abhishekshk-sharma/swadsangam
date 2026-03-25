@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<style>
    .filter-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        margin-bottom: 24px;
    }
    .filter-header {
        padding: 16px 24px;
        border-bottom: 1px solid #e3e6e8;
        background: #f9f9f9;
        border-radius: 8px 8px 0 0;
    }
    .filter-body { padding: 24px; }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: #232f3e;
        margin-bottom: 8px;
    }
    .form-select, .form-control {
        border: 1px solid #d5d9d9;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 14px;
    }
    .form-select:focus, .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        outline: none;
    }
    .btn-primary {
        background: #3b82f6;
        border: 1px solid #3b82f6;
        color: #fff;
        padding: 8px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
    }
    .btn-primary:hover { background: #2563eb; border-color: #2563eb; }
    .btn-secondary {
        background: #fff;
        border: 1px solid #d5d9d9;
        color: #232f3e;
        padding: 8px 24px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 14px;
    }
    .btn-secondary:hover { background: #f7f7f7; }
    .btn-success {
        background: #067d62;
        border: 1px solid #067d62;
        color: #fff;
        padding: 8px 20px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 13px;
    }
    .btn-success:hover { background: #055a47; }
    .stat-card-report {
        background: #fff;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        text-align: center;
    }
    .stat-card-report h5 {
        font-size: 13px;
        color: #666;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-card-report h2 {
        font-size: 32px;
        font-weight: 700;
        color: #232f3e;
        margin: 0;
    }
    .alert-info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 16px 20px;
        border-radius: 4px;
    }
</style>

{{-- Always-visible summary stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card-report" style="border-left: 4px solid #4facfe;">
            <h5>Orders Today</h5>
            <h2>{{ $stats['orders_today'] }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-report" style="border-left: 4px solid #43e97b;">
            <h5>Revenue Today</h5>
            <h2>₹{{ number_format($stats['revenue_today'], 2) }}</h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-report" style="border-left: 4px solid #f7971e;">
            <h5>{{ now()->format('F Y') }} Revenue</h5>
            <h2>₹{{ number_format($stats['revenue_this_month'], 2) }}</h2>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-header">
        <h5 class="mb-0" style="font-weight: 600; color: #232f3e;">
            <i class="fas fa-filter me-2"></i>Filter Orders
        </h5>
    </div>
    <div class="filter-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" id="filterForm">
            <div class="row g-3">
                @if(isset($branches) && $branches->count() > 0)
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-store me-1"></i>Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">Filter Type</label>
                    <select name="filter_type" class="form-select" id="filterType" required>
                        <option value="">Select Filter</option>
                        <option value="date" {{ request('filter_type') === 'date' ? 'selected' : '' }}>By Date</option>
                        <option value="month" {{ request('filter_type') === 'month' ? 'selected' : '' }}>By Month</option>
                        <option value="year" {{ request('filter_type') === 'year' ? 'selected' : '' }}>By Year</option>
                    </select>
                </div>
                <div class="col-md-3" id="dateFilter" style="display: none;">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3" id="monthFilter" style="display: none;">
                    <label class="form-label">Select Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
                </div>
                <div class="col-md-3" id="yearFilter" style="display: none;">
                    <label class="form-label">Select Year</label>
                    <input type="number" name="year" class="form-control" min="2020" max="2099" value="{{ request('year') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-primary">Apply Filter</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(request()->has('filter_type'))
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stat-card-report" style="border-left: 4px solid #4facfe;">
            <h5>Total Orders (Filtered)</h5>
            <h2>{{ $totalOrders }}</h2>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card-report" style="border-left: 4px solid #43e97b;">
            <h5>Total Revenue (Filtered, Paid)</h5>
            <h2>₹{{ number_format($totalRevenue, 2) }}</h2>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-table me-2"></i>Orders Report
        </h5>
        <form method="GET" action="{{ route('admin.reports.export') }}" class="d-inline">
            <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
            <input type="hidden" name="date" value="{{ request('date') }}">
            <input type="hidden" name="month" value="{{ request('month') }}">
            <input type="hidden" name="year" value="{{ request('year') }}">
            <input type="hidden" name="branch_id" value="{{ request('branch_id') }}">
            <button type="submit" class="btn-success">
                <i class="fas fa-download me-1"></i> Download Excel
            </button>
        </form>
    </div>
    <div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th style="min-width:80px;">Order ID</th>
                        <th style="min-width:90px;">Table</th>
                        <th style="min-width:220px;">Items</th>
                        <th style="min-width:110px;">Total Amount</th>
                        <th style="min-width:90px;">Status</th>
                        <th style="min-width:110px;">Payment Mode</th>
                        <th style="min-width:110px;">Created By</th>
                        <th style="min-width:140px;">Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->is_parcel ? '📦 Parcel' : 'Table ' . ($order->table?->table_number ?? '?') }}</td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($order->orderItems as $item)
                                <span style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:5px;padding:2px 8px;font-size:12.5px;white-space:nowrap;">
                                    {{ $item->menuItem?->name ?? '[Deleted Item]' }}
                                    <span style="background:#3b82f6;color:#fff;border-radius:3px;padding:0 5px;font-size:11px;font-weight:700;">×{{ $item->quantity }}</span>
                                </span>
                            @endforeach
                            </div>
                        </td>
                        <td><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
                        <td>
                            <span class="badge-custom badge-{{ $order->status }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>{{ $order->payment_mode ? ucfirst($order->payment_mode) : '-' }}</td>
                        <td>{{ $order->user->name ?? '-' }}</td>
                        <td style="font-size: 13px; color: #666;">{{ $order->created_at->format('d-m-Y h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color: #666;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: #ddd; margin-bottom: 16px; display: block;"></i>
                            No orders found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="alert-info">
    <i class="fas fa-info-circle me-2"></i> Please select a filter type to view detailed reports
</div>
@endif

<script>
document.getElementById('filterType').addEventListener('change', function() {
    document.getElementById('dateFilter').style.display = 'none';
    document.getElementById('monthFilter').style.display = 'none';
    document.getElementById('yearFilter').style.display = 'none';
    if (this.value === 'date') document.getElementById('dateFilter').style.display = 'block';
    else if (this.value === 'month') document.getElementById('monthFilter').style.display = 'block';
    else if (this.value === 'year') document.getElementById('yearFilter').style.display = 'block';
});

document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filterType').value;
    if (filterType === 'date') document.getElementById('dateFilter').style.display = 'block';
    else if (filterType === 'month') document.getElementById('monthFilter').style.display = 'block';
    else if (filterType === 'year') document.getElementById('yearFilter').style.display = 'block';
});
</script>
@endsection
