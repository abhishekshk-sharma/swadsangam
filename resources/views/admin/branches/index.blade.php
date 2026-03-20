@extends('layouts.admin')
@section('title', 'Branches')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">🏪 Branches</h2>
    <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Branch
    </a>
</div>

@if($branches->isEmpty())
    <div class="content-card p-5 text-center text-muted">
        <i class="fas fa-store fa-3x mb-3 d-block" style="color:#ddd;"></i>
        No branches yet. Create your first branch.
    </div>
@else
    <div class="row g-3">
        @foreach($branches as $branch)
        <div class="col-md-4">
            <div class="content-card h-100">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">{{ $branch->name }}</h5>
                            @if($branch->address)
                                <div style="font-size:13px;color:#64748b;"><i class="fas fa-map-marker-alt me-1"></i>{{ $branch->address }}</div>
                            @endif
                            @if($branch->phone)
                                <div style="font-size:13px;color:#64748b;"><i class="fas fa-phone me-1"></i>{{ $branch->phone }}</div>
                            @endif
                        </div>
                        <span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-warning' }}">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="d-flex gap-3 mb-3" style="font-size:13px;">
                        <div class="text-center">
                            <div class="fw-bold" style="font-size:18px;">{{ $branch->employees_count }}</div>
                            <div style="color:#64748b;">Staff</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" style="font-size:18px;">{{ $branch->tables_count }}</div>
                            <div style="color:#64748b;">Tables</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold" style="font-size:18px;">{{ $branch->orders_count }}</div>
                            <div style="color:#64748b;">Orders</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.branches.show', $branch) }}" class="btn btn-secondary btn-sm flex-fill text-center">View</a>
                        <a href="{{ route('admin.branches.edit', $branch) }}" class="btn btn-primary btn-sm flex-fill text-center">Edit</a>
                        <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" onsubmit="return confirm('Delete this branch?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection
