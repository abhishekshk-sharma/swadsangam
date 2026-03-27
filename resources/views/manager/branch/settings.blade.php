@extends('layouts.manager')
@section('title', 'Branch Settings')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size:1.2rem;font-weight:600;">Branch Settings</h2>
</div>

<div class="content-card" style="max-width:540px;">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-store"></i> {{ $branch->name }}</div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('manager.branch.settings.update') }}">
            @csrf @method('PATCH')
            <div class="form-group">
                <label class="form-label">Branch Name</label>
                <input type="text" class="form-control" value="{{ $branch->name }}" disabled>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" value="{{ $branch->phone ?? '—' }}" disabled>
            </div>
            <div class="form-group">
                <label class="form-label">UPI ID <span style="font-size:11px;color:var(--gray-500);">(used for payment QR on bills)</span></label>
                <input type="text" name="upi_id" class="form-control @error('upi_id') is-invalid @enderror"
                       value="{{ old('upi_id', $branch->upi_id) }}"
                       placeholder="e.g. restaurant@upi">
                @error('upi_id')<div style="color:var(--error);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">GST Slab <span style="font-size:11px;color:var(--gray-500);">(leave blank to disable GST)</span></label>
                <select name="gst_slab_id" class="form-select" id="gstSlabSelect" onchange="toggleGstMode()">
                    <option value="">— No GST —</option>
                    @foreach($gstSlabs as $slab)
                        <option value="{{ $slab->id }}" {{ old('gst_slab_id', $branch->gst_slab_id) == $slab->id ? 'selected' : '' }}>
                            {{ $slab->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="gstModeGroup" style="{{ old('gst_slab_id', $branch->gst_slab_id) ? '' : 'display:none;' }}">
                <label class="form-label">GST Mode</label>
                <select name="gst_mode" class="form-select">
                    <option value="included" {{ old('gst_mode', $branch->gst_mode) === 'included' ? 'selected' : '' }}>Included — GST is already in item prices</option>
                    <option value="excluded" {{ old('gst_mode', $branch->gst_mode) === 'excluded' ? 'selected' : '' }}>Excluded — Add GST on top of bill total</option>
                </select>
                <div style="font-size:11px;color:var(--gray-500);margin-top:4px;">
                    <strong>Included:</strong> ₹100 bill → shows ₹100 (GST breakdown shown, total unchanged)<br>
                    <strong>Excluded:</strong> ₹100 bill → shows ₹105 (5% GST added on top)
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Settings</button>
        </form>
    </div>
</div>

<script>
function toggleGstMode() {
    const val = document.getElementById('gstSlabSelect').value;
    document.getElementById('gstModeGroup').style.display = val ? '' : 'none';
}
</script>
@endsection
