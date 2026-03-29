@extends('layouts.admin')
@section('title', 'Branches')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="section-title"><i class="fas fa-store me-2"></i>Branches</h1>
        <p style="font-size:13px;color:var(--gray-500);">{{ now()->format('l, F j, Y') }}</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="toggleBulkGst()"
                style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="fas fa-percent"></i> Bulk GST
        </button>
        <a href="{{ route('admin.branches.create') }}"
           style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
            <i class="fas fa-plus"></i> Add Branch
        </a>
    </div>
</div>

{{-- BULK GST PANEL --}}
<div id="bulkGstPanel" style="display:none;margin-bottom:24px;">
<div class="content-card">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f0fdf4;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:#15803d;"><i class="fas fa-percent" style="margin-right:8px;"></i>Bulk GST Management</div>
        <button onclick="toggleBulkGst()" style="background:none;border:none;font-size:18px;color:#6b7280;cursor:pointer;">&times;</button>
    </div>
    <div style="padding:20px;">
        <form action="{{ route('admin.branches.bulkGst') }}" method="POST" id="bulkGstForm">
            @csrf

            {{-- Branch selection --}}
            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <label style="font-size:13px;font-weight:600;color:#374151;">Select Branches</label>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:600;color:#2563eb;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" style="width:15px;height:15px;accent-color:#2563eb;">
                        Select All
                    </label>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px;">
                    @foreach($branches as $branch)
                    <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:2px solid #e5e7eb;border-radius:8px;cursor:pointer;transition:all .2s;"
                           onmouseover="this.style.borderColor='#2563eb'" onmouseout="if(!this.querySelector('input').checked)this.style.borderColor='#e5e7eb'"
                           id="branchLabel{{ $branch->id }}">
                        <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                               class="branch-checkbox" style="width:16px;height:16px;accent-color:#2563eb;flex-shrink:0;"
                               onchange="onBranchCheck(this, {{ $branch->id }})">
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:13px;color:#111827;">{{ $branch->name }}</div>
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                                @if($branch->gstSlab)
                                    <span style="color:#16a34a;"><i class="fas fa-check-circle" style="margin-right:3px;"></i>{{ $branch->gstSlab->name }} ({{ $branch->gstSlab->total_rate }}%) · {{ ucfirst($branch->gst_mode) }}</span>
                                @else
                                    <span style="color:#9ca3af;"><i class="fas fa-times-circle" style="margin-right:3px;"></i>No GST</span>
                                @endif
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
                <div id="noSelectionMsg" style="display:none;font-size:12px;color:#dc2626;margin-top:8px;"><i class="fas fa-exclamation-circle" style="margin-right:4px;"></i>Please select at least one branch.</div>
            </div>

            {{-- Action tabs --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:10px;">Action</label>
                <div style="display:flex;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 18px;border:2px solid #d1d5db;border-radius:8px;cursor:pointer;transition:all .2s;" id="actionApplyLabel">
                        <input type="radio" name="action" value="apply" id="actionApply" checked onchange="toggleAction()" style="accent-color:#16a34a;">
                        <span style="font-size:13px;font-weight:600;color:#374151;"><i class="fas fa-plus-circle" style="margin-right:6px;color:#16a34a;"></i>Apply GST</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;padding:10px 18px;border:2px solid #d1d5db;border-radius:8px;cursor:pointer;transition:all .2s;" id="actionRemoveLabel">
                        <input type="radio" name="action" value="remove" id="actionRemove" onchange="toggleAction()" style="accent-color:#dc2626;">
                        <span style="font-size:13px;font-weight:600;color:#374151;"><i class="fas fa-minus-circle" style="margin-right:6px;color:#dc2626;"></i>Remove GST</span>
                    </label>
                </div>
            </div>

            {{-- GST settings (shown only for apply) --}}
            <div id="gstSettings" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin-bottom:20px;">
                <div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;">
                    <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
                        <label style="font-size:12px;font-weight:600;color:#374151;">GST Slab *</label>
                        <select name="gst_slab_id" id="gstSlabSelect" onchange="toggleGstMode()" style="padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                            <option value="">— Select Slab —</option>
                            @foreach($gstSlabs as $slab)
                                <option value="{{ $slab->id }}">{{ $slab->name }} ({{ $slab->total_rate }}%)</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="gstModeWrap" style="display:none;flex-direction:column;gap:6px;min-width:200px;">
                        <label style="font-size:12px;font-weight:600;color:#374151;">GST Mode *</label>
                        <select name="gst_mode" style="padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;">
                            <option value="included">Included — GST already in item prices</option>
                            <option value="excluded">Excluded — Add GST on top of bill</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;min-width:200px;">
                        <label style="font-size:12px;font-weight:600;color:#374151;">GSTIN <span style="font-weight:400;color:#9ca3af;">(optional — applies to all selected)</span></label>
                        <input type="text" name="gst_number" placeholder="e.g. 27AAPFU0939F1ZV" maxlength="15"
                               style="padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;text-transform:uppercase;">
                    </div>
                </div>
                <div style="font-size:12px;color:#6b7280;margin-top:10px;"><i class="fas fa-info-circle" style="margin-right:4px;"></i>If GSTIN is left blank, each branch keeps its existing GSTIN.</div>
            </div>

            {{-- Remove warning --}}
            <div id="removeWarning" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:14px 16px;margin-bottom:20px;">
                <div style="font-size:13px;color:#dc2626;font-weight:600;"><i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>This will remove GST slab, mode and GSTIN from all selected branches.</div>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" onclick="return validateBulk()" id="bulkSubmitBtn"
                        style="background:#16a34a;color:#fff;border:none;padding:11px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-check" style="margin-right:6px;"></i>Apply to Selected Branches
                </button>
                <button type="button" onclick="toggleBulkGst()"
                        style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:11px 20px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</div>

{{-- BRANCHES GRID --}}
@if($branches->isEmpty())
<div class="content-card" style="text-align:center;padding:60px;">
    <div style="font-size:48px;color:#d1d5db;margin-bottom:16px;"><i class="fas fa-store"></i></div>
    <p style="color:#6b7280;font-size:15px;">No branches yet.</p>
    <a href="{{ route('admin.branches.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;">
        <i class="fas fa-plus"></i> Create First Branch
    </a>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
    @foreach($branches as $branch)
    <div class="content-card" style="padding:0;border-top:4px solid {{ $branch->is_active ? '#16a34a' : '#9ca3af' }};">
        <div style="padding:18px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <div>
                    <div style="font-weight:700;font-size:16px;color:#111827;">{{ $branch->name }}</div>
                    @if($branch->address)
                        <div style="font-size:12px;color:#6b7280;margin-top:2px;"><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>{{ $branch->address }}</div>
                    @endif
                    @if($branch->phone)
                        <div style="font-size:12px;color:#6b7280;margin-top:2px;"><i class="fas fa-phone" style="margin-right:4px;"></i>{{ $branch->phone }}</div>
                    @endif
                </div>
                <span style="padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $branch->is_active ? 'background:#dcfce7;color:#15803d;' : 'background:#f3f4f6;color:#6b7280;' }}">
                    {{ $branch->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            {{-- Stats --}}
            <div style="display:flex;gap:16px;margin-bottom:14px;padding:10px;background:#f9fafb;border-radius:8px;">
                <div style="text-align:center;flex:1;">
                    <div style="font-weight:700;font-size:18px;color:#111827;">{{ $branch->employees_count }}</div>
                    <div style="font-size:11px;color:#6b7280;">Staff</div>
                </div>
                <div style="text-align:center;flex:1;">
                    <div style="font-weight:700;font-size:18px;color:#111827;">{{ $branch->tables_count }}</div>
                    <div style="font-size:11px;color:#6b7280;">Tables</div>
                </div>
                <div style="text-align:center;flex:1;">
                    <div style="font-weight:700;font-size:18px;color:#111827;">{{ $branch->orders_count }}</div>
                    <div style="font-size:11px;color:#6b7280;">Orders</div>
                </div>
            </div>

            {{-- GST badge --}}
            <div style="margin-bottom:14px;">
                @if($branch->gstSlab)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:600;">
                        <i class="fas fa-percent"></i> {{ $branch->gstSlab->name }} ({{ $branch->gstSlab->total_rate }}%) · {{ ucfirst($branch->gst_mode) }}
                        @if($branch->gst_number)
                            · <span style="font-family:monospace;">{{ $branch->gst_number }}</span>
                        @endif
                    </span>
                @else
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#f3f4f6;color:#9ca3af;padding:4px 10px;border-radius:6px;font-size:12px;">
                        <i class="fas fa-times-circle"></i> No GST
                    </span>
                @endif
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:8px;">
                <a href="{{ route('admin.branches.show', $branch) }}"
                   style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:8px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="{{ route('admin.branches.edit', $branch) }}"
                   style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:8px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                    <i class="fas fa-pen"></i> Edit
                </a>
                <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" style="margin:0;" onsubmit="return confirm('Delete {{ $branch->name }}?')">
                    @csrf @method('DELETE')
                    <button style="display:inline-flex;align-items:center;padding:8px 12px;background:#fee2e2;color:#dc2626;border:none;border-radius:8px;font-size:13px;cursor:pointer;">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<script>
function toggleBulkGst() {
    var panel = document.getElementById('bulkGstPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    if (panel.style.display === 'block') panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleSelectAll(cb) {
    document.querySelectorAll('.branch-checkbox').forEach(function(c) {
        c.checked = cb.checked;
        updateBranchLabelStyle(c);
    });
}

function onBranchCheck(cb, id) {
    updateBranchLabelStyle(cb);
    var all = document.querySelectorAll('.branch-checkbox');
    var checked = document.querySelectorAll('.branch-checkbox:checked');
    document.getElementById('selectAll').checked = all.length === checked.length;
    document.getElementById('selectAll').indeterminate = checked.length > 0 && checked.length < all.length;
}

function updateBranchLabelStyle(cb) {
    var label = document.getElementById('branchLabel' + cb.value);
    if (label) label.style.borderColor = cb.checked ? '#2563eb' : '#e5e7eb';
}

function toggleAction() {
    var isApply = document.getElementById('actionApply').checked;
    document.getElementById('gstSettings').style.display   = isApply ? 'block' : 'none';
    document.getElementById('removeWarning').style.display = isApply ? 'none'  : 'block';
    var btn = document.getElementById('bulkSubmitBtn');
    btn.style.background = isApply ? '#16a34a' : '#dc2626';
    btn.innerHTML = isApply
        ? '<i class="fas fa-check" style="margin-right:6px;"></i>Apply GST to Selected'
        : '<i class="fas fa-times" style="margin-right:6px;"></i>Remove GST from Selected';
    document.getElementById('actionApplyLabel').style.borderColor  = isApply ? '#16a34a' : '#d1d5db';
    document.getElementById('actionRemoveLabel').style.borderColor = isApply ? '#d1d5db' : '#dc2626';
}

function toggleGstMode() {
    var val = document.getElementById('gstSlabSelect').value;
    document.getElementById('gstModeWrap').style.display = val ? 'flex' : 'none';
}

function validateBulk() {
    var checked = document.querySelectorAll('.branch-checkbox:checked');
    if (checked.length === 0) {
        document.getElementById('noSelectionMsg').style.display = 'block';
        return false;
    }
    document.getElementById('noSelectionMsg').style.display = 'none';
    var isApply = document.getElementById('actionApply').checked;
    if (isApply && !document.getElementById('gstSlabSelect').value) {
        alert('Please select a GST slab.');
        return false;
    }
    var count = checked.length;
    var action = isApply ? 'apply GST to' : 'remove GST from';
    return confirm('Are you sure you want to ' + action + ' ' + count + ' branch(es)?');
}

// Init styles
document.addEventListener('DOMContentLoaded', function() {
    toggleAction();
});
</script>
@endsection
