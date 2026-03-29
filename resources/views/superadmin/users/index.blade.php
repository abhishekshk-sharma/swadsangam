@extends('layouts.superadmin')
@section('title', 'Admin Users')
@section('content')
@php
$th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;';
$td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;';
@endphp

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-user-shield" style="margin-right:8px;color:#d97706;"></i>Restaurant Admins</h1>
        <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Restaurant owner/admin accounts</p>
    </div>
    <a href="/superadmin/users/create" style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
        <i class="fas fa-plus"></i> Add User
    </a>
</div>

<div class="content-card">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Name</th>
                    <th style="{{ $th }}">Email</th>
                    <th style="{{ $th }}">Tenant</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;{{ $user->role === 'super_admin' ? 'background:#ede9fe;color:#6d28d9;' : 'background:#dbeafe;color:#1d4ed8;' }}">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;color:#111827;">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $user->email }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $user->role === 'super_admin' ? 'background:#ede9fe;color:#6d28d9;' : 'background:#dbeafe;color:#1d4ed8;' }}">
                            {{ $user->role === 'super_admin' ? 'Super Admin' : 'Restaurant Admin' }}
                        </span>
                    </td>
                    <td style="{{ $td }}color:#6b7280;">{{ $user->tenant->name ?? '—' }}</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $user->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="{{ $td }}">
                        <div style="display:flex;gap:6px;">
                            <a href="/superadmin/users/{{ $user->id }}/edit" style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;"><i class="fas fa-pen"></i> Edit</a>
                            <form action="/superadmin/users/{{ $user->id }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button style="display:inline-flex;align-items:center;padding:5px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#9ca3af;"><i class="fas fa-user-shield" style="font-size:36px;display:block;margin-bottom:12px;color:#e5e7eb;"></i>No users found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
