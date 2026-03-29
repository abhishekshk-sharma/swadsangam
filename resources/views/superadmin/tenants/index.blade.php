@extends('layouts.superadmin')
@section('title', 'Tenants')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-building" style="margin-right:8px;color:#d97706;"></i>Tenants</h1>
        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">All restaurants on the platform</p>
    </div>
    <a href="/superadmin/tenants/create"
       style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-plus"></i> Add Tenant
    </a>
</div>

<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;'; @endphp
                    <th style="{{ $th }}">Restaurant</th>
                    <th style="{{ $th }}">Slug</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Tables</th>
                    <th style="{{ $th }}">Menu Items</th>
                    <th style="{{ $th }}">Orders</th>
                    <th style="{{ $th }}">Created</th>
                    <th style="{{ $th }}">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                @php $td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;'; @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#d97706;flex-shrink:0;">
                                {{ strtoupper(substr($tenant->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;color:#111827;">{{ $tenant->name }}</div>
                                @if($tenant->domain)
                                    <div style="font-size:11px;color:#6b7280;">{{ $tenant->domain }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="{{ $td }}font-family:monospace;font-size:12px;color:#6b7280;">{{ $tenant->slug }}</td>
                    <td style="{{ $td }}">
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $tenant->status==='active' ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td style="{{ $td }}font-weight:600;">{{ $tenant->tables_count }}</td>
                    <td style="{{ $td }}font-weight:600;">{{ $tenant->menu_items_count }}</td>
                    <td style="{{ $td }}font-weight:600;">{{ $tenant->orders_count }}</td>
                    <td style="{{ $td }}color:#6b7280;white-space:nowrap;">{{ $tenant->created_at->format('d M Y') }}</td>
                    <td style="{{ $td }}">
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="/superadmin/tenants/{{ $tenant->id }}"
                               style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="/superadmin/tenants/{{ $tenant->id }}/edit"
                               style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <form action="/superadmin/tenants/{{ $tenant->id }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete {{ $tenant->name }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:48px;text-align:center;">
                        <div style="font-size:40px;color:#d1d5db;margin-bottom:12px;"><i class="fas fa-building"></i></div>
                        <div style="font-size:14px;color:#6b7280;">No tenants yet.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
