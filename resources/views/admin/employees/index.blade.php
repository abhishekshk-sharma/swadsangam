@extends('layouts.admin')

@section('title', 'Employees')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-users me-2"></i>Employees</h1>
    <a href="{{ route('admin.employees.create') }}" class="btn-primary">
        <i class="fas fa-plus me-2"></i>Add Employee
    </a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                            <strong>{{ $employee->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->phone ?? '-' }}</td>
                    <td>
                        @php
                            $roleColors = [
                                'admin' => 'danger',
                                'manager' => 'warning',
                                'chef' => 'info',
                                'waiter' => 'primary',
                                'cashier' => 'success',
                                'staff' => 'secondary'
                            ];
                            $color = $roleColors[$employee->role] ?? 'secondary';
                        @endphp
                        <span class="badge-custom badge-{{ $color }}">{{ ucfirst($employee->role) }}</span>
                    </td>
                    <td>
                        @if($employee->is_active)
                            <span class="badge-custom badge-completed">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        @else
                            <span class="badge-custom badge-cancelled">
                                <i class="fas fa-times-circle me-1"></i>Inactive
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this employee?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No employees found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
}
</style>
@endsection
