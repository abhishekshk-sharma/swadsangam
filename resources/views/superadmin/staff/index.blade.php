@extends('layouts.superadmin')
@section('title', 'All Staff')
@section('content')
@php
$th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;';
$td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;';
$roleColors=['manager'=>'background:#ede9fe;color:#6d28d9;','waiter'=>'background:#dbeafe;color:#1d4ed8;','chef'=>'background:#fef3c7;color:#92400e;','cashier'=>'background:#dcfce7;color:#15803d;'];
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-users" style="margin-right:8px;color:#d97706;"></i>All Staff</h1>
        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">All employees across all tenants</p>
    </div>
    <a href="/superadmin/staff/create" style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-plus"></i> Add Staff
    </a>
</div>

{{-- Filters --}}
<div class="content-card" style="margin-bottom:20px;">
    <div style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">Tenant</label>
                <select name="tenant_id" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;min-width:160px;">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ $selectedTenant == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">Role</label>
                <select name="role" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;min-width:130px;">
                    <option value="">All Roles</option>
                    @foreach(['manager','waiter','chef','cashier'] as $r)
                        <option value="{{ $r }}" {{ $selectedRole === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">Search</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Name or email…"
                       style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;min-width:180px;">
            </div>
            <button type="submit" style="padding:9px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Filter</button>
            @if($selectedTenant || $selectedRole || $search)
                <a href="/superadmin/staff" style="padding:9px 20px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Name</th>
                    <th style="{{ $th }}">Email</th>
                    <th style="{{ $th }}">Phone</th>
                    <th style="{{ $th }}">Role</th>
                    <th style="{{ $th }}">Tenant</th>
                    <th style="{{ $th }}">Branch</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $emp)
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#d97706;flex-shrink:0;">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;">{{ $emp->name }}</span>
                        </div>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->email }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->phone ?? '—' }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $roleColors[$emp->role] ?? 'background:#f3f4f6;color:#374151;' }}">{{ ucfirst($emp->role) }}</span>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->tenant->name ?? '—' }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $emp->branch->name ?? '—' }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $emp->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="{{ $td }}">
                        <div style="display:flex;gap:6px;">
                            <a href="/superadmin/staff/{{ $emp->id }}/edit" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;"><i class="fas fa-pen"></i></a>
                            <form action="/superadmin/staff/{{ $emp->id }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete {{ $emp->name }}?')">
                                @csrf @method('DELETE')
                                <button style="display:inline-flex;align-items:center;padding:5px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="padding:48px;text-align:center;color:#9ca3af;"><i class="fas fa-users" style="font-size:36px;display:block;margin-bottom:12px;color:#e5e7eb;"></i>No staff found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($staff->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;">{{ $staff->links() }}</div>
    @endif
</div>
@endsection
