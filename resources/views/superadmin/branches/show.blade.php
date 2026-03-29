@extends('layouts.superadmin')
@section('title', $branch->name . ' — ' . $branch->tenant->name)
@section('content')

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:#6b7280;">
    <a href="/superadmin/tenants" style="color:#d97706;text-decoration:none;font-weight:600;">Tenants</a>
    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
    <a href="/superadmin/tenants/{{ $branch->tenant_id }}" style="color:#d97706;text-decoration:none;font-weight:600;">{{ $branch->tenant->name }}</a>
    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
    <span style="color:#374151;font-weight:600;">{{ $branch->name }}</span>
</div>

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.3rem;font-weight:700;color:#111827;margin:0;">
            <i class="fas fa-store" style="margin-right:8px;color:#d97706;"></i>{{ $branch->name }}
            <span style="font-size:13px;font-weight:400;color:#6b7280;margin-left:8px;">{{ $branch->tenant->name }}</span>
        </h1>
        <div style="display:flex;align-items:center;gap:10px;margin-top:6px;flex-wrap:wrap;">
            @if($branch->address)
                <span style="font-size:12px;color:#6b7280;"><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>{{ $branch->address }}</span>
            @endif
            @if($branch->upi_id)
                <span style="font-size:12px;color:#6b7280;"><i class="fas fa-mobile-alt" style="margin-right:4px;"></i>{{ $branch->upi_id }}</span>
            @endif
            @if($branch->gstSlab)
                <span style="font-size:12px;background:#f0fdf4;color:#15803d;padding:2px 8px;border-radius:6px;font-weight:600;">GST {{ $branch->gstSlab->cgst_rate + $branch->gstSlab->sgst_rate }}% ({{ $branch->gst_mode }})</span>
            @endif
            <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $branch->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                {{ $branch->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
    <a href="/superadmin/branches/{{ $branch->id }}/edit"
       style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#374151;border:1px solid #d1d5db;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-pen"></i> Edit Branch
    </a>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;margin-bottom:24px;">
    @php
        $statCards = [
            ['label'=>'Staff',          'value'=>$stats['total_staff'],    'color'=>'#8b5cf6'],
            ['label'=>'Tables',         'value'=>$stats['total_tables'],   'color'=>'#3b82f6'],
            ['label'=>'Occupied',       'value'=>$stats['occupied_tables'],'color'=>'#dc2626'],
            ['label'=>'Menu Items',     'value'=>$stats['total_menu'],     'color'=>'#d97706'],
            ['label'=>'Orders Today',   'value'=>$stats['orders_today'],   'color'=>'#16a34a'],
            ['label'=>'Revenue Today',  'value'=>'₹'.number_format($stats['revenue_today'],0), 'color'=>'#16a34a'],
            ['label'=>'Total Orders',   'value'=>$stats['total_orders'],   'color'=>'#6b7280'],
            ['label'=>'Total Revenue',  'value'=>'₹'.number_format($stats['total_revenue'],0), 'color'=>'#059669'],
        ];
    @endphp
    @foreach($statCards as $card)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;border-left:4px solid {{ $card['color'] }};">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">{{ $card['label'] }}</div>
        <div style="font-size:22px;font-weight:700;color:#111827;">{{ $card['value'] }}</div>
    </div>
    @endforeach
</div>

{{-- Section Tabs --}}
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid #e5e7eb;">
    @foreach(['staff'=>'Staff','tables'=>'Tables','menu'=>'Menu','orders'=>"Today's Orders"] as $tab=>$label)
    <button onclick="switchTab('{{ $tab }}')" id="tab-{{ $tab }}"
        style="padding:10px 22px;font-size:14px;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:3px solid {{ $tab==='staff' ? '#d97706' : 'transparent' }};color:{{ $tab==='staff' ? '#92400e' : '#6b7280' }};margin-bottom:-2px;transition:all .2s;">
        {{ $label }}
        @if($tab==='staff') <span style="background:#fef3c7;color:#92400e;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $stats['total_staff'] }}</span>
        @elseif($tab==='tables') <span style="background:#f3f4f6;color:#6b7280;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $stats['total_tables'] }}</span>
        @elseif($tab==='menu') <span style="background:#f3f4f6;color:#6b7280;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $stats['total_menu'] }}</span>
        @elseif($tab==='orders') <span style="background:#dcfce7;color:#15803d;border-radius:20px;padding:1px 7px;font-size:11px;margin-left:4px;">{{ $stats['orders_today'] }}</span>
        @endif
    </button>
    @endforeach
</div>

{{-- STAFF TAB --}}
<div id="section-staff">
<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;'; @endphp
                    <th style="{{ $th }}">Name</th>
                    <th style="{{ $th }}">Role</th>
                    <th style="{{ $th }}">Email</th>
                    <th style="{{ $th }}">Phone</th>
                    <th style="{{ $th }}">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $emp)
                @php $td='padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;'; @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#2563eb;flex-shrink:0;">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;">{{ $emp->name }}</span>
                        </div>
                    </td>
                    <td style="{{ $td }}">
                        @php $roleColors=['manager'=>'background:#ede9fe;color:#6d28d9;','waiter'=>'background:#dbeafe;color:#1d4ed8;','chef'=>'background:#fef3c7;color:#92400e;','cashier'=>'background:#dcfce7;color:#15803d;']; @endphp
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $roleColors[$emp->role] ?? 'background:#f3f4f6;color:#374151;' }}">{{ ucfirst($emp->role) }}</span>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->email }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->phone ?? '—' }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $emp->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;">No staff assigned to this branch.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

