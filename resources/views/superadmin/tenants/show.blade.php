@extends('layouts.superadmin')
@section('title', $tenant->name)
@section('content')

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:#6b7280;">
    <a href="/superadmin/tenants" style="color:#d97706;text-decoration:none;font-weight:600;">Tenants</a>
    <i class="fas fa-chevron-right" style="font-size:10px;"></i>
    <span style="color:#374151;font-weight:600;">{{ $tenant->name }}</span>
</div>

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:52px;height:52px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#d97706;">
            {{ strtoupper(substr($tenant->name, 0, 1)) }}
        </div>
        <div>
            <h1 style="font-size:1.3rem;font-weight:700;color:#111827;margin:0;">{{ $tenant->name }}</h1>
            <div style="display:flex;align-items:center;gap:10px;margin-top:4px;">
                <span style="font-size:12px;font-family:monospace;color:#6b7280;">{{ $tenant->slug }}</span>
                <span style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600;{{ $tenant->status==='active' ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                    {{ ucfirst($tenant->status) }}
                </span>
            </div>
        </div>
    </div>
    <a href="/superadmin/tenants/{{ $tenant->id }}/edit"
       style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#374151;border:1px solid #d1d5db;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-pen"></i> Edit Tenant
    </a>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:24px;">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;border-left:4px solid #3b82f6;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Branches</div>
        <div style="font-size:28px;font-weight:700;color:#111827;">{{ $branches->count() }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;border-left:4px solid #8b5cf6;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Total Staff</div>
        <div style="font-size:28px;font-weight:700;color:#111827;">{{ $branches->sum('employees_count') + $unassignedStaff->count() }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;border-left:4px solid #16a34a;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Total Orders</div>
        <div style="font-size:28px;font-weight:700;color:#111827;">{{ $totalOrders }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;border-left:4px solid #d97706;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Total Revenue</div>
        <div style="font-size:22px;font-weight:700;color:#16a34a;">₹{{ number_format($totalRevenue, 0) }}</div>
    </div>
</div>

@if($branches->count() > 0)
{{-- Branches --}}
<div class="content-card" style="margin-bottom:24px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-store" style="color:#d97706;margin-right:8px;"></i>Branches</div>
        <a href="/superadmin/branches/create?tenant_id={{ $tenant->id }}"
           style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;">
            <i class="fas fa-plus"></i> Add Branch
        </a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:20px;">
        @foreach($branches as $branch)
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px;border-top:4px solid {{ $branch->is_active ? '#16a34a' : '#9ca3af' }};transition:box-shadow .2s;"
             onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow='none'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <div>
                    <div style="font-size:16px;font-weight:700;color:#111827;">{{ $branch->name }}</div>
                    @if($branch->address)
                        <div style="font-size:12px;color:#6b7280;margin-top:2px;">{{ $branch->address }}</div>
                    @endif
                </div>
                <span style="padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;{{ $branch->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#f3f4f6;color:#6b7280;' }}">
                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px;">
                <div style="text-align:center;background:#f9fafb;border-radius:8px;padding:8px;">
                    <div style="font-size:18px;font-weight:700;color:#2563eb;">{{ $branch->employees_count }}</div>
                    <div style="font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;">Staff</div>
                </div>
                <div style="text-align:center;background:#f9fafb;border-radius:8px;padding:8px;">
                    <div style="font-size:18px;font-weight:700;color:#8b5cf6;">{{ $branch->tables_count }}</div>
                    <div style="font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;">Tables</div>
                </div>
                <div style="text-align:center;background:#f9fafb;border-radius:8px;padding:8px;">
                    <div style="font-size:18px;font-weight:700;color:#16a34a;">{{ $branch->orders_count }}</div>
                    <div style="font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.3px;">Orders</div>
                </div>
            </div>
            @if($branch->upi_id)
                <div style="font-size:12px;color:#6b7280;margin-bottom:12px;"><i class="fas fa-mobile-alt" style="margin-right:4px;"></i>{{ $branch->upi_id }}</div>
            @endif
            <div style="display:flex;gap:8px;">
                <a href="/superadmin/branches/{{ $branch->id }}/view"
                   style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#2563eb;color:#fff;padding:8px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                    <i class="fas fa-eye"></i> View Details
                </a>
                <a href="/superadmin/branches/{{ $branch->id }}/edit"
                   style="display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;text-decoration:none;">
                    <i class="fas fa-pen"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($unassignedStaff->count() > 0)
{{-- Unassigned Staff --}}
<div class="content-card">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;">
        <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-user-slash" style="color:#9ca3af;margin-right:8px;"></i>Unassigned Staff <span style="font-size:13px;font-weight:400;color:#6b7280;">(not linked to any branch)</span></div>
    </div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;'; @endphp
                    <th style="{{ $th }}">Name</th>
                    <th style="{{ $th }}">Role</th>
                    <th style="{{ $th }}">Email</th>
                    <th style="{{ $th }}">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unassignedStaff as $emp)
                @php $td='padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;'; @endphp
                <tr>
                    <td style="{{ $td }}font-weight:600;">{{ $emp->name }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#f3f4f6;color:#374151;">{{ ucfirst($emp->role) }}</span>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->email }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $emp->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($branches->count() === 0 && $unassignedStaff->count() === 0)
<div style="text-align:center;padding:60px 20px;">
    <div style="font-size:48px;color:#d1d5db;margin-bottom:16px;"><i class="fas fa-store"></i></div>
    <div style="font-size:16px;font-weight:600;color:#374151;margin-bottom:8px;">No branches or staff yet</div>
    <a href="/superadmin/branches/create?tenant_id={{ $tenant->id }}"
       style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;margin-top:8px;">
        <i class="fas fa-plus"></i> Add First Branch
    </a>
</div>
@endif

@endsection
