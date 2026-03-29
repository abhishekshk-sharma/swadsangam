@extends('layouts.superadmin')
@section('title', 'Edit Branch')
@section('content')
@php
$inp='width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;box-sizing:border-box;';
$lbl='display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;';
$err='font-size:12px;color:#dc2626;margin-top:4px;';
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/superadmin/branches" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">← Back</a>
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-pen" style="margin-right:8px;color:#d97706;"></i>Edit Branch — {{ $branch->name }}</h1>
</div>

<div class="content-card" style="max-width:600px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <div style="font-size:13px;color:#6b7280;">Tenant: <strong style="color:#374151;">{{ $branch->tenant->name ?? '—' }}</strong></div>
    </div>
    <div style="padding:24px;">
        <form action="/superadmin/branches/{{ $branch->id }}" method="POST">
            @csrf @method('PUT')
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Branch Name *</label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required style="{{ $inp }}">
                @error('name')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Address</label>
                <input type="text" name="address" value="{{ old('address', $branch->address) }}" style="{{ $inp }}">
            </div>
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" style="{{ $inp }}">
            </div>
            <div style="margin-bottom:24px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:#d97706;">
                    <span style="font-size:13px;font-weight:600;color:#374151;">Active</span>
                </label>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:1;background:#d97706;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;"><i class="fas fa-save" style="margin-right:6px;"></i>Update Branch</button>
                <a href="/superadmin/branches" style="flex:1;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:11px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;text-align:center;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
