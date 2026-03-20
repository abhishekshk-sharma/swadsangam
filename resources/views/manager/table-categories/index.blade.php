@extends('layouts.manager')
@section('title', 'Table Categories')
@section('content')
@php $authUser = auth()->guard('employee')->user(); @endphp

<div class="content-card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-layer-group"></i> Table Categories</div>
        <a href="{{ route('manager.tables.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Tables</a>
    </div>
    <div class="card-body">

        {{-- Add Form --}}
        <div class="content-card mb-4">
            <div class="card-header" style="background:var(--blue-600);">
                <div class="card-title" style="color:white;"><i class="fas fa-plus-circle"></i> Add New Category</div>
            </div>
            <div class="card-body">
                <form action="{{ route('manager.table-categories.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., AC, Non-AC, VIP" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description (Optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="Brief description">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="content-card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-list"></i> All Categories</div>
            </div>
            <div class="card-body p-0" style="overflow-x:auto;">
                <table class="table" style="min-width:560px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Tables</th>
                            <th style="text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td><strong><i class="fas fa-tag me-2" style="color:var(--blue-500);"></i>{{ $cat->name }}</strong></td>
                            <td style="color:var(--gray-500);">{{ $cat->description ?? '-' }}</td>
                            <td>
                                @if($cat->branch_id)
                                    <span class="badge badge-info">Branch</span>
                                @elseif($cat->tenant_id)
                                    <span class="badge badge-neutral">Custom</span>
                                @else
                                    <span class="badge badge-neutral">Global</span>
                                @endif
                            </td>
                            <td><span class="badge badge-info">{{ $cat->tables_count }}</span></td>
                            <td style="text-align:center;">
                                @if($cat->branch_id === $authUser->branch_id)
                                    <button onclick="toggleEdit({{ $cat->id }})" class="btn btn-secondary btn-sm"><i class="fas fa-edit me-1"></i>Edit</button>
                                    <form action="{{ route('manager.table-categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Delete this category?')" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background:var(--error-light);color:var(--error);border:1px solid var(--error);"><i class="fas fa-trash me-1"></i>Delete</button>
                                    </form>
                                @else
                                    <span style="color:var(--gray-400);font-size:13px;font-style:italic;"><i class="fas fa-lock me-1"></i>Protected</span>
                                @endif
                            </td>
                        </tr>
                        @if($cat->branch_id === $authUser->branch_id)
                        <tr id="editRow{{ $cat->id }}" style="display:none;background:var(--blue-50);">
                            <td colspan="5" style="padding:12px 16px;">
                                <form action="{{ route('manager.table-categories.update', $cat->id) }}" method="POST" class="d-flex gap-2 align-items-end flex-wrap">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="form-label mb-1" style="font-size:12px;">Name</label>
                                        <input type="text" name="name" class="form-control form-control-sm" value="{{ $cat->name }}" required style="min-width:180px;">
                                    </div>
                                    <div>
                                        <label class="form-label mb-1" style="font-size:12px;">Description</label>
                                        <input type="text" name="description" class="form-control form-control-sm" value="{{ $cat->description }}" style="min-width:220px;">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i>Save</button>
                                    <button type="button" onclick="toggleEdit({{ $cat->id }})" class="btn btn-secondary btn-sm">Cancel</button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:48px;color:var(--gray-400);">
                                <i class="fas fa-inbox" style="font-size:3rem;display:block;margin-bottom:12px;opacity:.5;"></i>
                                No categories yet. Create one above.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function toggleEdit(id) {
    const row = document.getElementById('editRow' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>
@endpush
@endsection
