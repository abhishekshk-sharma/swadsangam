@extends('layouts.admin')
@section('title', 'Add Branch')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">🏪 Add Branch</h2>
    <a href="{{ route('admin.branches.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
</div>
<div class="content-card" style="max-width:540px;">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.branches.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-2">Create Branch</button>
        </form>
    </div>
</div>
@endsection
