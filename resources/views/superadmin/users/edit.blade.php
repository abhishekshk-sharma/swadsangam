@extends('layouts.superadmin')
@section('title', 'Edit User')
@section('content')
@php
$inp='width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;box-sizing:border-box;';
$lbl='display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;';
$err='font-size:12px;color:#dc2626;margin-top:4px;';
$isAdmin = $user->role === 'admin';
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/superadmin/users" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">← Back</a>
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-pen" style="margin-right:8px;color:#d97706;"></i>Edit User — {{ $user->name }}</h1>
</div>

<div class="content-card" style="max-width:600px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:14px;font-weight:600;color:#374151;">User Details</div>
        {{-- Role badge — read only --}}
        <span style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;{{ $user->role === 'super_admin' ? 'background:#ede9fe;color:#6d28d9;' : 'background:#dbeafe;color:#1d4ed8;' }}">
            <i class="fas fa-lock" style="margin-right:5px;font-size:10px;opacity:.7;"></i>
            {{ $user->role === 'super_admin' ? 'Super Admin' : 'Restaurant Admin' }}
        </span>
    </div>
    <div style="padding:24px;">
        <form action="/superadmin/users/{{ $user->id }}" method="POST">
            @csrf @method('PUT')

            {{-- Tenant (only for admin role) --}}
            @if($isAdmin)
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Restaurant *</label>
                <select name="tenant_id" required style="{{ $inp }}">
                    <option value="">Select Restaurant</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ old('tenant_id', $user->tenant_id) == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
                    @endforeach
                </select>
                @error('tenant_id')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            @endif

            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="{{ $inp }}">
                @error('name')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="{{ $inp }}">
                @error('email')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Password <span style="font-weight:400;color:#9ca3af;">(leave blank to keep)</span></label>
                <input type="password" name="password" style="{{ $inp }}">
                @error('password')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#d97706;">
                    <span style="font-size:13px;font-weight:600;color:#374151;">Active</span>
                </label>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:1;background:#d97706;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;"><i class="fas fa-save" style="margin-right:6px;"></i>Update User</button>
                <a href="/superadmin/users" style="flex:1;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:11px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;text-align:center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
