@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<style>
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

<div class="row g-4 mb-4">
    <!-- All Tables Grid -->
    <div class="col-12">
        <div class="content-card">
            <div class="card-header">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-table me-2"></i>Tables Overview</h2>
                    <a href="/admin/tables" class="view-all-link">Manage <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                {{-- Search + Category filter --}}
                @php $allCats = $recentTables->pluck('category.name')->filter()->unique()->values(); @endphp
                <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-top:10px;">
                    <input id="tableSearch" type="text" placeholder="Search table..." style="border:1px solid #d5d9d9;border-radius:6px;padding:6px 12px;font-size:13px;width:180px;outline:none;">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        <button class="cat-btn active" data-cat="all" style="padding:4px 14px;border-radius:20px;border:1px solid #3b82f6;background:#3b82f6;color:#fff;font-size:12px;font-weight:600;cursor:pointer;">All</button>
                        @foreach($allCats as $cat)
                            <button class="cat-btn" data-cat="{{ $cat }}" style="padding:4px 14px;border-radius:20px;border:1px solid #d5d9d9;background:#fff;color:#374151;font-size:12px;font-weight:600;cursor:pointer;">{{ $cat }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="p-3" id="tablesGrid">
                @php
                    $grouped = $recentTables->groupBy(fn($t) => $t->category->name ?? 'Uncategorized');
                @endphp
                @forelse($grouped as $catName => $tables)
                    <div class="cat-section mb-3" data-cat="{{ $catName }}">
                        <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">{{ $catName }}</div>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            @foreach($tables as $table)
                                @php
                                    $activeOrder = $table->orders->first();
                                    $mins = $activeOrder ? (int) $activeOrder->created_at->diffInMinutes(now()) : null;
                                @endphp
                                <a class="table-card" data-name="{{ strtolower($table->table_number) }}" data-cat="{{ $catName }}"
                                   href="{{ route('admin.orders.create', array_filter(['table_id' => $table->id, 'branch_id' => $table->branch_id])) }}" style="
                                    width:110px;min-height:80px;border-radius:10px;padding:10px 8px;
                                    border:2px solid {{ $table->is_occupied ? '#ef4444' : '#22c55e' }};
                                    background:{{ $table->is_occupied ? '#fef2f2' : '#f0fdf4' }};
                                    display:flex;flex-direction:column;align-items:center;justify-content:center;
                                    text-align:center;gap:4px;text-decoration:none;
                                ">
                                    <div style="font-weight:700;font-size:14px;color:#1e293b;">{{ $table->table_number }}</div>
                                    <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;
                                        background:{{ $table->is_occupied ? '#ef4444' : '#22c55e' }};color:#fff;"
                                    >{{ $table->is_occupied ? 'Occupied' : 'Free' }}</span>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('tableSearch');
    const catBtns = document.querySelectorAll('.cat-btn');
    let activeCat = 'all';

    function applyFilters() {
        const q = searchInput.value.toLowerCase().trim();
        document.querySelectorAll('.cat-section').forEach(section => {
            const cat = section.dataset.cat;
            const catMatch = activeCat === 'all' || activeCat === cat;
            let anyVisible = false;
            section.querySelectorAll('.table-card').forEach(card => {
                const nameMatch = !q || card.dataset.name.includes(q);
                const show = catMatch && nameMatch;
                card.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });
            section.style.display = (catMatch && anyVisible) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);

    catBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            catBtns.forEach(b => {
                b.style.background = '#fff';
                b.style.color = '#374151';
                b.style.borderColor = '#d5d9d9';
            });
            this.style.background = '#3b82f6';
            this.style.color = '#fff';
            this.style.borderColor = '#3b82f6';
            activeCat = this.dataset.cat;
            applyFilters();
        });
    });
});
</script>
@endsection
