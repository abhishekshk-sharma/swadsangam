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
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" id="roleSelect" required onchange="toggleBranch()">
                            <option value="">Select Role</option>
                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="waiter" {{ old('role') === 'waiter' ? 'selected' : '' }}>Waiter</option>
                            <option value="chef" {{ old('role') === 'chef' ? 'selected' : '' }}>Chef</option>
                            <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                        </select>
                        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3" id="branchWrap">
                        <label class="form-label fw-semibold">Assign Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">— No Branch (Admin-level) —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Manager/staff assigned to a branch can only see that branch's data.</small>
                        @error('branch_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

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
                        <input type="text" name="phone" class="form-control" placeholder="9876543210" value="{{ old('phone') }}">
                        <small class="text-muted">Enter 10 digit number!</small>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirmation', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
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
<script>
function toggleBranch() {
    var role = document.getElementById('roleSelect').value;
    document.getElementById('branchWrap').style.display = role ? '' : 'none';
}
toggleBranch();
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endsection
