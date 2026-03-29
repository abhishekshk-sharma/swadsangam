@extends('layouts.superadmin')
@section('title', 'Branches')
@section('content')
@php
$th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;';
$td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;';
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-store" style="margin-right:8px;color:#d97706;"></i>Branches</h1>
        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">All branches across all tenants</p>
    </div>
    <a href="/superadmin/branches/create" style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-plus"></i> Add Branch
    </a>
</div>

{{-- Filter --}}
<div class="content-card" style="margin-bottom:20px;">
    <div style="padding:14px 20px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">Tenant</label>
                <select name="tenant_id" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;min-width:180px;">
                    <option value="">All Tenants</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ $selectedTenant == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" style="padding:9px 20px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Filter</button>
            @if($selectedTenant)
                <a href="/superadmin/branches" style="padding:9px 20px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Branch</th>
                    <th style="{{ $th }}">Tenant</th>
                    <th style="{{ $th }}">Address</th>
                    <th style="{{ $th }}">Phone</th>
                    <th style="{{ $th }}">Staff</th>
                    <th style="{{ $th }}">Tables</th>
                    <th style="{{ $th }}">Orders</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-weight:700;color:#111827;">{{ $branch->name }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $branch->tenant->name ?? '—' }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $branch->address ?? '—' }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $branch->phone ?? '—' }}</td>
                    <td style="{{ $td }}font-weight:600;text-align:center;">{{ $branch->employees_count }}</td>
                    <td style="{{ $td }}font-weight:600;text-align:center;">{{ $branch->tables_count }}</td>
                    <td style="{{ $td }}font-weight:600;text-align:center;">{{ $branch->orders_count }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $branch->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="{{ $td }}">
                        <div style="display:flex;gap:6px;">
                            <a href="/superadmin/branches/{{ $branch->id }}/view" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;"><i class="fas fa-eye"></i></a>
                            <a href="/superadmin/branches/{{ $branch->id }}/edit" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;"><i class="fas fa-pen"></i></a>
                            <form action="/superadmin/branches/{{ $branch->id }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete this branch?')">
                                @csrf @method('DELETE')
                                <button style="display:inline-flex;align-items:center;padding:5px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="padding:48px;text-align:center;color:#9ca3af;"><i class="fas fa-store" style="font-size:36px;display:block;margin-bottom:12px;color:#e5e7eb;"></i>No branches found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
