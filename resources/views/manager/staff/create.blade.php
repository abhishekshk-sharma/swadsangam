@extends('layouts.manager')
@section('title', 'Add Staff')
@section('content')

<div class="d-flex justify-between align-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;color:var(--gray-800);">Add Staff</h2>
    <a href="{{ route('manager.staff.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="content-card" style="max-width:700px;">
    <div class="card-body">
        <form action="{{ route('manager.staff.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Name <span style="color:var(--error)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span style="color:var(--error)">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Role <span style="color:var(--error)">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="waiter"  {{ old('role') === 'waiter'  ? 'selected' : '' }}>Waiter</option>
                        <option value="chef"    {{ old('role') === 'chef'    ? 'selected' : '' }}>Chef</option>
                        <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Cashier</option>
                    </select>
                    @error('role')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:0.25rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem;">
                        <input type="checkbox" name="is_active" value="1" checked style="width:16px;height:16px;accent-color:var(--blue-600);"> Active
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Password <span style="color:var(--error)">*</span></label>
                    <div style="display:flex;gap:0.5rem;">
                        <input type="password" name="password" id="pw" class="form-control" required>
                        <button type="button" onclick="togglePw('pw',this)" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                    </div>
                    @error('password')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password <span style="color:var(--error)">*</span></label>
                    <div style="display:flex;gap:0.5rem;">
                        <input type="password" name="password_confirmation" id="pw2" class="form-control" required>
                        <button type="button" onclick="togglePw('pw2',this)" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
            <div style="margin-top:1.5rem;display:flex;gap:0.75rem;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save</button>
                <a href="{{ route('manager.staff.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePw(id, btn) {
    var inp = document.getElementById(id), ico = btn.querySelector('i');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.classList.toggle('fa-eye'); ico.classList.toggle('fa-eye-slash');
}
</script>
@endsection
