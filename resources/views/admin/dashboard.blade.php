@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<style>
    .section-title { font-size:18px;font-weight:600;color:#232f3e; }
    .view-all-link { color:#3b82f6;text-decoration:none;font-size:14px;font-weight:600; }
    .view-all-link:hover { color:#2563eb; }
    .table-scroll-wrap { overflow-x:auto; }
    .overview-table { width:100%;min-width:520px; }
    .overview-table thead th { padding:12px 16px;font-size:12px;font-weight:600;color:#666;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #e3e6e8;background:#fafafa;white-space:nowrap; }
    .overview-table tbody td { padding:12px 16px;border-bottom:1px solid #f0f0f0;color:#232f3e;font-size:14px;white-space:nowrap; }
    .overview-table tbody tr:hover { background:#f9f9f9; }
    .empty-state { text-align:center;padding:40px 20px;color:#666; }
    .empty-state i { font-size:48px;color:#ddd;margin-bottom:16px;display:block; }

    /* ── Table Grid Cards ── */
    .tbl-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 10px;
    }
    .tbl-card {
        position: relative;
        border-radius: 14px;
        padding: 14px 8px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: transform .18s, box-shadow .18s;
        cursor: pointer;
        min-width: 0;
    }
    .tbl-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.12); }
    .tbl-card.free     { border: 2px solid #22c55e; background: linear-gradient(145deg,#f0fdf4,#dcfce7); }
    .tbl-card.occupied { border: 2px solid #ef4444; background: linear-gradient(145deg,#fff5f5,#fee2e2); }
    .tbl-dot { width:10px;height:10px;border-radius:50%;position:absolute;top:9px;right:9px; }
    .tbl-dot.free     { background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.2); }
    .tbl-dot.occupied { background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.2);animation:pulse-dot 1.6s ease-in-out infinite; }
    @keyframes pulse-dot { 0%,100%{box-shadow:0 0 0 3px rgba(239,68,68,.2)} 50%{box-shadow:0 0 0 6px rgba(239,68,68,.08)} }
    .tbl-icon   { font-size:20px;margin-bottom:1px; }
    .tbl-number { font-size:14px;font-weight:800;color:#1e293b;letter-spacing:.3px;text-align:center;word-break:break-all; }
    .tbl-badge  { font-size:9px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.3px;white-space:nowrap; }
    .tbl-badge.free     { background:#22c55e;color:#fff; }
    .tbl-badge.occupied { background:#ef4444;color:#fff; }
    .tbl-timer { font-size:10px;font-weight:700;font-family:monospace;padding:2px 7px;border-radius:20px;background:#fee2e2;color:#b91c1c;white-space:nowrap; }
    .tbl-timer.warn { background:#fef9c3;color:#a16207; }
    .tbl-timer.ok   { background:#dcfce7;color:#15803d; }

    /* header top row */
    .tbl-header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    .tbl-stats-pill {
        font-size:12px;font-weight:600;color:#64748b;
        background:#f1f5f9;padding:4px 12px;border-radius:20px;
        white-space:nowrap;
    }

    /* search + filter row */
    .tbl-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .tbl-search {
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: 7px 12px 7px 34px; font-size: 13px;
        flex: 1; min-width: 140px; max-width: 220px;
        outline: none; background: #fff;
        transition: border-color .2s;
    }
    .tbl-search:focus { border-color: #3b82f6; }
    .tbl-search-wrap { position: relative; flex:1; min-width:140px; max-width:220px; }
    .tbl-search-wrap i { position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px; }
    .tbl-pills { display:flex;flex-wrap:wrap;gap:6px;flex:1; }
    .tbl-cat-pill {
        padding: 5px 13px; border-radius: 20px; font-size: 12px; font-weight: 600;
        cursor: pointer; border: 1.5px solid #e2e8f0; background: #fff; color: #475569;
        transition: all .15s; white-space:nowrap;
    }
    .tbl-cat-pill.active, .tbl-cat-pill:hover { background:#2563eb;color:#fff;border-color:#2563eb; }

    /* legend */
    .tbl-legend { display:flex;align-items:center;gap:5px;font-size:12px;color:#64748b;font-weight:500; }
    .tbl-legend-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0; }
    .tbl-legends { display:flex;align-items:center;gap:12px;flex-wrap:wrap; }

    /* category section divider */
    .cat-section-header { display:flex;align-items:center;gap:8px;margin-bottom:12px; }
    .cat-section-label  { font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.8px;white-space:nowrap; }
    .cat-section-count  { font-size:11px;font-weight:600;background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:20px;white-space:nowrap; }
    .cat-section-line   { flex:1;height:1px;background:#e2e8f0; }

    @media (max-width: 480px) {
        .tbl-grid { grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap:8px; }
        .tbl-card { padding:12px 6px 10px; }
        .tbl-number { font-size:13px; }
        .tbl-header-top { flex-direction:column; align-items:flex-start; }
        .tbl-search-wrap, .tbl-search { max-width:100%; width:100%; }
    }
</style>

<div class="row g-4 mb-4">
    <!-- Tables Overview -->
    <div class="col-12">
        <div class="content-card">
            {{-- Header --}}
            <div class="card-header" style="flex-direction:column;align-items:stretch;gap:12px;">
                <div class="tbl-header-top">
                    <h2 class="section-title"><i class="fas fa-border-all me-2" style="color:#3b82f6;"></i>Tables Overview</h2>
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <div class="tbl-legends">
                            <div class="tbl-legend"><div class="tbl-legend-dot" style="background:#22c55e;"></div>Free</div>
                            <div class="tbl-legend"><div class="tbl-legend-dot" style="background:#ef4444;"></div>Occupied</div>
                        </div>
                        @php
                            $totalTables    = $recentTables->count();
                            $occupiedTables = $recentTables->where('is_occupied', true)->count();
                            $freeTables     = $totalTables - $occupiedTables;
                        @endphp
                        <span class="tbl-stats-pill">{{ $freeTables }} free &nbsp;·&nbsp; {{ $occupiedTables }} occupied</span>
                    </div>
                </div>
                {{-- Search + Category filter --}}
                @php $allCats = $recentTables->pluck('category.name')->filter()->unique()->values(); @endphp
                <div class="tbl-controls">
                    <div class="tbl-search-wrap">
                        <i class="fas fa-search"></i>
                        <input id="tableSearch" type="text" class="tbl-search" placeholder="Search table...">
                    </div>
                    <div class="tbl-pills">
                        <button class="tbl-cat-pill active" data-cat="all">All</button>
                        @foreach($allCats as $cat)
                            <button class="tbl-cat-pill" data-cat="{{ $cat }}">{{ $cat }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Grid --}}
            <div style="padding:16px;" id="tablesGrid">
                @php $grouped = $recentTables->groupBy(fn($t) => $t->category->name ?? 'Uncategorized'); @endphp
                @forelse($grouped as $catName => $tables)
                    <div class="cat-section" data-cat="{{ $catName }}" style="margin-bottom:20px;">
                        <div class="cat-section-header">
                            <span class="cat-section-label">{{ $catName }}</span>
                            <span class="cat-section-count">{{ $tables->count() }} tables</span>
                            <div class="cat-section-line"></div>
                        </div>
                        <div class="tbl-grid">
                            @foreach($tables as $table)
                                @php
                                    $activeOrder = $table->orders->first();
                                    $secs = $activeOrder ? (int) $activeOrder->created_at->diffInSeconds(now()) : null;
                                    $mins = $secs !== null ? (int)($secs / 60) : null;
                                    $timerClass = $secs !== null ? ($secs >= 1800 ? 'late' : ($secs >= 900 ? 'warn' : 'ok')) : '';
                                @endphp
                                <a class="tbl-card {{ $table->is_occupied ? 'occupied' : 'free' }}"
                                   data-name="{{ strtolower($table->table_number) }}"
                                   data-cat="{{ $catName }}"
                                   data-ts="{{ $activeOrder ? $activeOrder->created_at->timestamp : '' }}"
                                   href="{{ route('admin.orders.create', array_filter(['table_id' => $table->id, 'branch_id' => $table->branch_id])) }}">
                                    <div class="tbl-dot {{ $table->is_occupied ? 'occupied' : 'free' }}"></div>
                                    <div class="tbl-icon">{{ $table->is_occupied ? '🍽️' : '🪑' }}</div>
                                    <div class="tbl-number">{{ $table->table_number }}</div>
                                    <span class="tbl-badge {{ $table->is_occupied ? 'occupied' : 'free' }}">
                                        {{ $table->is_occupied ? 'Occupied' : 'Free' }}
                                    </span>
                                    @if($table->is_occupied && $mins !== null)
                                        <div class="tbl-timer {{ $timerClass }}" data-timer>⏱ {{ $mins }}m</div>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="empty-state"><i class="fas fa-border-all"></i><p>No tables yet</p></div>
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
    const catBtns     = document.querySelectorAll('.tbl-cat-pill');
    let activeCat = 'all';

    function applyFilters() {
        const q = searchInput.value.toLowerCase().trim();
        document.querySelectorAll('.cat-section').forEach(section => {
            const cat = section.dataset.cat;
            const catMatch = activeCat === 'all' || activeCat === cat;
            let anyVisible = false;
            section.querySelectorAll('.tbl-card').forEach(card => {
                const show = catMatch && (!q || card.dataset.name.includes(q));
                card.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });
            section.style.display = (catMatch && anyVisible) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', applyFilters);

    catBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            catBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeCat = this.dataset.cat;
            applyFilters();
        });
    });

    // Live timer tick every 30s
    function tickTimers() {
        const now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.tbl-card[data-ts]').forEach(card => {
            const ts = parseInt(card.dataset.ts);
            if (!ts) return;
            const elapsed = now - ts;
            const m = Math.floor(elapsed / 60);
            const timerEl = card.querySelector('[data-timer]');
            if (!timerEl) return;
            timerEl.textContent = '⏱ ' + m + 'm';
            timerEl.className = 'tbl-timer ' + (elapsed >= 1800 ? 'late' : elapsed >= 900 ? 'warn' : 'ok');
        });
    }
    tickTimers();
    setInterval(tickTimers, 30000);
});
</script>
@endsection
