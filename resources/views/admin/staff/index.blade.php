@extends('layouts.admin')
@section('title', 'Staff')
@section('content')

<div class="d-flex justify-between align-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;color:var(--gray-800);">Staff</h2>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">
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
                <button id="btn-filter" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
                <button id="btn-reset" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users"></i> Staff</div>
        <span id="total-count" style="font-size:0.8rem;color:var(--gray-500);">{{ $staff->total() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Branch</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="table-body">
                @include('admin.staff._tbody')
            </tbody>
        </table>
    </div>
    <div id="pagination">
        @include('admin.staff._pagination')
    </div>
</div>

<style>
.page-btn { padding:0.3rem 0.65rem;border-radius:0.4rem;border:1px solid var(--gray-200);color:var(--gray-600);font-size:0.85rem;text-decoration:none;display:inline-block; }
.page-btn.active { background:var(--blue-600);border-color:var(--blue-600);color:#fff;font-weight:600; }
.page-btn.disabled { color:var(--gray-300);pointer-events:none; }
.page-btn:not(.active):not(.disabled):hover { background:var(--gray-50); }
</style>

<script>
const ajaxUrl = '{{ route('admin.staff.ajax') }}';
let currentPage = 1, debounceTimer;

function getParams(page) {
    const v = id => document.getElementById(id)?.value ?? '';
    const params = new URLSearchParams({ page });
    ['f-search','f-phone','f-role','f-status','f-per-page'].forEach(id => {
        const key = id.replace('f-','').replace('-','_');
        const val = v(id);
        if (val !== '' && val !== null) params.set(key === 'per_page' ? 'per_page' : key, val);
    });
    params.set('per_page', v('f-per-page') || '10');
    return params;
}

function load(page) {
    currentPage = page;
    fetch(ajaxUrl + '?' + getParams(page), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(data => {
            document.getElementById('table-body').innerHTML = data.tbody;
            document.getElementById('pagination').innerHTML = data.pagination;
            document.getElementById('total-count').textContent = data.total + ' total';
            bindPagination();
        })
        .catch(err => console.error('Filter error:', err));
}

function bindPagination() {
    document.querySelectorAll('#pagination .page-btn[data-page]').forEach(btn => {
        btn.addEventListener('click', e => { e.preventDefault(); load(btn.dataset.page); });
    });
}

document.getElementById('btn-filter').addEventListener('click', () => load(1));
document.getElementById('btn-reset').addEventListener('click', () => {
    document.getElementById('f-search').value = '';
    document.getElementById('f-phone').value = '';
    document.getElementById('f-role').value = '';
    document.getElementById('f-status').value = '';
    document.getElementById('f-per-page').value = '10';
    load(1);
});

['f-search','f-phone'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => load(1), 400);
    });
    document.getElementById(id).addEventListener('keydown', e => { if (e.key === 'Enter') { clearTimeout(debounceTimer); load(1); } });
});

['f-role','f-status','f-per-page'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => load(1));
});

bindPagination();
</script>
@endsection
