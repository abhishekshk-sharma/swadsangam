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
        border-color: #ff9900;
    }
    .stat-card:hover::before {
        transform: translateX(100%);
    }
    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
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
        color: #ff9900;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .view-all-link:hover {
        color: #ec8b00;
    }
    .overview-table {
        width: 100%;
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
    }
    .overview-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        color: #232f3e;
        font-size: 14px;
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
    <div class="col-md-6">
        <a href="{{ route('admin.tables.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Total Tables</div>
                    <div class="stat-value">{{ $stats['tables'] }}</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>View All Tables
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-table"></i>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-6">
        <a href="{{ route('admin.menu.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Menu Items</div>
                    <div class="stat-value">{{ $stats['menu_items'] }}</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>View All Items
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-utensils"></i>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-6">
        <a href="{{ route('admin.cook.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Orders Today</div>
                    <div class="stat-value">{{ $stats['orders_today'] }}</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>View All Orders
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-md-6">
        <a href="{{ route('admin.reports.index') }}" class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">Revenue Today</div>
                    <div class="stat-value">₹{{ number_format($stats['revenue_today'], 2) }}</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i>View Reports
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </a>
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
    <div>
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
                        <td>Table {{ $order->table->table_number ?? 'N/A' }}</td>
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

<div class="row g-4 mb-4">
    <!-- Recent Tables -->
    <div class="col-md-6">
        <div class="content-card">
            <div class="card-header">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-table me-2"></i>Recent Tables</h2>
                    <a href="/admin/tables" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div>
                @if($recentTables->count())
                    <table class="overview-table">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Category</th>
                                <th>Capacity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTables as $table)
                            <tr>
                                <td><strong>{{ $table->table_number }}</strong></td>
                                <td>{{ $table->category->name ?? 'N/A' }}</td>
                                <td>{{ $table->capacity }} seats</td>
                                <td>
                                    <span class="badge-custom {{ $table->is_occupied ? 'badge-pending' : 'badge-completed' }}">
                                        {{ $table->is_occupied ? 'Occupied' : 'Available' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <i class="fas fa-table"></i>
                        <p>No tables yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Menu Items -->
    <div class="col-md-6">
        <div class="content-card">
            <div class="card-header">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-utensils me-2"></i>Recent Menu Items</h2>
                    <a href="/admin/menu" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div>
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

<!-- Recent Employees -->
<div class="content-card">
    <div class="card-header">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-users me-2"></i>Recent Employees</h2>
            <a href="/admin/employees" class="view-all-link">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div>
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
                        <td style="color: #666; font-size: 13px;">{{ $employee->created_at->diffForHumans() }}</td>
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
