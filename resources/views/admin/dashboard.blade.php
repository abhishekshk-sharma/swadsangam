@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<style>
    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
        display: block;
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    .stat-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        transform: translateY(-4px);
        border-color: #3b82f6;
    }
    .stat-card:hover::before {
        transform: translateX(100%);
    }
    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }
    .fa-table, .fa-utensils, 
    .fa-shopping-cart, .fa-rupee-sign{
        font-size: 24px;
    }
    .stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #fff;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #232f3e;
        margin: 12px 0 4px;
        line-height: 1;
    }
    .stat-label {
        font-size: 13px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    .stat-trend {
        font-size: 12px;
        color: #43e97b;
        margin-top: 8px;
        font-weight: 600;
    }
    .stat-trend i {
        margin-right: 4px;
    }
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #232f3e;
    }
    .view-all-link {
        color: #3b82f6;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .view-all-link:hover {
        color: #2563eb;
    }
    .table-scroll-wrap { overflow-x: auto; }
    .overview-table {
        width: 100%;
        min-width: 520px;
    }
    .overview-table thead th {
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e3e6e8;
        background: #fafafa;
        white-space: nowrap;
    }
    .overview-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #232f3e;
        font-size: 14px;
        white-space: nowrap;
    }
    .overview-table tbody tr:hover {
        background-color: #f9f9f9;
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }
    .empty-state i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 16px;
        display: block;
    }
</style>

<div class="row g-2 mb-4">
    <div class="col-md-4">
        <a href="{{ route('admin.tables.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Tables</div>
                    <div class="stat-value">{{ $stats['tables'] }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i>View All Tables</div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-table"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('admin.menu.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Menu Items</div>
                    <div class="stat-value">{{ $stats['menu_items'] }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i>View All Items</div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-utensils"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('admin.cook.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Orders Today</div>
                    <div class="stat-value">{{ $stats['orders_today'] }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i>View All Orders</div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('admin.reports.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Monthly Revenue</div>
                    <div class="stat-value">₹{{ number_format($stats['revenue_this_month'], 2) }}</div>
                    <div class="stat-trend"><i class="fas fa-chart-line"></i>{{ now()->format('F Y') }}</div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </a>
    </div>

    
    <div class="col-md-4">
        <a href="{{ route('admin.reports.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Revenue Today</div>
                    <div class="stat-value">₹{{ number_format($stats['revenue_today'], 2) }}</div>
                    <div class="stat-trend"><i class="fas fa-arrow-up"></i>View Reports</div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </a>
    </div>


    <div class="col-md-4">
        <a href="{{ route('admin.handover.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Cash Handovers</div>
                    <div class="stat-value">{{ $stats['pending_handovers'] }}</div>
                    <div class="stat-trend"><i class="fas fa-clock"></i>Pending Approval</div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </a>
    </div>
</div>



<div class="row g-4 mb-4">
    <!-- All Tables Grid -->
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-table me-2"></i>Tables Overview</h2>
                    <a href="/admin/tables" class="view-all-link">Manage <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="p-3">
                @php
                    $grouped = $recentTables->groupBy(fn($t) => $t->category->name ?? 'Uncategorized');
                @endphp
                @forelse($grouped as $catName => $tables)
                    <div class="mb-3">
                        <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">{{ $catName }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            @foreach($tables as $table)
                                @php
                                    $activeOrder = $table->orders->first();
                                    $mins = $activeOrder ? (int) $activeOrder->created_at->diffInMinutes(now()) : null;
                                @endphp
                                <a href="{{ route('admin.cook.index', array_filter(['table_id' => $table->id, 'status' => $table->is_occupied ? ($activeOrder?->status ?? null) : null])) }}" style="
                                    width:110px;min-height:80px;border-radius:10px;padding:10px 8px;
                                    border:2px solid {{ $table->is_occupied ? '#ef4444' : '#22c55e' }};
                                    background:{{ $table->is_occupied ? '#fef2f2' : '#f0fdf4' }};
                                    display:flex;flex-direction:column;align-items:center;justify-content:center;
                                    text-align:center;gap:4px;text-decoration:none;
                                ">
                                    <div style="font-weight:700;font-size:14px;color:#1e293b;">{{ $table->table_number }}</div>
                                    <span style="
                                        font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;
                                        background:{{ $table->is_occupied ? '#ef4444' : '#22c55e' }};
                                        color:#fff;
                                    ">{{ $table->is_occupied ? 'Occupied' : 'Free' }}</span>
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

    <!-- Recent Menu Items -->
    <div class="col-md-12">
        <div class="content-card">
            <div class="card-header">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-utensils me-2"></i>Recent Menu Items</h2>
                    <a href="/admin/menu" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="table-scroll-wrap">
                @if($recentMenuItems->count())
                    <table class="overview-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMenuItems as $item)
                            <tr>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td>{{ $item->category->name ?? 'N/A' }}</td>
                                <td><strong>₹{{ number_format($item->price, 2) }}</strong></td>
                                <td>
                                    <span class="badge-custom {{ $item->is_available ? 'badge-completed' : 'badge-pending' }}">
                                        {{ $item->is_available ? 'Available' : 'Unavailable' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="fas fa-utensils"></i>
                        <p>No menu items yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>



<!-- Recent Orders -->
<div class="content-card mb-4">
    <div class="card-header">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h2>
            <a href="/admin/cook" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="table-scroll-wrap">
        @if($recentOrders->count())
            <table class="overview-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Table</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td><strong>#{{ $order->id }}</strong></td>
                        <td>{{ $order->is_parcel ? '📦 Parcel' : ('Table ' . ($order->table?->table_number ?? 'N/A')) }}</td>
                        <td>{{ $order->orderItems->count() }} items</td>
                        <td>
                            <span class="badge-custom badge-{{ $order->status }}">{{ $order->status }}</span>
                        </td>
                        <td><strong>₹{{ number_format($order->total_amount, 2) }}</strong></td>
                        <td style="color: #666; font-size: 13px;">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <p>No orders yet</p>
            </div>
        @endif
    </div>
</div>



<!-- Recent Employees -->
<div class="content-card">
    <div class="card-header">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-users me-2"></i>Recent Employees</h2>
            <a href="/admin/employees" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="table-scroll-wrap">
        @if($recentEmployees->count())
            <table class="overview-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentEmployees as $employee)
                    <tr>
                        <td><strong>{{ $employee->name }}</strong></td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->phone ?? 'N/A' }}</td>
                        <td><span class="badge-custom badge-processing">{{ ucfirst($employee->role) }}</span></td>
                        <td>
                            <span class="badge-custom {{ $employee->is_active ? 'badge-completed' : 'badge-pending' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="color: #666; font-size: 13px;">{{ $employee->created_at ? $employee->created_at->diffForHumans() : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No employees yet</p>
            </div>
        @endif
    </div>
</div>
@endsection
