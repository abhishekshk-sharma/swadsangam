@extends('layouts.superadmin')
@section('title', 'Menu Categories')
@section('content')
@php
$th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;';
$td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;';
@endphp

<div style="margin-bottom:24px;">
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-tags" style="margin-right:8px;color:#d97706;"></i>Menu Categories</h1>
    <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Global menu category templates</p>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- Table --}}
    <div class="content-card">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;">
            <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-list" style="color:#d97706;margin-right:8px;"></i>All Categories</div>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="{{ $th }}">Name</th>
                        <th style="{{ $th }}">Menu Items</th>
                        <th style="{{ $th }}">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    {{-- View row --}}
                    <tr id="row-{{ $cat->id }}" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                        <td style="{{ $td }}font-weight:600;">{{ $cat->name }}</td>
                        <td style="{{ $td }}">
                            <span style="background:#f3f4f6;color:#374151;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">{{ $cat->menu_items_count }}</span>
                        </td>
                        <td style="{{ $td }}">
                            <div style="display:flex;gap:6px;">
                                <button onclick="toggleEdit({{ $cat->id }})"
                                        style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <form action="{{ route('superadmin.menu-categories.destroy', $cat->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button style="display:inline-flex;align-items:center;padding:5px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    {{-- Inline edit row --}}
                    <tr id="editrow-{{ $cat->id }}" style="display:none;background:#fffbeb;">
                        <td colspan="3" style="padding:12px 16px;">
                            <form action="{{ route('superadmin.menu-categories.update', $cat->id) }}" method="POST" style="display:flex;gap:10px;align-items:center;">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $cat->name }}" required
                                       style="flex:1;padding:8px 12px;border:2px solid #d97706;border-radius:8px;font-size:14px;color:#111827;background:#fff;">
                                <button type="submit" style="background:#d97706;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    <i class="fas fa-save" style="margin-right:4px;"></i>Save
                                </button>
                                <button type="button" onclick="toggleEdit({{ $cat->id }})"
                                        style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                                    Cancel
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="padding:40px;text-align:center;color:#9ca3af;">No categories yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add form --}}
    <div class="content-card">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;">
            <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-plus" style="color:#d97706;margin-right:8px;"></i>Add Category</div>
        </div>
        <div style="padding:20px;">
            <form action="{{ route('superadmin.menu-categories.store') }}" method="POST">
                @csrf
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Starters, Main Course, Desserts"
                           style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;color:#111827;background:#fff;box-sizing:border-box;">
                    @error('name')<div style="font-size:12px;color:#dc2626;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <button type="submit" style="width:100%;background:#d97706;color:#fff;border:none;padding:11px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-plus" style="margin-right:6px;"></i>Add Category
                </button>
            </form>
        </div>
    </div>

</div>

<script>
function toggleEdit(id) {
    var editRow = document.getElementById('editrow-' + id);
    editRow.style.display = editRow.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endsection
