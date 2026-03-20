@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('content')
<div class="d-flex justify-between align-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;color:var(--gray-800);">
        Edit — {{ $employee->name }}
        <span class="badge {{ $employee->role === 'manager' ? 'badge-warning' : 'badge-info' }}" style="font-size:0.7rem;vertical-align:middle;">
            {{ ucfirst($employee->role) }}
        </span>
    </h2>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

{{-- Tab Toggle --}}
<div style="display:flex;gap:0;border-bottom:2px solid var(--gray-200);margin-bottom:1.5rem;">
    <button onclick="showTab('info')" id="tab-info"
        style="padding:0.5rem 1.25rem;border:none;background:none;font-weight:600;font-size:0.9rem;cursor:pointer;border-bottom:2px solid var(--blue-600);margin-bottom:-2px;color:var(--blue-700);">
        <i class="fas fa-user me-1"></i> Employee Info
    </button>
    @if($employee->role === 'manager')
    <button onclick="showTab('assign')" id="tab-assign"
        style="padding:0.5rem 1.25rem;border:none;background:none;font-weight:600;font-size:0.9rem;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--gray-500);">
        <i class="fas fa-users me-1"></i> Assign Employees
        <span style="background:var(--blue-100);color:var(--blue-700);border-radius:20px;padding:0.1rem 0.5rem;font-size:0.7rem;margin-left:4px;">
            {{ $assignedEmployees->count() }}
        </span>
    </button>
    @endif
</div>

<form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" id="editForm">
    @csrf
    @method('PUT')

    {{-- ===== TAB: Employee Info ===== --}}
    <div id="pane-info">
        <div class="content-card">
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

                    <div class="form-group">
                        <label class="form-label">Name <span style="color:var(--error)">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                        @error('name')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span style="color:var(--error)">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required>
                        @error('email')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}" placeholder="9876543210">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role <span style="color:var(--error)">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="manager"  {{ old('role', $employee->role) === 'manager'  ? 'selected' : '' }}>Manager</option>
                            <option value="waiter"   {{ old('role', $employee->role) === 'waiter'   ? 'selected' : '' }}>Waiter</option>
                            <option value="chef"     {{ old('role', $employee->role) === 'chef'     ? 'selected' : '' }}>Chef</option>
                            <option value="cashier"  {{ old('role', $employee->role) === 'cashier'  ? 'selected' : '' }}>Cashier</option>
                        </select>
                        @error('role')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">— No Branch —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $employee->branch_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:0.25rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;color:var(--gray-700);">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}
                                style="width:16px;height:16px;accent-color:var(--blue-600);">
                            Active Employee
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div style="display:flex;gap:0.5rem;">
                            <input type="password" name="password" id="pw" class="form-control" placeholder="Leave blank to keep current">
                            <button type="button" onclick="togglePw('pw',this)" class="btn btn-secondary btn-sm" style="white-space:nowrap;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div style="display:flex;gap:0.5rem;">
                            <input type="password" name="password_confirmation" id="pw2" class="form-control">
                            <button type="button" onclick="togglePw('pw2',this)" class="btn btn-secondary btn-sm" style="white-space:nowrap;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <div style="margin-top:1.5rem;display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TAB: Assign Employees (managers only) ===== --}}
    @if($employee->role === 'manager')
    <div id="pane-assign" style="display:none;">

        @if(!$employee->branch_id)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            This manager has no branch assigned. Assign a branch first (in Employee Info tab) before assigning staff.
        </div>
        @else

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

            {{-- LEFT: Assigned --}}
            <div class="content-card" style="margin-bottom:0;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-check-circle" style="color:var(--success)"></i> Assigned to {{ $employee->name }}</div>
                    <span style="font-size:0.75rem;color:var(--gray-500);">Click to unassign</span>
                </div>
                <div style="padding:1rem;min-height:200px;max-height:420px;overflow-y:auto;" id="assignedList">
                    @forelse($assignedEmployees as $emp)
                    <div class="emp-item assigned-item" data-id="{{ $emp->id }}" data-name="{{ $emp->name }}" data-role="{{ ucfirst($emp->role) }}"
                        onclick="moveToUnassigned(this)"
                        style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid var(--success);background:var(--success-light);cursor:pointer;transition:all 0.15s;">
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;color:var(--gray-800);">{{ $emp->name }}</div>
                            <div style="font-size:0.75rem;color:var(--gray-500);">{{ ucfirst($emp->role) }}</div>
                        </div>
                        <i class="fas fa-times-circle" style="color:var(--error);font-size:1rem;"></i>
                        <input type="hidden" name="assigned_ids[]" value="{{ $emp->id }}" class="assigned-input">
                    </div>
                    @empty
                    <div id="assignedEmpty" style="text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;">
                        <i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        No employees assigned
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: Unassigned --}}
            <div class="content-card" style="margin-bottom:0;">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-user-plus" style="color:var(--blue-500)"></i> Unassigned Staff</div>
                    <span style="font-size:0.75rem;color:var(--gray-500);">Click to assign</span>
                </div>
                <div style="padding:1rem;min-height:200px;max-height:420px;overflow-y:auto;" id="unassignedList">
                    @forelse($unassignedEmployees as $emp)
                    <div class="emp-item unassigned-item" data-id="{{ $emp->id }}" data-name="{{ $emp->name }}" data-role="{{ ucfirst($emp->role) }}"
                        onclick="moveToAssigned(this)"
                        style="display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid var(--gray-200);background:var(--white);cursor:pointer;transition:all 0.15s;">
                        <div>
                            <div style="font-weight:600;font-size:0.9rem;color:var(--gray-800);">{{ $emp->name }}</div>
                            <div style="font-size:0.75rem;color:var(--gray-500);">{{ ucfirst($emp->role) }}</div>
                        </div>
                        <i class="fas fa-plus-circle" style="color:var(--success);font-size:1rem;"></i>
                    </div>
                    @empty
                    <div id="unassignedEmpty" style="text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;">
                        <i class="fas fa-check-double" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>
                        All staff assigned
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <div style="margin-top:1.25rem;display:flex;gap:0.75rem;align-items:center;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Assignments</button>
            <span style="font-size:0.85rem;color:var(--gray-500);">Changes apply when you click Save.</span>
        </div>

        @endif
    </div>
    @endif

