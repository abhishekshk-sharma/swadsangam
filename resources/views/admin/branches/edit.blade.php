@extends('layouts.admin')
@section('title', 'Edit Branch')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">✏️ Edit Branch</h2>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>
<div class="content-card" style="max-width:540px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.branches.update', $branch) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $branch->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $branch->address) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ $branch->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$branch->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2">Update Branch</button>
        </form>
    </div>
</div>
@endsection
