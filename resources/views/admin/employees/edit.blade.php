@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-user-edit me-2"></i>Edit Employee</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone (with country code)</label>
                        <input type="text" name="phone" class="form-control" placeholder="+919876543210" value="{{ old('phone', $employee->phone) }}">
                        <small class="text-muted">Format: +91XXXXXXXXXX</small>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control">
                        <small class="text-muted">Leave blank to keep current password</small>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="staff" {{ old('role', $employee->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="waiter" {{ old('role', $employee->role) === 'waiter' ? 'selected' : '' }}>Waiter</option>
                            <option value="chef" {{ old('role', $employee->role) === 'chef' ? 'selected' : '' }}>Chef</option>
                            <option value="cashier" {{ old('role', $employee->role) === 'cashier' ? 'selected' : '' }}>Cashier</option>
                            <option value="manager" {{ old('role', $employee->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="admin" {{ old('role', $employee->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Employee
                            </label>
                        </div>
                        <small class="text-muted">Inactive employees cannot login</small>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save me-2"></i>Update Employee
                        </button>
                        <a href="{{ route('admin.employees.index') }}" class="btn-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
