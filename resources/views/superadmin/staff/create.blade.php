@extends('layouts.superadmin')
@section('title', 'Add Staff')
@section('content')
@php
$inp='width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;box-sizing:border-box;';
$lbl='display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;';
$err='font-size:12px;color:#dc2626;margin-top:4px;';
@endphp

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="/superadmin/staff" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">← Back</a>
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-user-plus" style="margin-right:8px;color:#d97706;"></i>Add Staff</h1>
</div>

<div class="content-card" style="max-width:640px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <div style="font-size:14px;font-weight:600;color:#374151;">Staff Details</div>
    </div>
    <div style="padding:24px;">
        <form action="/superadmin/staff" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="{{ $lbl }}">Tenant *</label>
                    <select name="tenant_id" id="tenant_id" required style="{{ $inp }}">
                        <option value="">Select Tenant</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('tenant_id')<div style="{{ $err }}">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="{{ $lbl }}">Branch</label>
                    <select name="branch_id" id="branch_id" style="{{ $inp }}">
                        <option value="">Select Branch</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                <div>
                    <label style="{{ $lbl }}">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required style="{{ $inp }}">
                    @error('name')<div style="{{ $err }}">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="{{ $lbl }}">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" style="{{ $inp }}">
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label style="{{ $lbl }}">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required style="{{ $inp }}">
                @error('email')<div style="{{ $err }}">{{ $message }}</div>@enderror
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                <div>
                    <label style="{{ $lbl }}">Password *</label>
                    <input type="password" name="password" required style="{{ $inp }}">
                    @error('password')<div style="{{ $err }}">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label style="{{ $lbl }}">Role *</label>
                    <select name="role" required style="{{ $inp }}">
                        <option value="">Select Role</option>
                        @foreach(['manager','waiter','chef','cashier'] as $r)
                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')<div style="{{ $err }}">{{ $message }}</div>@enderror
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" style="flex:1;background:#d97706;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;"><i class="fas fa-save" style="margin-right:6px;"></i>Create Staff</button>
                <a href="/superadmin/staff" style="flex:1;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:11px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;text-align:center;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('tenant_id').addEventListener('change', function () {
    const branchSelect = document.getElementById('branch_id');
    branchSelect.innerHTML = '<option value="">Select Branch</option>';
    if (!this.value) return;
    fetch('/superadmin/staff/branches/' + this.value)
        .then(r => r.json())
        .then(data => data.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.id; opt.textContent = b.name;
            branchSelect.appendChild(opt);
        }));
});
</script>
@endsection
