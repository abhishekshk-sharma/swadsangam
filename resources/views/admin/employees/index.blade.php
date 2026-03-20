@extends('layouts.admin')

@section('title', 'Employees')

@section('content')

{{-- Header --}}
<div class="d-flex justify-between align-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;color:var(--gray-800);">Employees</h2>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Employee
    </a>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.employees.index') }}" id="filterForm">
    <input type="hidden" name="tab" id="activeTab" value="{{ $tab }}">
    <div class="content-card mb-4">
        <div class="card-body" style="padding:1rem;">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto auto;gap:0.75rem;align-items:flex-end;">
                <div>
                    <label class="form-label" style="font-size:0.75rem;">Search Name / Email / Role</label>
                    <input type="text" name="search" class="form-control" placeholder="Name…" value="{{ $search }}">
                </div>
                <div>
                    <label class="form-label" style="font-size:0.75rem;">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="Phone…" value="{{ $phone }}">
                </div>
                <div>
                    <label class="form-label" style="font-size:0.75rem;">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:0.75rem;">Per Page</label>
                    <select name="per_page" class="form-select">
                        @foreach([10,25,50,100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('admin.employees.index', ['tab' => $tab]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Tab Toggle --}}
<div style="display:flex;gap:0;border-bottom:2px solid var(--gray-200);margin-bottom:1.5rem;">
    @php
        $tabs = [
            'admins'   => ['icon'=>'fa-user-shield', 'label'=>'Admins',   'count'=>$admins->total()],
            'managers' => ['icon'=>'fa-user-tie',    'label'=>'Managers', 'count'=>$managers->total()],
            'staff'    => ['icon'=>'fa-users',       'label'=>'Staff',    'count'=>$employees->total()],
        ];
    @endphp
    @foreach($tabs as $key => $t)
    <button onclick="switchTab('{{ $key }}')" id="tab-{{ $key }}"
        style="padding:0.5rem 1.25rem;border:none;background:none;font-weight:600;font-size:0.9rem;cursor:pointer;margin-bottom:-2px;
               border-bottom:2px solid {{ $tab === $key ? 'var(--blue-600)' : 'transparent' }};
               color:{{ $tab === $key ? 'var(--blue-700)' : 'var(--gray-500)' }};">
        <i class="fas {{ $t['icon'] }} me-1"></i> {{ $t['label'] }}
        <span style="background:var(--gray-100);border:1px solid var(--gray-200);border-radius:20px;padding:0.1rem 0.5rem;font-size:0.7rem;margin-left:4px;">{{ $t['count'] }}</span>
    </button>
    @endforeach
</div>

{{-- ===== ADMINS PANE ===== --}}
<div id="pane-admins" style="{{ $tab !== 'admins' ? 'display:none' : '' }}">
    <div class="content-card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-shield"></i> Admins</div>
            <span style="font-size:0.8rem;color:var(--gray-500);">{{ $admins->total() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $i => $admin)
                    <tr>
                        <td style="color:var(--gray-400);font-size:0.8rem;">{{ $admins->firstItem() + $i }}</td>
                        <td><strong>{{ $admin->name }}</strong></td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->phone ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $admin->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $admin->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5"><div class="empty-state"><i class="fas fa-user-shield"></i><p>No admins found</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($admins->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-100);">
            @include('admin.employees._pagination', ['paginator' => $admins, 'pageName' => 'apage'])
        </div>
        @endif
    </div>
</div>

{{-- ===== MANAGERS PANE ===== --}}
<div id="pane-managers" style="{{ $tab !== 'managers' ? 'display:none' : '' }}">
    <div class="content-card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-tie"></i> Managers</div>
            <span style="font-size:0.8rem;color:var(--gray-500);">{{ $managers->total() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Assigned Staff</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $i => $manager)
                    <tr>
                        <td style="color:var(--gray-400);font-size:0.8rem;">{{ $managers->firstItem() + $i }}</td>
                        <td><strong>{{ $manager->name }}</strong></td>
                        <td>{{ $manager->email }}</td>
                        <td>{{ $manager->phone ?? '—' }}</td>
                        <td>{{ $manager->branch->name ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $manager->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $manager->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $assigned = $manager->branch_id ? ($assignedByBranch[$manager->branch_id] ?? collect()) : collect();
                            @endphp
                            @if($assigned->count())
                                @foreach($assigned as $emp)
                                    <span class="badge badge-info" style="margin:1px;">{{ $emp->name }} <span style="font-size:0.65rem;">({{ ucfirst($emp->role) }})</span></span>
                                @endforeach
                            @else
                                <span class="text-muted" style="font-size:0.8rem;">None</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.employees.edit', $manager->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.employees.destroy', $manager->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm" style="background:var(--error-light);border-color:var(--error);color:var(--error);"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-user-tie"></i><p>No managers found</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($managers->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-100);">
            @include('admin.employees._pagination', ['paginator' => $managers, 'pageName' => 'mpage'])
        </div>
        @endif
    </div>
</div>

{{-- ===== STAFF PANE ===== --}}
<div id="pane-staff" style="{{ $tab !== 'staff' ? 'display:none' : '' }}">
    <div class="content-card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-users"></i> Staff</div>
            <span style="font-size:0.8rem;color:var(--gray-500);">{{ $employees->total() }} total</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $i => $employee)
                    <tr>
                        <td style="color:var(--gray-400);font-size:0.8rem;">{{ $employees->firstItem() + $i }}</td>
                        <td><strong>{{ $employee->name }}</strong></td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->phone ?? '—' }}</td>
                        <td>
                            @php $roleColor = ['chef'=>'badge-info','waiter'=>'badge-neutral','cashier'=>'badge-success'][$employee->role] ?? 'badge-neutral'; @endphp
                            <span class="badge {{ $roleColor }}">{{ ucfirst($employee->role) }}</span>
                        </td>
                        <td>
                            @if($employee->branch_id)
                                @php $mgr = $allManagers->firstWhere('branch_id', $employee->branch_id); @endphp
                                {{ $mgr ? $mgr->name : '—' }}
                            @else
                                <span class="text-muted">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-error' }}">
                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm" style="background:var(--error-light);border-color:var(--error);color:var(--error);"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-users"></i><p>No staff found</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--gray-100);">
            @include('admin.employees._pagination', ['paginator' => $employees, 'pageName' => 'epage'])
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
var _activeTab = '{{ $tab }}';

function switchTab(key) {
    _activeTab = key;
    ['admins','managers','staff'].forEach(function(t) {
        document.getElementById('pane-' + t).style.display = t === key ? '' : 'none';
        var btn = document.getElementById('tab-' + t);
        btn.style.borderBottomColor = t === key ? 'var(--blue-600)' : 'transparent';
        btn.style.color             = t === key ? 'var(--blue-700)' : 'var(--gray-500)';
    });
    document.getElementById('activeTab').value = key;
    var url = new URL(window.location.href);
    url.searchParams.set('tab', key);
    history.replaceState(null, '', url.toString());
}

// Before form submits, always sync the hidden tab input from current active tab
document.getElementById('filterForm').addEventListener('submit', function() {
    document.getElementById('activeTab').value = _activeTab;
});
</script>
@endpush
@endsection
