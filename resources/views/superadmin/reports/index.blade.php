@extends('layouts.superadmin')
@section('title', 'Platform Reports')
@section('content')
@php
$th='padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;';
$td='padding:12px 14px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;vertical-align:top;';
$ss=['paid'=>'background:#dcfce7;color:#15803d;','pending'=>'background:#fef9c3;color:#a16207;','cancelled'=>'background:#fee2e2;color:#dc2626;','preparing'=>'background:#dbeafe;color:#1d4ed8;','ready'=>'background:#d1fae5;color:#065f46;','served'=>'background:#ede9fe;color:#6d28d9;'];
@endphp

<div style="margin-bottom:24px;">
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-chart-bar" style="margin-right:8px;color:#d97706;"></i>Platform Reports</h1>
    <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">{{ now()->format('l, F j, Y') }}</p>
</div>

{{-- Summary Stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-left:4px solid #3b82f6;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Orders Today</div>
        <div style="font-size:28px;font-weight:700;color:#111827;">{{ $stats['orders_today'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-left:4px solid #16a34a;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Revenue Today</div>
        <div style="font-size:28px;font-weight:700;color:#16a34a;">₹{{ number_format($stats['revenue_today'], 0) }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-left:4px solid #d97706;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">{{ now()->format('F Y') }} Revenue</div>
        <div style="font-size:28px;font-weight:700;color:#111827;">₹{{ number_format($stats['revenue_this_month'], 0) }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="content-card" style="margin-bottom:20px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <strong style="font-size:14px;color:#374151;"><i class="fas fa-filter" style="margin-right:8px;color:#d97706;"></i>Filter Orders</strong>
    </div>
    <div style="padding:20px;">
        <form method="GET" action="/superadmin/reports" id="filterForm">
            <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
                @foreach(['date'=>'By Date','month'=>'Monthly','year'=>'Yearly'] as $val=>$label)
                <button type="button" onclick="setFilterType('{{ $val }}')" id="ftab-{{ $val }}"
                    style="padding:8px 20px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:2px solid {{ request('filter_type')===$val ? '#d97706' : '#d1d5db' }};background:{{ request('filter_type')===$val ? '#fffbeb' : '#fff' }};color:{{ request('filter_type')===$val ? '#92400e' : '#6b7280' }};transition:all .15s;">
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <input type="hidden" name="filter_type" id="filterType" value="{{ request('filter_type') }}">
            <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Tenant</label>
                    <select name="tenant_id" id="tenantSelect" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                        <option value="">All Tenants</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Branch</label>
                    <select name="branch_id" id="branchSelect" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:{{ request('filter_type')==='date' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;" id="panel-date">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                </div>
                <div style="display:{{ request('filter_type')==='month' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;" id="panel-month">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Month</label>
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                </div>
                <div style="display:{{ request('filter_type')==='year' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:120px;" id="panel-year">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Year</label>
                    <select name="year" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                        @for($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <button type="submit" style="padding:9px 22px;background:#d97706;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Apply</button>
                    <a href="/superadmin/reports" style="padding:9px 22px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

@if(request()->has('filter_type') || request()->has('tenant_id'))
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-left:4px solid #3b82f6;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Total Orders (Filtered)</div>
        <div style="font-size:28px;font-weight:700;color:#111827;">{{ $totalOrders }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-left:4px solid #16a34a;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Revenue (Paid, Filtered)</div>
        <div style="font-size:28px;font-weight:700;color:#16a34a;">₹{{ number_format($totalRevenue, 0) }}</div>
    </div>
</div>

<div class="content-card">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-table" style="color:#d97706;margin-right:8px;"></i>Orders</div>
        <a href="/superadmin/reports/export?{{ http_build_query(request()->only('tenant_id','branch_id','filter_type','date','month','year')) }}"
           style="display:inline-flex;align-items:center;gap:6px;background:#059669;color:#fff;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            <i class="fas fa-download"></i> Export Excel
        </a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Order #</th>
                    <th style="{{ $th }}">Tenant</th>
                    <th style="{{ $th }}">Table</th>
                    <th style="{{ $th }}">Items</th>
                    <th style="{{ $th }}">Total</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Payment</th>
                    <th style="{{ $th }}">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-weight:700;color:#111827;">#{{ $order->id }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $order->tenant->name ?? '—' }}</td>
                    <td style="{{ $td }}">{{ $order->is_parcel ? '📦 Parcel' : 'T'.($order->table?->table_number ?? '?') }}</td>
                    <td style="{{ $td }}">
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($order->orderItems as $item)
                            <span style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:4px;padding:2px 6px;font-size:11px;white-space:nowrap;">
                                {{ $item->menuItem?->name ?? '[Deleted]' }} <strong>×{{ $item->quantity }}</strong>
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td style="{{ $td }}font-weight:700;">₹{{ number_format($order->total_amount, 2) }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $ss[$order->status] ?? 'background:#f3f4f6;color:#6b7280;' }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $order->payment_mode ? ucfirst($order->payment_mode) : '—' }}</td>
                    <td style="{{ $td }}color:#6b7280;white-space:nowrap;">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="8" style="padding:48px;text-align:center;color:#9ca3af;"><i class="fas fa-inbox" style="font-size:36px;display:block;margin-bottom:12px;color:#e5e7eb;"></i>No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:16px 20px;border-radius:8px;font-size:14px;">
    <i class="fas fa-info-circle" style="margin-right:8px;"></i> Select a filter above to view detailed reports.
</div>
@endif

<script>
function setFilterType(type) {
    document.getElementById('filterType').value = type;
    ['date','month','year'].forEach(function(t) {
        var active = t === type;
        var btn = document.getElementById('ftab-' + t);
        if (btn) { btn.style.borderColor = active ? '#d97706' : '#d1d5db'; btn.style.background = active ? '#fffbeb' : '#fff'; btn.style.color = active ? '#92400e' : '#6b7280'; }
        var panel = document.getElementById('panel-' + t);
        if (panel) panel.style.display = active ? 'flex' : 'none';
    });
}
document.getElementById('tenantSelect').addEventListener('change', function () {
    var branchSelect = document.getElementById('branchSelect');
    branchSelect.innerHTML = '<option value="">All Branches</option>';
    if (!this.value) return;
    fetch('/superadmin/staff/branches/' + this.value)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            data.forEach(function(b) {
                var opt = document.createElement('option');
                opt.value = b.id; opt.textContent = b.name;
                branchSelect.appendChild(opt);
            });
        });
});
</script>
@endsection
