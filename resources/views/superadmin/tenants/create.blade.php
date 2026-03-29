@extends('layouts.superadmin')
@section('title', 'Add Tenant')
@section('content')
@php
$inp='width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;box-sizing:border-box;';
$lbl='display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;';
$err='font-size:12px;color:#dc2626;margin-top:4px;';
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/superadmin/tenants" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">← Back</a>
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-plus" style="margin-right:8px;color:#d97706;"></i>Add Tenant</h1>
</div>

<div class="content-card" style="max-width:600px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <div style="font-size:14px;font-weight:600;color:#374151;">Restaurant Details</div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px;">A default "Main" branch will be created automatically.</div>
    </div>
    <div style="padding:24px;">
        <form action="/superadmin/tenants" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Restaurant Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required style="{{ $inp }}" placeholder="e.g. Swad Sangam">
                @error('name')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Slug (URL identifier) *</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required style="{{ $inp }}" placeholder="e.g. swad-sangam">
                <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Only letters, numbers, dashes and underscores</div>
                @error('slug')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:24px;">
                <label style="{{ $lbl }}">Domain <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
                <input type="text" name="domain" value="{{ old('domain') }}" style="{{ $inp }}" placeholder="e.g. restaurant.com">
                @error('domain')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:1;background:#d97706;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;"><i class="fas fa-save" style="margin-right:6px;"></i>Create Tenant</button>
                <a href="/superadmin/tenants" style="flex:1;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:11px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;text-align:center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
