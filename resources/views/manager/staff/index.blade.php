@extends('layouts.manager')
@section('title', 'Staff')
@section('content')

<div class="d-flex justify-between align-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;color:var(--gray-800);">Branch Staff</h2>
    <a href="{{ route('manager.staff.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> Add Staff
    </a>
</div>

<div class="content-card mb-4">
    <div class="card-body" style="padding:1rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto auto;gap:0.75rem;align-items:flex-end;">
            <div>
                <label class="form-label" style="font-size:0.75rem;">Name / Email</label>
                <input type="text" id="f-search" class="form-control" placeholder="Search…" value="{{ $search }}">
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Phone</label>
                <input type="text" id="f-phone" class="form-control" placeholder="Phone…" value="{{ $phone }}">
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Role</label>
                <select id="f-role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="waiter"  {{ $role === 'waiter'  ? 'selected' : '' }}>Waiter</option>
                    <option value="chef"    {{ $role === 'chef'    ? 'selected' : '' }}>Chef</option>
                    <option value="cashier" {{ $role === 'cashier' ? 'selected' : '' }}>Cashier</option>
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Status</label>
                <select id="f-status" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div>
                <label class="form-label" style="font-size:0.75rem;">Per Page</label>
                <select id="f-per-page" class="form-select">
                    @foreach(['10','25','50','100'] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button id="btn-filter" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                <button id="btn-reset" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users"></i> Staff</div>
        <span style="font-size:0.8rem;color:var(--gray-500);">{{ $staff->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($staff as $i => $emp)
                <tr>
                    <td style="color:var(--gray-400);font-size:0.8rem;">{{ $staff->firstItem() + $i }}</td>
                    <td><strong>{{ $emp->name }}</strong></td>
                    <td>{{ $emp->email }}</td>
                    <td>{{ $emp->phone ?? '—' }}</td>
                    <td>
                        @php $rc = ['chef'=>'badge-info','waiter'=>'badge-neutral','cashier'=>'badge-success'][$emp->role] ?? 'badge-neutral'; @endphp
                        <span class="badge {{ $rc }}">{{ ucfirst($emp->role) }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $emp->is_active ? 'badge-success' : 'badge-error' }}">
                            {{ $emp->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('manager.staff.edit', $emp->id) }}" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><i class="fas fa-users"></i><p>No staff found</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.5rem;">{{ $staff->links() }}</div>
</div>

<script>
let debounceTimer;
function reload() {
    const params = new URLSearchParams({
        search:   document.getElementById('f-search').value,
        phone:    document.getElementById('f-phone').value,
        role:     document.getElementById('f-role').value,
        status:   document.getElementById('f-status').value,
        per_page: document.getElementById('f-per-page').value,
    });
    window.location.href = '{{ route('manager.staff.index') }}?' + params.toString();
}
document.getElementById('btn-filter').addEventListener('click', reload);
document.getElementById('btn-reset').addEventListener('click', () => {
    ['f-search','f-phone'].forEach(id => document.getElementById(id).value = '');
    ['f-role','f-status'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('f-per-page').value = '10';
    reload();
});
['f-search','f-phone'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(reload, 400); });
    document.getElementById(id).addEventListener('keydown', e => { if (e.key === 'Enter') { clearTimeout(debounceTimer); reload(); } });
});
['f-role','f-status','f-per-page'].forEach(id => document.getElementById(id).addEventListener('change', reload));
</script>
@endsection
