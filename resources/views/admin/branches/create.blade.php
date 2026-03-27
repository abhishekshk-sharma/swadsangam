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
            <div class="form-group">
                <label class="form-label">UPI ID</label>
                <input type="text" name="upi_id" class="form-control" value="{{ old('upi_id') }}" placeholder="e.g. restaurant@upi">
            </div>

            <div class="form-group">
                <label class="form-label">GST Slab <span style="font-size:11px;color:var(--gray-500);">(leave blank to disable GST)</span></label>
                <select name="gst_slab_id" class="form-select" id="gstSlabSelect" onchange="toggleGstMode()">
                    <option value="">— No GST —</option>
                    @foreach($gstSlabs as $slab)
                        <option value="{{ $slab->id }}" {{ old('gst_slab_id') == $slab->id ? 'selected' : '' }}>
                            {{ $slab->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" id="gstModeGroup" style="display:none;">
                <label class="form-label">GST Mode</label>
                <select name="gst_mode" class="form-select">
                    <option value="included" {{ old('gst_mode') === 'included' ? 'selected' : '' }}>Included — GST is already in item prices</option>
                    <option value="excluded" {{ old('gst_mode') === 'excluded' ? 'selected' : '' }}>Excluded — Add GST on top of bill total</option>
                </select>
                <div style="font-size:11px;color:var(--gray-500);margin-top:4px;">
                    <strong>Included:</strong> ₹100 bill → shows ₹100 (GST breakdown shown, total unchanged)<br>
                    <strong>Excluded:</strong> ₹100 bill → shows ₹105 (5% GST added on top)
                </div>
            </div>
            <div class="form-group" id="gstNumberGroup" style="display:none;">
                <label class="form-label">GSTIN <span class="text-danger">*</span> <span style="font-size:11px;color:var(--gray-500);">(required to enable GST)</span></label>
                <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror"
                    value="{{ old('gst_number') }}"
                    placeholder="e.g. 27AAPFU0939F1ZV" maxlength="15" style="text-transform:uppercase;">
                @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div style="font-size:11px;color:var(--gray-500);margin-top:4px;">15-character GST Identification Number</div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Create Branch</button>
        </form>
    </div>
</div>
<script>
function toggleGstMode() {
    const val = document.getElementById('gstSlabSelect').value;
    document.getElementById('gstModeGroup').style.display   = val ? '' : 'none';
    document.getElementById('gstNumberGroup').style.display = val ? '' : 'none';
}
</script>
@endsection
