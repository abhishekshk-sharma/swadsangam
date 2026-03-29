@extends('layouts.superadmin')
@section('title', 'Dashboard')
@section('content')

<div style="margin-bottom:24px;">
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-chart-pie" style="margin-right:8px;color:#d97706;"></i>Platform Overview</h1>
    <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">{{ now()->format('l, F j, Y') }}</p>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:28px;">
    @php
        $cards = [
            ['label'=>'Total Tenants',   'value'=>$stats['tenants'],         'sub'=>$stats['active_tenants'].' active',    'color'=>'#3b82f6', 'icon'=>'fa-building'],
            ['label'=>'Branches',        'value'=>$stats['total_branches'],  'sub'=>'across all tenants',                  'color'=>'#d97706', 'icon'=>'fa-store'],
            ['label'=>'Admin Users',     'value'=>$stats['total_admins'],    'sub'=>$stats['super_admins'].' super admins','color'=>'#8b5cf6', 'icon'=>'fa-user-shield'],
            ['label'=>'Total Staff',     'value'=>$stats['total_employees'], 'sub'=>'all roles',                           'color'=>'#16a34a', 'icon'=>'fa-users'],
            ['label'=>'Total Orders',    'value'=>number_format($stats['total_orders']), 'sub'=>'platform-wide',           'color'=>'#f59e0b', 'icon'=>'fa-receipt'],
            ['label'=>'Total Revenue',   'value'=>'₹'.number_format($stats['total_revenue'],0), 'sub'=>'paid orders',     'color'=>'#059669', 'icon'=>'fa-rupee-sign'],
        ];
    @endphp
    @foreach($cards as $card)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-left:4px solid {{ $card['color'] }};display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">{{ $card['label'] }}</div>
            <div style="font-size:26px;font-weight:700;color:#111827;line-height:1.1;">{{ $card['value'] }}</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:4px;">{{ $card['sub'] }}</div>
        </div>
        <div style="width:40px;height:40px;border-radius:10px;background:{{ $card['color'] }}1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fas {{ $card['icon'] }}" style="color:{{ $card['color'] }};font-size:16px;"></i>
        </div>
    </div>
    @endforeach
</div>

{{-- Recent Tenants --}}
<div class="content-card">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-building" style="color:#d97706;margin-right:8px;"></i>Recent Tenants</div>
        <a href="/superadmin/tenants" style="font-size:13px;color:#d97706;text-decoration:none;font-weight:600;">View all →</a>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;'; @endphp
                    <th style="{{ $th }}">Name</th>
                    <th style="{{ $th }}">Slug</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Orders</th>
                    <th style="{{ $th }}">Created</th>
                    <th style="{{ $th }}">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTenants as $tenant)
                @php $td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;'; @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-weight:700;color:#111827;">{{ $tenant->name }}</td>
                    <td style="{{ $td }}font-family:monospace;font-size:12px;color:#6b7280;">{{ $tenant->slug }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $tenant->status==='active' ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td style="{{ $td }}font-weight:600;">{{ $tenant->orders_count }}</td>
                    <td style="{{ $td }}color:#6b7280;white-space:nowrap;">{{ $tenant->created_at->format('d M Y') }}</td>
                    <td style="{{ $td }}">
                        <a href="/superadmin/tenants/{{ $tenant->id }}"
                           style="display:inline-flex;align-items:center;gap:4px;padding:5px 12px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:40px;text-align:center;color:#9ca3af;">No tenants yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
