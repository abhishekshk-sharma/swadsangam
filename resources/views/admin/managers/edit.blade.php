@extends('layouts.admin')
@section('title', 'Edit Manager')
@section('content')

<div class="d-flex justify-between align-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;color:var(--gray-800);">
        Edit — {{ $manager->name }}
    </h2>
    <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>

{{-- Tabs --}}
<div style="display:flex;border-bottom:2px solid var(--gray-200);margin-bottom:1.5rem;">
    <button onclick="showTab('info')" id="tab-info"
        style="padding:0.5rem 1.25rem;border:none;background:none;font-weight:600;font-size:0.9rem;cursor:pointer;margin-bottom:-2px;border-bottom:2px solid var(--blue-600);color:var(--blue-700);">
        <i class="fas fa-user me-1"></i> Info
    </button>
    <button onclick="showTab('assign')" id="tab-assign"
        style="padding:0.5rem 1.25rem;border:none;background:none;font-weight:600;font-size:0.9rem;cursor:pointer;margin-bottom:-2px;border-bottom:2px solid transparent;color:var(--gray-500);">
        <i class="fas fa-users me-1"></i> Assign Staff
        <span style="background:var(--blue-100);color:var(--blue-700);border-radius:20px;padding:0.1rem 0.5rem;font-size:0.7rem;margin-left:4px;">{{ $assignedEmployees->count() }}</span>
    </button>
</div>

<form action="{{ route('admin.managers.update', $manager->id) }}" method="POST" id="editForm">
    @csrf @method('PUT')

    {{-- Info Tab --}}
    <div id="pane-info">
        <div class="content-card" style="max-width:700px;">
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Name <span style="color:var(--error)">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $manager->name) }}" required>
                        @error('name')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span style="color:var(--error)">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $manager->email) }}" required>
                        @error('email')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $manager->phone) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">— No Branch —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $manager->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div style="display:flex;gap:0.5rem;">
                            <input type="password" name="password" id="pw" class="form-control" placeholder="Leave blank to keep">
                            <button type="button" onclick="togglePw('pw',this)" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                        </div>
                        @error('password')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div style="display:flex;gap:0.5rem;">
                            <input type="password" name="password_confirmation" id="pw2" class="form-control">
                            <button type="button" onclick="togglePw('pw2',this)" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:0.25rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $manager->is_active) ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--blue-600);"> Active
                        </label>
                    </div>
                </div>
                <div style="margin-top:1.5rem;display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                    <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Tab --}}
    <div id="pane-assign" style="display:none;">
        @if(!$manager->branch_id)
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Assign a branch first (in Info tab) before assigning staff.</div>
        @else
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
            <div class="content-card" style="margin-bottom:0;">
                <div class="card-header">
                    <div class="card-title" style="color:var(--success);"><i class="fas fa-check-circle"></i> Assigned to {{ $manager->name }}</div>
                    <span style="font-size:0.75rem;color:var(--gray-500);">Click to remove</span>
                </div>
                <div id="assignedList" style="padding:1rem;min-height:200px;max-height:420px;overflow-y:auto;">
                    @forelse($assignedEmployees as $emp)
                    <div class="emp-card" data-id="{{ $emp->id }}" data-name="{{ $emp->name }}" data-role="{{ ucfirst($emp->role) }}" onclick="moveOut(this)"
                        style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid var(--success);background:var(--success-light);cursor:pointer;">
                        <div><div style="font-weight:600;font-size:0.9rem;">{{ $emp->name }}</div><div style="font-size:0.75rem;color:var(--gray-500);">{{ ucfirst($emp->role) }}</div></div>
                        <i class="fas fa-times-circle" style="color:var(--error);"></i>
                        <input type="hidden" name="assigned_ids[]" value="{{ $emp->id }}">
                    </div>
                    @empty
                    <div id="emptyAssigned" style="text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>No staff assigned
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="content-card" style="margin-bottom:0;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-user-plus" style="color:var(--blue-500);"></i> Unassigned Staff</div>
                    <span style="font-size:0.75rem;color:var(--gray-500);">Click to assign</span>
                </div>
                <div id="unassignedList" style="padding:1rem;min-height:200px;max-height:420px;overflow-y:auto;">
                    @forelse($unassignedEmployees as $emp)
                    <div class="emp-card" data-id="{{ $emp->id }}" data-name="{{ $emp->name }}" data-role="{{ ucfirst($emp->role) }}" onclick="moveIn(this)"
                        style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid var(--gray-200);background:var(--white);cursor:pointer;">
                        <div><div style="font-weight:600;font-size:0.9rem;">{{ $emp->name }}</div><div style="font-size:0.75rem;color:var(--gray-500);">{{ ucfirst($emp->role) }}</div></div>
                        <i class="fas fa-plus-circle" style="color:var(--success);"></i>
                    </div>
                    @empty
                    <div id="emptyUnassigned" style="text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;">
                        <i class="fas fa-check-double" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>All staff assigned
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div style="margin-top:1.25rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Assignments</button>
        </div>
        @endif
    </div>
