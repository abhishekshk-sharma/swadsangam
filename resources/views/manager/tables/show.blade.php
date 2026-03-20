@extends('layouts.manager')
@section('title', 'Table QR Code')
@section('content')

<div style="max-width:500px;margin:0 auto;background:#fff;padding:40px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e3e6e8;text-align:center;">
    <h1 style="font-size:28px;font-weight:700;color:#232f3e;margin-bottom:8px;">Table {{ $table->table_number }}</h1>
    <p style="color:#666;font-size:14px;margin-bottom:32px;">
        <i class="fas fa-users me-2"></i>Capacity: {{ $table->capacity }} seats
        @if($table->category)<span class="mx-2">•</span><i class="fas fa-tag me-2"></i>{{ $table->category->name }}@endif
    </p>

    <div style="background:#f9f9f9;padding:24px;border-radius:8px;margin-bottom:24px;border:2px dashed #d5d9d9;">
        {!! $qrCodeImage !!}
    </div>

    <div style="font-size:12px;color:#666;word-break:break-all;background:#f9f9f9;padding:12px;border-radius:4px;margin-bottom:24px;font-family:monospace;">
        <i class="fas fa-link me-2"></i><a href="{{ url('/table/' . $table->qr_code) }}" style="color:#232f3e;text-decoration:none;">{{ url('/table/' . $table->qr_code) }}</a>
    </div>

    <div style="display:flex;gap:12px;justify-content:center;">
        <button onclick="downloadQR()" style="background:#067d62;color:#fff;padding:8px 20px;border-radius:4px;font-size:13px;font-weight:600;border:none;cursor:pointer;">
            <i class="fas fa-download me-2"></i>Download QR
        </button>
        <a href="{{ route('manager.tables.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<script>
function downloadQR() {
    const svg = document.querySelector('svg');
    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas = document.createElement('canvas');
    canvas.width = 512; canvas.height = 512;
    const ctx = canvas.getContext('2d');
    const img = new Image();
    img.onload = function() {
        ctx.fillStyle = 'white'; ctx.fillRect(0, 0, 512, 512);
        ctx.drawImage(img, 0, 0, 512, 512);
        const link = document.createElement('a');
        link.download = 'Table_{{ $table->table_number }}_QR.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    };
    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
}
</script>
@endsection
