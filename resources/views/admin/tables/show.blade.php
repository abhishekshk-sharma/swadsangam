@extends('layouts.admin')

@section('title', 'Table QR Code')

@section('content')
<style>
    .qr-container {
        max-width: 500px;
        margin: 0 auto;
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        text-align: center;
    }
    .qr-title {
        font-size: 28px;
        font-weight: 700;
        color: #232f3e;
        margin-bottom: 8px;
    }
    .qr-subtitle {
        color: #666;
        font-size: 14px;
        margin-bottom: 32px;
    }
    .qr-code-wrapper {
        background: #f9f9f9;
        padding: 24px;
        border-radius: 8px;
        margin-bottom: 24px;
        border: 2px dashed #d5d9d9;
    }
    .qr-url {
        font-size: 12px;
        color: #666;
        word-break: break-all;
        background: #f9f9f9;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 24px;
        font-family: monospace;
    }
    .action-buttons {
        display: flex;
        gap: 12px;
        justify-content: center;
    }
    .qrlink{
        color: black;
        font-size: 16px;
        text-decoration: none;
    }
</style>

<div class="qr-container">
    <h1 class="qr-title">Table {{ $table->table_number }}</h1>
    <p class="qr-subtitle">
        <i class="fas fa-users me-2"></i>Capacity: {{ $table->capacity }} seats
        @if($table->category)
            <span class="mx-2">•</span>
            <i class="fas fa-tag me-2"></i>{{ $table->category->name }}
        @endif
    </p>
    
    <div class="qr-code-wrapper" id="qrCodeContainer">
        {!! $qrCodeImage !!}
    </div>
    
    <div class="qr-url">
        <i class="fas fa-link me-2"></i><a href="{{ url('/table/' . $table->qr_code) }}" class='qrlink'>{{ url('/table/' . $table->qr_code) }}</a>
    </div>
    
    <div class="action-buttons">
        <button onclick="downloadQR()" class="btn-success">
            <i class="fas fa-download me-2"></i>Download QR Code
        </button>
        <a href="{{ route('admin.tables.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Tables
        </a>
    </div>
</div>

<script>
function downloadQR() {
    const svg = document.querySelector('#qrCodeContainer svg');
    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    canvas.width = 512;
    canvas.height = 512;
    
    img.onload = function() {
        ctx.fillStyle = 'white';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        const link = document.createElement('a');
        link.download = 'Table_{{ $table->table_number }}_QR_Code.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    };
    
    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
}
</script>
@endsection
