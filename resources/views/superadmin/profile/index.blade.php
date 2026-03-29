@extends('layouts.superadmin')
@section('title', 'Super Admin Profile')
@section('content')
@php
$inp='width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;box-sizing:border-box;';
$lbl='display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;';
$err='font-size:12px;color:#dc2626;margin-top:4px;';
$th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;';
$td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;';
@endphp

<div style="margin-bottom:24px;">
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-crown" style="margin-right:8px;color:#d97706;"></i>Super Admin Management</h1>
    <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Manage your profile and all super admin accounts</p>
</div>

<div style="display:grid;grid-template-columns:360px 1fr;gap:24px;align-items:start;">

    {{-- LEFT: My Profile --}}
    <div>
        <div class="content-card" style="margin-bottom:20px;">
            <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#fffbeb;border-radius:12px 12px 0 0;">
                <div style="font-size:15px;font-weight:600;color:#92400e;"><i class="fas fa-user-circle" style="margin-right:8px;color:#d97706;"></i>My Profile</div>
            </div>
            <div style="padding:20px;">
                {{-- Avatar --}}
                <div style="text-align:center;margin-bottom:20px;">
                    <div style="width:72px;height:72px;border-radius:50%;background:#fef3c7;border:3px solid #fde68a;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#d97706;margin:0 auto 10px;">
                        {{ strtoupper(substr($me->name, 0, 1)) }}
                    </div>
                    <div style="font-weight:700;font-size:16px;color:#111827;">{{ $me->name }}</div>
                    <div style="font-size:13px;color:#6b7280;">{{ $me->email }}</div>
                    <span style="display:inline-block;margin-top:6px;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#ede9fe;color:#6d28d9;">
                        <i class="fas fa-crown" style="margin-right:4px;font-size:10px;"></i>Super Admin
                    </span>
                </div>

                <form action="{{ route('superadmin.profile.update') }}" method="POST">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label style="{{ $lbl }}">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $me->name) }}" required style="{{ $inp }}">
                        @error('name')<div style="{{ $err }}">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="{{ $lbl }}">Email *</label>
                        <input type="email" name="email" value="{{ old('email', $me->email) }}" required style="{{ $inp }}">
                        @error('email')<div style="{{ $err }}">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="{{ $lbl }}">New Password <span style="font-weight:400;color:#9ca3af;">(leave blank to keep)</span></label>
                        <input type="password" name="password" style="{{ $inp }}" placeholder="••••••••">
                        @error('password')<div style="{{ $err }}">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="{{ $lbl }}">Confirm Password</label>
                        <input type="password" name="password_confirmation" style="{{ $inp }}" placeholder="••••••••">
                    </div>
                    <button type="submit" style="width:100%;background:#d97706;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-save" style="margin-right:6px;"></i>Update My Profile
                    </button>
                </form>
            </div>
        </div>

        {{-- Add New Super Admin --}}
        <div class="content-card">
            <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
                <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-plus" style="color:#d97706;margin-right:8px;"></i>Add Super Admin</div>
            </div>
            <div style="padding:20px;">
                <form action="{{ route('superadmin.super-admins.store') }}" method="POST">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label style="{{ $lbl }}">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required style="{{ $inp }}" placeholder="Full name">
                        @error('name')<div style="{{ $err }}">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="{{ $lbl }}">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required style="{{ $inp }}" placeholder="email@example.com">
                        @error('email')<div style="{{ $err }}">{{ $message }}</div>@enderror
                    </div>
                    <div style="margin-bottom:20px;">
                        <label style="{{ $lbl }}">Password *</label>
                        <input type="password" name="password" required style="{{ $inp }}" placeholder="Min 6 characters">
                        @error('password')<div style="{{ $err }}">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" style="width:100%;background:#2563eb;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-user-plus" style="margin-right:6px;"></i>Add Super Admin
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- RIGHT: All Super Admins --}}
    <div class="content-card">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;">
            <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-crown" style="color:#d97706;margin-right:8px;"></i>All Super Admins <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $superAdmins->count() }} total)</span></div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="{{ $th }}">Name</th>
                        <th style="{{ $th }}">Email</th>
                        <th style="{{ $th }}">Status</th>
                        <th style="{{ $th }}">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($superAdmins as $sa)
                    {{-- View row --}}
                    <tr id="sarow-{{ $sa->id }}" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                        <td style="{{ $td }}">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#d97706;flex-shrink:0;">
                                    {{ strtoupper(substr($sa->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;color:#111827;">
                                        {{ $sa->name }}
                                        @if($sa->id === $me->id)
                                            <span style="font-size:11px;background:#fef3c7;color:#92400e;padding:1px 7px;border-radius:10px;margin-left:6px;font-weight:600;">You</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="{{ $td }}color:#6b7280;">{{ $sa->email }}</td>
                        <td style="{{ $td }}">
                            <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $sa->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                                {{ $sa->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="{{ $td }}">
                            <div style="display:flex;gap:6px;">
                                <button onclick="toggleSaEdit({{ $sa->id }})"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                @if($sa->id !== $me->id)
                                <form action="{{ route('superadmin.super-admins.destroy', $sa->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('Remove {{ $sa->name }} as Super Admin?')">
                                    @csrf @method('DELETE')
                                    <button style="display:inline-flex;align-items:center;padding:5px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    {{-- Inline edit row --}}
                    <tr id="saeditrow-{{ $sa->id }}" style="display:none;background:#fffbeb;">
                        <td colspan="4" style="padding:14px 20px;">
                            <form action="{{ route('superadmin.super-admins.update', $sa->id) }}" method="POST" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                                @csrf @method('PUT')
                                <div style="display:flex;flex-direction:column;gap:5px;min-width:160px;">
                                    <label style="font-size:12px;font-weight:600;color:#374151;">Name</label>
                                    <input type="text" name="name" value="{{ $sa->name }}" required style="padding:8px 12px;border:2px solid #d97706;border-radius:8px;font-size:13px;color:#111827;background:#fff;min-width:160px;">
                                </div>
                                <div style="display:flex;flex-direction:column;gap:5px;min-width:200px;">
                                    <label style="font-size:12px;font-weight:600;color:#374151;">Email</label>
                                    <input type="email" name="email" value="{{ $sa->email }}" required style="padding:8px 12px;border:2px solid #d97706;border-radius:8px;font-size:13px;color:#111827;background:#fff;min-width:200px;">
                                </div>
                                <div style="display:flex;flex-direction:column;gap:5px;min-width:160px;">
                                    <label style="font-size:12px;font-weight:600;color:#374151;">New Password <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
                                    <input type="password" name="password" placeholder="Leave blank to keep" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#fff;min-width:160px;">
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px;">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $sa->is_active ? 'checked' : '' }} style="width:15px;height:15px;accent-color:#d97706;">
                                    <label style="font-size:13px;font-weight:600;color:#374151;">Active</label>
                                </div>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <button type="submit" style="background:#d97706;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                                        <i class="fas fa-save" style="margin-right:4px;"></i>Save
                                    </button>
                                    <button type="button" onclick="toggleSaEdit({{ $sa->id }})" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:9px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function toggleSaEdit(id) {
    var row = document.getElementById('saeditrow-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endsection
