@extends('layouts.superadmin')
@section('title', 'GST Slabs')
@section('content')
@php
$th='padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;';
$td='padding:13px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;';
$inp='padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#fff;';
@endphp

<div style="margin-bottom:24px;">
    <h1 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0;"><i class="fas fa-percent" style="margin-right:8px;color:#d97706;"></i>GST Slabs</h1>
    <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Configure GST rates and manage branch GST settings</p>
</div>

{{-- ── GST Slabs Table ── --}}
<div class="content-card" style="margin-bottom:24px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-percent" style="color:#d97706;margin-right:8px;"></i>GST Slabs</div>
        <button onclick="document.getElementById('createSlabForm').style.display=document.getElementById('createSlabForm').style.display==='none'?'block':'none'"
                style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="fas fa-plus"></i> Add Slab
        </button>
    </div>

    {{-- Create form --}}
    <div id="createSlabForm" style="display:none;padding:16px 20px;background:#fffbeb;border-bottom:1px solid #e5e7eb;">
        <form method="POST" action="{{ route('superadmin.gst-slabs.store') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            @csrf
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">Name</label>
                <input type="text" name="name" required placeholder="e.g. 5% GST" style="{{ $inp }}width:200px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">CGST %</label>
                <input type="number" name="cgst_rate" step="0.01" min="0" max="50" required placeholder="2.50" style="{{ $inp }}width:90px;">
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-size:12px;font-weight:600;color:#374151;">SGST %</label>
                <input type="number" name="sgst_rate" step="0.01" min="0" max="50" required placeholder="2.50" style="{{ $inp }}width:90px;">
            </div>
            <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px;">
                <input type="checkbox" name="is_active" value="1" checked id="createActive" style="width:15px;height:15px;accent-color:#d97706;">
                <label for="createActive" style="font-size:13px;color:#374151;font-weight:600;">Active</label>
            </div>
            <button type="submit" style="background:#059669;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Save</button>
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Name</th>
                    <th style="{{ $th }}">CGST</th>
                    <th style="{{ $th }}">SGST</th>
                    <th style="{{ $th }}">Total</th>
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slabs as $slab)
                <tr id="slabRow{{ $slab->id }}" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-weight:600;">{{ $slab->name }}</td>
                    <td style="{{ $td }}">{{ $slab->cgst_rate }}%</td>
                    <td style="{{ $td }}">{{ $slab->sgst_rate }}%</td>
                    <td style="{{ $td }}font-weight:700;">{{ $slab->total_rate }}%</td>
                    <td style="{{ $td }}">
                        <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $slab->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#f3f4f6;color:#6b7280;' }}">
                            {{ $slab->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="{{ $td }}">
                        <div style="display:flex;gap:6px;">
                            <button onclick="toggleEditSlab({{ $slab->id }})"
                                    style="display:inline-flex;align-items:center;gap:4px;padding:5px 10px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;"><i class="fas fa-pen"></i> Edit</button>
                            <form method="POST" action="{{ route('superadmin.gst-slabs.destroy', $slab) }}" style="margin:0;" onsubmit="return confirm('Delete this slab?')">
                                @csrf @method('DELETE')
                                <button style="display:inline-flex;align-items:center;padding:5px 10px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <tr id="editRow{{ $slab->id }}" style="display:none;background:#fffbeb;">
                    <td colspan="6" style="padding:14px 20px;">
                        <form method="POST" action="{{ route('superadmin.gst-slabs.update', $slab) }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                            @csrf @method('PUT')
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-size:12px;font-weight:600;color:#374151;">Name</label>
                                <input type="text" name="name" value="{{ $slab->name }}" required style="{{ $inp }}width:200px;">
                            </div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-size:12px;font-weight:600;color:#374151;">CGST %</label>
                                <input type="number" name="cgst_rate" step="0.01" value="{{ $slab->cgst_rate }}" required style="{{ $inp }}width:90px;">
                            </div>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-size:12px;font-weight:600;color:#374151;">SGST %</label>
                                <input type="number" name="sgst_rate" step="0.01" value="{{ $slab->sgst_rate }}" required style="{{ $inp }}width:90px;">
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;padding-bottom:2px;">
                                <input type="checkbox" name="is_active" value="1" {{ $slab->is_active ? 'checked' : '' }} style="width:15px;height:15px;accent-color:#d97706;">
                                <label style="font-size:13px;color:#374151;font-weight:600;">Active</label>
                            </div>
                            <button type="submit" style="background:#2563eb;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Update</button>
                            <button type="button" onclick="toggleEditSlab({{ $slab->id }})" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:40px;text-align:center;color:#9ca3af;">No GST slabs yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Branch GST Filter ── --}}
<div class="content-card" style="margin-bottom:24px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <div style="font-size:15px;font-weight:600;color:#111827;"><i class="fas fa-store" style="color:#d97706;margin-right:8px;"></i>Branch GST Settings</div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px;">Select a tenant to view and manage GST settings for its branches</div>
    </div>
    <div style="padding:16px 20px;border-bottom:1px solid #e5e7eb;">
        <form method="GET" action="{{ route('superadmin.gst-slabs.index') }}" id="filterForm" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:6px;min-width:200px;">
                <label style="font-size:12px;font-weight:600;color:#374151;"><i class="fas fa-building" style="margin-right:4px;"></i>Tenant</label>
                <select name="tenant_id" id="tenantSelect" onchange="this.form.submit()" style="{{ $inp }}min-width:200px;">
                    <option value="">— Select Tenant —</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ $selectedTenant == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($selectedTenant && $branches->count() > 0)
            <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                <label style="font-size:12px;font-weight:600;color:#374151;"><i class="fas fa-store" style="margin-right:4px;"></i>Branch</label>
                <select name="branch_id" onchange="this.form.submit()" style="{{ $inp }}min-width:180px;">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranch == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if($selectedTenant)
            <a href="{{ route('superadmin.gst-slabs.index') }}" style="padding:9px 16px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
            @endif
        </form>
    </div>

    @if($selectedTenant)
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="{{ $th }}">Branch</th>
                    <th style="{{ $th }}">GSTIN</th>
                    <th style="{{ $th }}">Current Slab</th>
                    <th style="{{ $th }}">Mode</th>
                    <th style="{{ $th }}">Update GST</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branchDetails as $branch)
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}">
                        <div style="font-weight:700;color:#111827;">{{ $branch->name }}</div>
                        @if($branch->address)
                            <div style="font-size:11px;color:#9ca3af;margin-top:2px;">{{ $branch->address }}</div>
                        @endif
                    </td>
                    <td style="{{ $td }}">
                        @if($branch->gst_number)
                            <span style="font-family:monospace;font-size:13px;background:#f3f4f6;padding:3px 8px;border-radius:4px;color:#374151;">{{ $branch->gst_number }}</span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="{{ $td }}">
                        @if($branch->gstSlab)
                            <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:#f0fdf4;color:#15803d;">{{ $branch->gstSlab->name }} ({{ $branch->gstSlab->total_rate }}%)</span>
                        @else
                            <span style="color:#9ca3af;">— No GST —</span>
                        @endif
                    </td>
                    <td style="{{ $td }}">
                        @if($branch->gst_mode)
                            <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $branch->gst_mode === 'excluded' ? 'background:#fef3c7;color:#92400e;' : 'background:#dbeafe;color:#1d4ed8;' }}">
                                {{ ucfirst($branch->gst_mode) }}
                            </span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="{{ $td }}">
                        <button onclick="toggleBranchEdit({{ $branch->id }})"
                                style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-pen"></i> Edit
                        </button>
                    </td>
                </tr>
                {{-- Inline branch edit row --}}
                <tr id="branchEditRow{{ $branch->id }}" style="display:none;background:#fffbeb;">
                    <td colspan="5" style="padding:14px 20px;">
                        <form method="POST" action="{{ route('superadmin.gst-slabs.assign-branch', $branch) }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                            @csrf @method('PATCH')
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                <label style="font-size:12px;font-weight:600;color:#374151;">GSTIN</label>
                                <input type="text" name="gst_number" value="{{ $branch->gst_number }}"
                                       placeholder="e.g. 27AAPFU0939F1ZV" maxlength="15"
                                       style="{{ $inp }}width:200px;text-transform:uppercase;">
                            </div>
                            <div style="display:flex;flex-direction:column;gap:6px;min-width:160px;">
                                <label style="font-size:12px;font-weight:600;color:#374151;">GST Slab</label>
                                <select name="gst_slab_id" id="branchSlabSel{{ $branch->id }}" onchange="toggleBranchMode({{ $branch->id }})" style="{{ $inp }}min-width:160px;">
                                    <option value="">— No GST —</option>
                                    @foreach($slabs->where('is_active', true) as $slab)
                                        <option value="{{ $slab->id }}" {{ $branch->gst_slab_id == $slab->id ? 'selected' : '' }}>
                                            {{ $slab->name }} ({{ $slab->total_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="branchModeWrap{{ $branch->id }}" style="display:{{ $branch->gst_slab_id ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:180px;">
                                <label style="font-size:12px;font-weight:600;color:#374151;">Mode</label>
                                <select name="gst_mode" style="{{ $inp }}min-width:180px;">
                                    <option value="included" {{ $branch->gst_mode === 'included' ? 'selected' : '' }}>Included (in price)</option>
                                    <option value="excluded" {{ $branch->gst_mode === 'excluded' ? 'selected' : '' }}>Excluded (add on top)</option>
                                </select>
                            </div>
                            <button type="submit" style="background:#d97706;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"><i class="fas fa-save" style="margin-right:4px;"></i>Save</button>
                            <button type="button" onclick="toggleBranchEdit({{ $branch->id }})" style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Cancel</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;">No branches found for this tenant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @else
    <div style="padding:40px;text-align:center;color:#9ca3af;">
        <i class="fas fa-store" style="font-size:36px;display:block;margin-bottom:12px;color:#e5e7eb;"></i>
        Select a tenant above to view branch GST settings.
    </div>
    @endif
</div>

<script>
function toggleEditSlab(id) {
    var row = document.getElementById('editRow' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
function toggleBranchEdit(id) {
    var row = document.getElementById('branchEditRow' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
function toggleBranchMode(id) {
    var val = document.getElementById('branchSlabSel' + id).value;
    document.getElementById('branchModeWrap' + id).style.display = val ? 'flex' : 'none';
}
</script>
@endsection