</form>

@push('scripts')
<script>
function showTab(tab) {
    document.getElementById('pane-info').style.display   = tab === 'info'   ? '' : 'none';
    @if($employee->role === 'manager')
    document.getElementById('pane-assign').style.display = tab === 'assign' ? '' : 'none';
    @endif

    document.getElementById('tab-info').style.borderBottomColor  = tab === 'info'   ? 'var(--blue-600)' : 'transparent';
    document.getElementById('tab-info').style.color               = tab === 'info'   ? 'var(--blue-700)' : 'var(--gray-500)';
    @if($employee->role === 'manager')
    document.getElementById('tab-assign').style.borderBottomColor = tab === 'assign' ? 'var(--blue-600)' : 'transparent';
    document.getElementById('tab-assign').style.color              = tab === 'assign' ? 'var(--blue-700)' : 'var(--gray-500)';
    @endif
}

function moveToAssigned(el) {
    const id   = el.dataset.id;
    const name = el.dataset.name;
    const role = el.dataset.role;

    // Build assigned card
    const card = document.createElement('div');
    card.className = 'emp-item assigned-item';
    card.dataset.id   = id;
    card.dataset.name = name;
    card.dataset.role = role;
    card.onclick = function() { moveToUnassigned(this); };
    card.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid var(--success);background:var(--success-light);cursor:pointer;transition:all 0.15s;';
    card.innerHTML = `<div><div style="font-weight:600;font-size:0.9rem;color:var(--gray-800);">${name}</div><div style="font-size:0.75rem;color:var(--gray-500);">${role}</div></div>
        <i class="fas fa-times-circle" style="color:var(--error);font-size:1rem;"></i>
        <input type="hidden" name="assigned_ids[]" value="${id}" class="assigned-input">`;

    document.getElementById('assignedList').appendChild(card);
    el.remove();
    syncEmpty();
}

function moveToUnassigned(el) {
    const id   = el.dataset.id;
    const name = el.dataset.name;
    const role = el.dataset.role;

    const card = document.createElement('div');
    card.className = 'emp-item unassigned-item';
    card.dataset.id   = id;
    card.dataset.name = name;
    card.dataset.role = role;
    card.onclick = function() { moveToAssigned(this); };
    card.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:0.6rem 0.75rem;margin-bottom:0.5rem;border-radius:0.5rem;border:1px solid var(--gray-200);background:var(--white);cursor:pointer;transition:all 0.15s;';
    card.innerHTML = `<div><div style="font-weight:600;font-size:0.9rem;color:var(--gray-800);">${name}</div><div style="font-size:0.75rem;color:var(--gray-500);">${role}</div></div>
        <i class="fas fa-plus-circle" style="color:var(--success);font-size:1rem;"></i>`;

    document.getElementById('unassignedList').appendChild(card);
    el.remove();
    syncEmpty();
}

function syncEmpty() {
    const aList = document.getElementById('assignedList');
    const uList = document.getElementById('unassignedList');

    const hasAssigned   = aList.querySelectorAll('.assigned-item').length > 0;
    const hasUnassigned = uList.querySelectorAll('.unassigned-item').length > 0;

    let ae = document.getElementById('assignedEmpty');
    if (!hasAssigned && !ae) {
        ae = document.createElement('div');
        ae.id = 'assignedEmpty';
        ae.style.cssText = 'text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;';
        ae.innerHTML = '<i class="fas fa-inbox" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>No employees assigned';
        aList.appendChild(ae);
    } else if (hasAssigned && ae) ae.remove();

    let ue = document.getElementById('unassignedEmpty');
    if (!hasUnassigned && !ue) {
        ue = document.createElement('div');
        ue.id = 'unassignedEmpty';
        ue.style.cssText = 'text-align:center;padding:2rem;color:var(--gray-400);font-size:0.9rem;';
        ue.innerHTML = '<i class="fas fa-check-double" style="font-size:2rem;display:block;margin-bottom:0.5rem;"></i>All staff assigned';
        uList.appendChild(ue);
    } else if (hasUnassigned && ue) ue.remove();
}

function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const ico = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; ico.classList.replace('fa-eye','fa-eye-slash'); }
    else { inp.type = 'password'; ico.classList.replace('fa-eye-slash','fa-eye'); }
}

// Open assign tab if redirected back with error on assign pane
@if(session('_tab') === 'assign')
showTab('assign');
@endif
</script>
@endpush
@endsection
