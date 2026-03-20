@extends('layouts.manager')
@section('title', 'Create Table')
@section('content')

<div class="content-card" style="max-width:600px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-plus-circle"></i> Create Table</div>
        <a href="{{ route('manager.tables.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('manager.tables.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Category (Optional)</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <select name="category_id" id="category_id" class="form-select" style="flex:1;">
                        <option value="">No Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" data-prefix="{{ strtoupper(substr($category->name, 0, 1)) }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="document.getElementById('quickCatModal').style.display='flex'" class="btn btn-secondary btn-sm" style="white-space:nowrap;"><i class="fas fa-plus"></i> New</button>
                </div>
            </div>

            <div class="form-group" id="bulk-row" style="display:none;">
                <label class="form-label">Number of Tables to Create</label>
                <input type="number" name="count" id="count" min="1" max="100" class="form-control" placeholder="e.g. 5">
                <div id="bulk-preview" style="margin-top:8px;font-size:13px;color:#067d62;"></div>
            </div>

            <div class="form-group" id="manual-name-row">
                <label class="form-label">Table Number / Name</label>
                <input type="text" name="table_number" id="table_number" class="form-control" placeholder="e.g., Table 1, VIP-A" value="{{ old('table_number') }}">
                @error('table_number')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Capacity (seats)</label>
                <input type="number" name="capacity" min="1" value="{{ old('capacity', 4) }}" class="form-control" required>
                @error('capacity')<div style="color:var(--error);font-size:0.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:12px;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-check me-2"></i>Create</button>
                <a href="{{ route('manager.tables.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
const catSel = document.getElementById('category_id');
const bulkRow = document.getElementById('bulk-row');
const manualRow = document.getElementById('manual-name-row');
const countInput = document.getElementById('count');
const preview = document.getElementById('bulk-preview');
const tableNumInput = document.getElementById('table_number');

function updateUI() {
    const hasCat = catSel.value && catSel.value !== '';
    bulkRow.style.display = hasCat ? '' : 'none';
    if (!hasCat) { countInput.value = ''; preview.textContent = ''; manualRow.style.display = ''; tableNumInput.required = true; }
    updatePreview();
}

function updatePreview() {
    const opt = catSel.options[catSel.selectedIndex];
    const prefix = opt ? (opt.dataset.prefix || '') : '';
    const n = parseInt(countInput.value);
    if (prefix && n > 0) {
        manualRow.style.display = 'none'; tableNumInput.required = false;
        const labels = Array.from({length: Math.min(n, 5)}, (_, i) => prefix + (i + 1));
        preview.innerHTML = '<i class="fas fa-eye me-1"></i>Will create: <strong>' + labels.join(', ') + (n > 5 ? ', ...' : '') + '</strong>';
    } else if (catSel.value) {
        manualRow.style.display = ''; tableNumInput.required = true; preview.textContent = '';
    }
}

catSel.addEventListener('change', updateUI);
countInput.addEventListener('input', updatePreview);
</script>
@endsection

{{-- Quick Create Category Modal --}}
<div id="quickCatModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:1rem;font-weight:600;margin-bottom:16px;"><i class="fas fa-layer-group me-2" style="color:var(--blue-500);"></i>New Table Category</h3>
        <div class="form-group">
            <label class="form-label">Name *</label>
            <input type="text" id="qcName" class="form-control" placeholder="e.g., Rooftop, Garden">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" id="qcDesc" class="form-control" placeholder="Optional">
        </div>
        <div id="qcError" style="color:var(--error);font-size:13px;margin-bottom:8px;display:none;"></div>
        <div style="display:flex;gap:8px;margin-top:16px;">
            <button type="button" onclick="quickCreateCat()" class="btn btn-primary" style="flex:1;"><i class="fas fa-plus me-1"></i>Create & Select</button>
            <button type="button" onclick="document.getElementById('quickCatModal').style.display='none'" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function quickCreateCat() {
    const name = document.getElementById('qcName').value.trim();
    const desc = document.getElementById('qcDesc').value.trim();
    const err  = document.getElementById('qcError');
    if (!name) { err.textContent = 'Name is required.'; err.style.display = 'block'; return; }
    err.style.display = 'none';

    fetch('{{ route('manager.table-categories.quickCreate') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ name, description: desc })
    })
    .then(r => r.json())
    .then(data => {
        const sel = document.getElementById('category_id');
        const opt = new Option(data.name, data.id, true, true);
        opt.dataset.prefix = data.name.charAt(0).toUpperCase();
        sel.appendChild(opt);
        sel.value = data.id;
        sel.dispatchEvent(new Event('change'));
        document.getElementById('quickCatModal').style.display = 'none';
        document.getElementById('qcName').value = '';
        document.getElementById('qcDesc').value = '';
    })
    .catch(() => { err.textContent = 'Failed to create category.'; err.style.display = 'block'; });
}
</script>
@endpush