{{-- TABLES TAB --}}
<div id="section-tables" style="display:none;">
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
    @forelse($tables as $table)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px;border-top:4px solid {{ $table->is_occupied ? '#dc2626' : '#16a34a' }};text-align:center;">
        <div style="font-size:18px;font-weight:700;color:#111827;margin-bottom:6px;">{{ $table->table_number }}</div>
        @if($table->category)
            <div style="font-size:11px;background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:6px;display:inline-block;margin-bottom:8px;">{{ $table->category->name }}</div>
        @endif
        <div style="font-size:12px;color:#6b7280;margin-bottom:6px;"><i class="fas fa-users" style="margin-right:4px;"></i>{{ $table->capacity }} seats</div>
        <span style="padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;{{ $table->is_occupied ? 'background:#fee2e2;color:#dc2626;' : 'background:#dcfce7;color:#15803d;' }}">
            {{ $table->is_occupied ? 'Occupied' : 'Free' }}
        </span>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;">No tables in this branch.</div>
    @endforelse
</div>
</div>

{{-- MENU TAB --}}
<div id="section-menu" style="display:none;">
<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Item</th>
                    <th style="{{ $th }}">Category</th>
                    <th style="{{ $th }}">Price</th>
                    <th style="{{ $th }}">Available</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems as $item)
                @php $td='padding:11px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;'; @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($item->image)
                                <img src="{{ asset($item->image) }}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;flex-shrink:0;">
                            @else
                                <div style="width:36px;height:36px;border-radius:6px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-utensils" style="color:#9ca3af;font-size:14px;"></i></div>
                            @endif
                            <span style="font-weight:600;">{{ $item->name }}</span>
                        </div>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $item->category?->name ?? '—' }}</td>
                    <td style="{{ $td }}font-weight:700;color:#111827;">₹{{ number_format($item->price, 2) }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $item->is_available ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $item->is_available ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="padding:40px;text-align:center;color:#9ca3af;">No menu items in this branch.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

{{-- ORDERS TAB --}}
<div id="section-orders" style="display:none;">
<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Order</th>
                    <th style="{{ $th }}">Table</th>
                    <th style="{{ $th }}">Items</th>
                    <th style="{{ $th }}">Amount</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($todayOrders as $order)
                @php
                    $td='padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;vertical-align:top;';
                    $ss=['paid'=>'background:#dcfce7;color:#15803d;','pending'=>'background:#fef9c3;color:#a16207;','preparing'=>'background:#dbeafe;color:#1d4ed8;','ready'=>'background:#d1fae5;color:#065f46;','served'=>'background:#ede9fe;color:#6d28d9;','cancelled'=>'background:#fee2e2;color:#dc2626;'];
                    $s=$ss[$order->status]??'background:#f3f4f6;color:#6b7280;';
                @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-weight:700;color:#111827;">#{{ $order->id }}</td>
                    <td style="{{ $td }}">
                        @if($order->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">📦 Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">T{{ $order->table?->table_number ?? '?' }}</span>
                        @endif
                    </td>
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
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $s }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td style="{{ $td }}color:#6b7280;white-space:nowrap;">{{ $order->created_at->format('h:i A') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:40px;text-align:center;color:#9ca3af;">No orders today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>

<script>
function switchTab(tab) {
    ['staff','tables','menu','orders'].forEach(function(t) {
        var sec = document.getElementById('section-' + t);
        var btn = document.getElementById('tab-' + t);
        var active = t === tab;
        if (sec) sec.style.display = active ? '' : 'none';
        if (btn) {
            btn.style.borderBottomColor = active ? '#d97706' : 'transparent';
            btn.style.color = active ? '#92400e' : '#6b7280';
        }
    });
}
</script>
@endsection
