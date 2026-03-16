@extends('layouts.admin')
@section('title', 'Menu OCR — Extract to Excel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">📷 Menu OCR — Extract to Excel</h2>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Upload Card --}}
    <div class="col-md-6">
        <div class="content-card h-100">
            <div class="p-3 border-bottom bg-light" style="border-radius:8px 8px 0 0;">
                <strong><i class="fas fa-upload me-2"></i>Upload Menu Image</strong>
            </div>
            <div class="p-4">
                <form method="POST" action="{{ route('admin.menu-ocr.process') }}" enctype="multipart/form-data" id="ocrForm">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Menu Image</label>
                        <div id="dropZone" style="
                            border: 2px dashed #cbd5e1;
                            border-radius: 10px;
                            padding: 40px 20px;
                            text-align: center;
                            cursor: pointer;
                            transition: border-color 0.2s, background 0.2s;
                            background: #f8fafc;
                        ">
                            <i class="fas fa-image" style="font-size:36px;color:#94a3b8;display:block;margin-bottom:10px;"></i>
                            <p style="color:#64748b;margin:0 0 8px;">Drag & drop or <strong>click to browse</strong></p>
                            <p style="color:#94a3b8;font-size:12px;margin:0;">JPG, PNG, WEBP — max 5MB</p>
                            <input type="file" name="menu_image" id="menuImage" accept="image/*"
                                   style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                        </div>
                        @error('menu_image')
                            <div style="color:#dc2626;font-size:13px;margin-top:6px;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Preview --}}
                    <div id="previewWrap" style="display:none;margin-bottom:20px;">
                        <img id="previewImg" src="" alt="Preview"
                             style="width:100%;max-height:280px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;">
                        <p id="previewName" style="font-size:12px;color:#64748b;margin-top:6px;text-align:center;"></p>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="fas fa-magic me-2"></i> Extract & Download Excel
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- How it works --}}
    <div class="col-md-6">
        <div class="content-card h-100">
            <div class="p-3 border-bottom bg-light" style="border-radius:8px 8px 0 0;">
                <strong><i class="fas fa-info-circle me-2"></i>How It Works</strong>
            </div>
            <div class="p-4">
                <div style="display:flex;flex-direction:column;gap:20px;">
                    @foreach([
                        ['fas fa-upload',       '#3b82f6', 'Upload Menu Image',       'Take a photo or scan of your printed menu and upload it here.'],
                        ['fas fa-eye',          '#8b5cf6', 'OCR Text Extraction',      'Google Cloud Vision reads all text from the image with high accuracy.'],
                        ['fas fa-list-ul',      '#059669', 'Smart Item Detection',     'The system detects item names paired with prices automatically.'],
                        ['fas fa-file-excel',   '#16a34a', 'Download Excel',           'A clean Excel file with Item Name and Price columns is downloaded instantly.'],
                    ] as [$icon, $color, $title, $desc])
                    <div style="display:flex;gap:14px;align-items:flex-start;">
                        <div style="width:38px;height:38px;border-radius:9px;background:{{ $color }}1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="{{ $icon }}" style="color:{{ $color }};font-size:15px;"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#1e293b;">{{ $title }}</div>
                            <div style="font-size:13px;color:#64748b;margin-top:2px;">{{ $desc }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="alert alert-warning mt-4 py-2 small mb-0">
                    <strong>💡 Tips for best results:</strong>
                    <ul style="margin:6px 0 0 16px;padding:0;">
                        <li>Use a clear, well-lit photo</li>
                        <li>Ensure prices are next to item names on the same line</li>
                        <li>Avoid blurry or skewed images</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var input    = document.getElementById('menuImage');
    var dropZone = document.getElementById('dropZone');
    var preview  = document.getElementById('previewWrap');
    var previewImg  = document.getElementById('previewImg');
    var previewName = document.getElementById('previewName');
    var submitBtn   = document.getElementById('submitBtn');
    var form        = document.getElementById('ocrForm');

    function showPreview(file) {
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewName.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            preview.style.display = '';
            dropZone.style.borderColor = '#3b82f6';
            dropZone.style.background  = '#eff6ff';
        };
        reader.readAsDataURL(file);
    }

    input.addEventListener('change', function () {
        showPreview(this.files[0]);
    });

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.style.borderColor = '#3b82f6';
        dropZone.style.background  = '#eff6ff';
    });

    dropZone.addEventListener('dragleave', function () {
        dropZone.style.borderColor = '#cbd5e1';
        dropZone.style.background  = '#f8fafc';
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        var file = e.dataTransfer.files[0];
        if (file) {
            var dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            showPreview(file);
        }
    });

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
    });
}());
</script>
@endpush
