@extends('layouts.admin')
@section('title', $branch->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">🏪 {{ $branch->name }}</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-primary"><i class="fas fa-edit me-2"></i>Edit</a>
        <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Staff',   $stats['employees'], 'fas fa-users',        '#3b82f6'],
        ['Tables',  $stats['tables'],    'fas fa-utensils',      '#8b5cf6'],
        ['Orders',  $stats['orders'],    'fas fa-shopping-cart', '#f59e0b'],
        ['Revenue', '₹'.number_format($stats['revenue'],2), 'fas fa-rupee-sign', '#059669'],
    ] as [$label, $val, $icon, $color])
    <div class="col-6 col-md-3">
        <div class="content-card p-3 text-center">
            <i class="{{ $icon }} mb-2" style="font-size:22px;color:{{ $color }};display:block;"></i>
            <div class="fw-bold" style="font-size:22px;">{{ $val }}</div>
            <div style="font-size:12px;color:#64748b;">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Branch Info --}}
<div class="content-card mb-4">
    <div class="card-body">
        <div class="row g-3">
            @if($branch->address)
            <div class="col-md-4">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Address</div>
                <div class="fw-semibold mt-1">{{ $branch->address }}</div>
            </div>
            @endif
            @if($branch->phone)
            <div class="col-md-4">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Phone</div>
                <div class="fw-semibold mt-1">{{ $branch->phone }}</div>
            </div>
            @endif
            <div class="col-md-4">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Status</div>
                <div class="mt-1">
                    <span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-warning' }}">
                        {{ $branch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Staff --}}
<div class="content-card">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-users"></i> Staff</span>
        <a href="{{ route('admin.employees.create') }}?branch_id={{ $branch->id }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>Add Staff
        </a>
    </div>
    @if($employees->isEmpty())
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h4>No staff assigned</h4>
            <p>No employees have been assigned to this branch yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    <tr>
                        <td class="fw-semibold">{{ $emp->name }}</td>
                        <td><span class="badge badge-info">{{ ucfirst($emp->role) }}</span></td>
                        <td>{{ $emp->email }}</td>
                        <td>
                            <span class="badge {{ $emp->is_active ? 'badge-success' : 'badge-warning' }}">
                                {{ $emp->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.employees.edit', $emp) }}" class="btn btn-outline btn-sm">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
