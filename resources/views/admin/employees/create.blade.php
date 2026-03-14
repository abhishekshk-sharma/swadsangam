@extends('layouts.admin')

@section('title', 'Add Employee')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-user-plus me-2"></i>Add New Employee</h1>
    <a href="{{ route('admin.employees.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.employees.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone (with country code)</label>
                        <input type="text" name="phone" class="form-control" placeholder="+919876543210" value="{{ old('phone') }}">
                        <small class="text-muted">Format: +91XXXXXXXXXX</small>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimum 8 characters</small>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="">Select Role</option>
                            
                            <option value="waiter" {{ old('role') === 'waiter' ? 'selected' : '' }}>Waiter</option>
                            <option value="chef" {{ old('role') === 'chef' ? 'selected' : '' }}>Chef</option>
                            <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                        </select>
                        @error('role')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save me-2"></i>Add Employee
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