</form>

<script>
function showTab(t) {
    document.getElementById('pane-info').style.display   = t === 'info'   ? '' : 'none';
    document.getElementById('pane-assign').style.display = t === 'assign' ? '' : 'none';
    document.getElementById('tab-info').style.cssText   += ';border-bottom-color:' + (t==='info'   ? 'var(--blue-600)' : 'transparent') + ';color:' + (t==='info'   ? 'var(--blue-700)' : 'var(--gray-500)');
    document.getElementById('tab-assign').style.cssText += ';border-bottom-color:' + (t==='assign' ? 'var(--blue-600)' : 'transparent') + ';color:' + (t==='assign' ? 'var(--blue-700)' : 'var(--gray-500)');
}
function makeCard(d, assigned) {
    var c = document.createElement('div');
    c.className = 'emp-card'; c.dataset.id = d.id; c.dataset.name = d.name; c.dataset.role = d.role;
    c.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;cursor:pointer;' +
        (assigned ? 'border:1px solid var(--success);background:var(--success-light);' : 'border:1px solid var(--gray-200);background:var(--white);');
    c.innerHTML = '<div><div style="font-weight:600;font-size:0.9rem;">'+d.name+'</div><div style="font-size:0.75rem;color:var(--gray-500);">'+d.role+'</div></div>' +
        (assigned ? '<i class="fas fa-times-circle" style="color:var(--error);"></i><input type="hidden" name="assigned_ids[]" value="'+d.id+'">'
                  : '<i class="fas fa-plus-circle" style="color:var(--success);"></i>');
    c.onclick = assigned ? function(){ moveOut(this); } : function(){ moveIn(this); };
    return c;
}
function moveIn(el) {
    document.getElementById('assignedList').appendChild(makeCard(el.dataset, true));
    el.remove(); syncEmpty();
}
function moveOut(el) {
    document.getElementById('unassignedList').appendChild(makeCard(el.dataset, false));
    el.remove(); syncEmpty();
}
function syncEmpty() {
    var al = document.getElementById('assignedList'), ul = document.getElementById('unassignedList');
    var ae = document.getElementById('emptyAssigned'), ue = document.getElementById('emptyUnassigned');
    var hasA = al.querySelectorAll('.emp-card').length > 0;
    var hasU = ul.querySelectorAll('.emp-card').length > 0;
    if (!hasA && !ae) { var d=document.createElement('div'); d.id='emptyAssigned'; d.style.cssText='text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;'; d.innerHTML='<i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>No staff assigned'; al.appendChild(d); }
    else if (hasA && ae) ae.remove();
    if (!hasU && !ue) { var d=document.createElement('div'); d.id='emptyUnassigned'; d.style.cssText='text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;'; d.innerHTML='<i class="fas fa-check-double" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>All staff assigned'; ul.appendChild(d); }
    else if (hasU && ue) ue.remove();
}
function togglePw(id, btn) {
    var inp = document.getElementById(id), ico = btn.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.classList.toggle('fa-eye'); ico.classList.toggle('fa-eye-slash');
}
</script>
@endsection
