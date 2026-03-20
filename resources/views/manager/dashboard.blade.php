@extends('layouts.manager')

@section('title', 'Dashboard')

@section('content')
<style>
    .stat-card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 2px 8px rgba(0,0,0,.08); border:1px solid #e3e6e8; transition:all .3s ease; cursor:pointer; text-decoration:none; display:block; }
    .stat-card:hover { box-shadow:0 8px 24px rgba(0,0,0,.15); transform:translateY(-4px); border-color:#3b82f6; }
    .stat-icon { width:38px; height:38px; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#fff; transition:all .3s ease; box-shadow:0 4px 12px rgba(0,0,0,.15); }
    .stat-value { font-size:36px; font-weight:700; color:#232f3e; margin:12px 0 4px; line-height:1; }
    .stat-label { font-size:13px; color:#666; text-transform:uppercase; letter-spacing:.5px; font-weight:600; }
    .stat-trend { font-size:12px; color:#43e97b; margin-top:8px; font-weight:600; }
    .section-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    .section-title { font-size:18px; font-weight:600; color:#232f3e; }
    .view-all-link { color:#3b82f6; text-decoration:none; font-size:14px; font-weight:600; }
    .view-all-link:hover { color:#2563eb; }
    .overview-table { width:100%; min-width:520px; }
    .overview-table thead th { padding:12px 16px; font-size:12px; font-weight:600; color:#666; text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #e3e6e8; background:#fafafa; }
    .overview-table tbody td { padding:12px 16px; border-bottom:1px solid #f0f0f0; color:#232f3e; font-size:14px; }
    .overview-table tbody tr:hover { background:#f9f9f9; }
    .empty-state { text-align:center; padding:40px 20px; color:#666; }
    .empty-state i { font-size:48px; color:#ddd; margin-bottom:16px; display:block; }
</style>

<div class="row g-2 mb-4">
    <div class="col-md-4">
        <a href="{{ route('manager.tables.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Branch Tables</div>
                    <div class="stat-value">{{ $stats['tables'] }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i> View Tables</div>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                    <i class="fas fa-table"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('manager.menu.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Menu Items</div>
                    <div class="stat-value">{{ $stats['menu_items'] }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i> View Menu</div>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                    <i class="fas fa-utensils"></i>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-4">
        <a href="{{ route('manager.staff.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Branch Staff</div>
                    <div class="stat-value">{{ $stats['employees'] }}</div>
                    <div class="stat-trend"><i class="fas fa-users"></i> Your Branch</div>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('manager.reports.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="stat-value">₹{{ number_format($stats['revenue_this_month'], 2) }}</div>
                    <div class="stat-trend"><i class="fas fa-chart-line"></i> {{ now()->format('F Y') }}</div>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#f7971e,#ffd200);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('manager.reports.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Revenue Today</div>
                    <div class="stat-value">₹{{ number_format($stats['revenue_today'], 2) }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i> View Reports</div>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#43e97b,#38f9d7);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('manager.cook.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Orders Today</div>
                    <div class="stat-value">{{ $stats['orders_today'] }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i> View Orders</div>
                </div>
                <div class="stat-icon" style="background:linear-gradient(135deg,#4facfe,#00f2fe);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>

    
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <div class="section-header w-100">
                    <h2 class="section-title"><i class="fas fa-table me-2"></i>Tables Overview</h2>
                    <a href="{{ route('manager.tables.index') }}" class="view-all-link">Manage <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="p-3">
                @php $grouped = $recentTables->groupBy(fn($t) => $t->category->name ?? 'Uncategorized'); @endphp
                @forelse($grouped as $catName => $tables)
                    <div class="mb-3">
                        <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">{{ $catName }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            @foreach($tables as $table)
                                @php $activeOrder = $table->orders->first(); $mins = $activeOrder ? (int)$activeOrder->created_at->diffInMinutes(now()) : null; @endphp
                                <a href="{{ route('manager.cook.index', ['table_id' => $table->id]) }}" style="width:110px;min-height:80px;border-radius:10px;padding:10px 8px;border:2px solid {{ $table->is_occupied ? '#ef4444' : '#22c55e' }};background:{{ $table->is_occupied ? '#fef2f2' : '#f0fdf4' }};display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:4px;text-decoration:none;">
                                    <div style="font-weight:700;font-size:14px;color:#1e293b;">{{ $table->table_number }}</div>
                                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ $table->is_occupied ? '#ef4444' : '#22c55e' }};color:#fff;">{{ $table->is_occupied ? 'Occupied' : 'Free' }}</span>
                                    @if($table->is_occupied && $mins !== null)
                                        <div style="font-size:11px;color:#ef4444;font-weight:600;">{{ $mins }}m ago</div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><i class="fas fa-table"></i><p>No tables yet</p></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="content-card mb-4">
    <div class="card-header">
        <div class="section-header w-100">
            <h2 class="section-title"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h2>
            <a href="{{ route('manager.cook.index') }}" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div style="overflow-x:auto;">
        @if($recentOrders->count())
            <table class="overview-table">
                <thead><tr><th>Order ID</th><th>Table</th><th>Items</th><th>Status</th><th>Amount</th><th>Time</th></tr></thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->is_parcel ? '📦 Parcel' : ('Table ' . ($order->table?->table_number ?? 'N/A')) }}</td>
                        <td>{{ $order->orderItems->count() }} items</td>
                        <td><span class="badge badge-info">{{ $order->status }}</span></td>
                        <td><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
                        <td style="color:#666;font-size:13px;">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state"><i class="fas fa-shopping-cart"></i><p>No orders yet</p></div>
        @endif
    </div>
</div>
@endsection
