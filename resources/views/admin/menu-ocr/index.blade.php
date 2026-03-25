@extends('layouts.admin')
@section('title', 'Menu OCR — Extract to Menu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">📷 Menu OCR — Extract to Menu</h2>
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
                <form id="ocrForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Output Language</label>
                        <div class="d-flex gap-2">
                            @foreach(['en' => '🇬🇧 English', 'hi' => '🇮🇳 Hindi', 'gu' => '🇮🇳 Gujarati'] as $code => $label)
                            <label style="flex:1;cursor:pointer;">
                                <input type="radio" name="language" value="{{ $code }}" class="d-none lang-radio"
                                    {{ $code === 'en' ? 'checked' : '' }}>
                                <div class="lang-btn text-center py-2 px-1 rounded border fw-semibold"
                                     style="font-size:13px;transition:all .15s;
                                     {{ $code === 'en' ? 'background:#3b82f6;color:#fff;border-color:#3b82f6;' : 'background:#f8fafc;color:#475569;border-color:#cbd5e1;' }}">
                                    {{ $label }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">OCR Mode</label>
                        <div class="d-flex gap-2">
                            <label style="flex:1;cursor:pointer;">
                                <input type="radio" name="ocr_mode" value="standard" class="d-none mode-radio" checked>
                                <div class="mode-btn text-center py-2 px-1 rounded border fw-semibold"
                                     style="font-size:13px;transition:all .15s;background:#3b82f6;color:#fff;border-color:#3b82f6;">
                                    <i class="fas fa-list me-1"></i> Standard
                                </div>
                            </label>
                            <label style="flex:1;cursor:pointer;">
                                <input type="radio" name="ocr_mode" value="variant" class="d-none mode-radio">
                                <div class="mode-btn text-center py-2 px-1 rounded border fw-semibold"
                                     style="font-size:13px;transition:all .15s;background:#f8fafc;color:#475569;border-color:#cbd5e1;">
                                    <i class="fas fa-layer-group me-1"></i> Variant
                                </div>
                            </label>
                        </div>
                        <div id="variantHint" style="display:none;font-size:12px;color:#7c3aed;margin-top:6px;padding:6px 10px;background:#f5f3ff;border-radius:6px;">
                            <i class="fas fa-info-circle me-1"></i> Use when image has items with multiple variants like <strong>dabeli (oil/butter) 15/25</strong>
                        </div>
                    </div>
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
                            position: relative;
                            overflow: hidden;
                        ">
                            <i class="fas fa-image" style="font-size:36px;color:#94a3b8;display:block;margin-bottom:10px;"></i>
                            <p style="color:#64748b;margin:0 0 8px;">Drag & drop or <strong>click to browse</strong></p>
                            <p style="color:#94a3b8;font-size:12px;margin:0;">JPG, PNG, WEBP — max 5MB</p>
                            <input type="file" name="menu_image" id="menuImage" accept="image/*"
                                   style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;z-index:1;">
                        </div>
                    </div>

                    <div id="previewWrap" style="display:none;margin-bottom:20px;">
                        <img id="previewImg" src="" alt="Preview"
                             style="width:100%;max-height:280px;object-fit:contain;border-radius:8px;border:1px solid #e2e8f0;">
                        <p id="previewName" style="font-size:12px;color:#64748b;margin-top:6px;text-align:center;"></p>
                    </div>

                    <div id="errorBox" class="alert alert-danger" style="display:none;"></div>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="fas fa-magic me-2"></i> Extract & Review
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
                        ['fas fa-upload',     '#3b82f6', 'Upload Menu Image',    'Take a photo or scan of your printed menu and upload it here.'],
                        ['fas fa-eye',        '#8b5cf6', 'OCR Text Extraction',  'Google Cloud Vision reads all text from the image.'],
                        ['fas fa-edit',       '#f59e0b', 'Review & Edit',        'A preview modal shows all detected items — edit names, prices or delete rows before saving.'],
                        ['fas fa-database',   '#059669', 'Import to Menu',       'Confirmed items are inserted into your menu with categories automatically.'],
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
                        <li>Prices should be next to item names on the same line</li>
                        <li>Avoid blurry or skewed images</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Excel Upload Row --}}
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="content-card">
            <div class="p-3 border-bottom bg-light" style="border-radius:8px 8px 0 0;">
                <strong><i class="fas fa-file-excel me-2" style="color:#16a34a;"></i>Import from Excel / CSV</strong>
            </div>
            <div class="p-4">
                {{-- Standard Excel --}}
                <form method="POST" action="{{ route('admin.menu-ocr.excel') }}" enctype="multipart/form-data" id="excelForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Standard Format <span style="font-size:11px;color:#64748b;font-weight:400;">(Col A = Name, Col B = Price)</span></label>
                            <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100" id="excelBtn">
                                <i class="fas fa-table me-2"></i>Upload & Review
                            </button>
                        </div>
                    </div>
                </form>


            </div>
        </div>
    </div>
</div>

{{-- Review Modal --}}
<div id="reviewOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:99999;overflow-y:auto;padding:24px 12px;">
    <div style="background:#fff;border-radius:12px;max-width:780px;margin:0 auto;box-shadow:0 8px 32px rgba(0,0,0,0.2);">

        <div style="padding:18px 24px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:17px;font-weight:700;color:#1e293b;"><i class="fas fa-edit me-2" style="color:#f59e0b;"></i>Review Extracted Menu</div>
            <button onclick="closeReview()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;">✕</button>
        </div>

        <div style="padding:20px 24px;">
            <p style="font-size:13px;color:#64748b;margin-bottom:16px;">
                Edit category names, item names or prices. Click <strong>✕</strong> on any row to remove it. Empty categories are ignored on import.
            </p>
            <div id="reviewBody"></div>
        </div>

        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;display:flex;flex-wrap:wrap;gap:8px;">
            <button onclick="closeReview()" class="btn btn-secondary" style="flex:1;min-width:100px;">Cancel</button>
            <button onclick="exportReviewExcel()" class="btn btn-success" style="flex:1;min-width:120px;">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </button>
            <button onclick="submitImport()" class="btn btn-primary" id="importBtn" style="flex:2;min-width:140px;">
                <i class="fas fa-database me-1"></i>Import to Menu
            </button>
        </div>
    </div>
</div>

{{-- Hidden import form --}}
<form id="importForm" method="POST" action="{{ route('admin.menu-ocr.import') }}" style="display:none;">
    @csrf
    <input type="hidden" name="sections" id="importSections">
</form>
@endsection

@push('scripts')
<script>
(function () {
    var input       = document.getElementById('menuImage');
    var dropZone    = document.getElementById('dropZone');
    var preview     = document.getElementById('previewWrap');
    var previewImg  = document.getElementById('previewImg');
    var previewName = document.getElementById('previewName');
    var submitBtn   = document.getElementById('submitBtn');
    var errorBox    = document.getElementById('errorBox');
    var form        = document.getElementById('ocrForm');

    // ── Language toggle styling ───────────────────────────────────────────────
    document.querySelectorAll('.lang-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.lang-radio').forEach(function (r) {
                var btn = r.nextElementSibling;
                if (r.checked) {
                    btn.style.background = '#3b82f6';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#3b82f6';
                } else {
                    btn.style.background = '#f8fafc';
                    btn.style.color = '#475569';
                    btn.style.borderColor = '#cbd5e1';
                }
            });
        });
    });

    // ── OCR Mode toggle styling ────────────────────────────────────────────
    document.querySelectorAll('.mode-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.mode-radio').forEach(function (r) {
                var btn = r.nextElementSibling;
                if (r.checked) {
                    btn.style.background = '#3b82f6';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#3b82f6';
                } else {
                    btn.style.background = '#f8fafc';
                    btn.style.color = '#475569';
                    btn.style.borderColor = '#cbd5e1';
                }
            });
            var hint = document.getElementById('variantHint');
            hint.style.display = document.querySelector('.mode-radio[value="variant"]').checked ? '' : 'none';
        });
    });

    // ── Preview ──────────────────────────────────────────────────────────────
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

    input.addEventListener('change', function () { showPreview(this.files[0]); });

    dropZone.addEventListener('dragover',  function (e) { e.preventDefault(); dropZone.style.borderColor='#3b82f6'; dropZone.style.background='#eff6ff'; });
    dropZone.addEventListener('dragleave', function ()  { dropZone.style.borderColor='#cbd5e1'; dropZone.style.background='#f8fafc'; });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        var file = e.dataTransfer.files[0];
        if (file) { var dt = new DataTransfer(); dt.items.add(file); input.files = dt.files; showPreview(file); }
    });

    // ── AJAX submit ──────────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        errorBox.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Extracting...';

        var fd = new FormData(form);
        fetch('{{ route('admin.menu-ocr.process') }}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function (r) {
            if (!r.ok && r.status !== 422) {
                return r.text().then(function (t) { throw new Error(t); });
            }
            return r.json();
        })
        .then(function (data) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-magic me-2"></i> Extract & Review';
            if (data.error || data.message) {
                errorBox.textContent = data.error || data.message;
                errorBox.style.display = '';
                return;
            }
            openReview(data.sections);
        })
        .catch(function (err) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-magic me-2"></i> Extract & Review';
            errorBox.textContent = 'Error: ' + (err.message || 'Something went wrong. Please try again.');
            errorBox.style.display = '';
            console.error('OCR Error:', err);
        });
    });

    // ── Export reviewed data to Excel (CSV) ─────────────────────────────────
    window.exportReviewExcel = function () {
        var rows = [];
        var lastCat = null;

        document.querySelectorAll('#reviewBody > div[data-si]').forEach(function (wrap) {
            var catInput = wrap.querySelector('input[data-field="category"]');
            var category = catInput ? catInput.value.trim() : '';

            wrap.querySelectorAll('tbody tr').forEach(function (tr) {
                var nameEl  = tr.querySelector('input[data-field="name"]');
                var priceEl = tr.querySelector('input[data-field="price"]');
                var name    = nameEl  ? nameEl.value.trim()  : '';
                var price   = priceEl ? priceEl.value.trim() : '';
                if (!name) return;
                // Emit category row (col A only, no price) when category changes
                if (category !== lastCat) {
                    rows.push([category, '']);
                    lastCat = category;
                }
                rows.push([name, price]);
            });
        });

        if (!rows.length) { alert('No items to export.'); return; }

        var csv = rows.map(function (r) {
            return r.map(function (c) {
                var s = String(c).replace(/"/g, '""');
                return /[,"\n]/.test(s) ? '"' + s + '"' : s;
            }).join(',');
        }).join('\n');

        var blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        var a    = document.createElement('a');
        a.href   = URL.createObjectURL(blob);
        a.download = 'menu-ocr-export.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    };

    // ── Download Excel ────────────────────────────────────────────────────────
    window.downloadExcel = function (sections) {
        var rows = [['Item Name', 'Price']];
        sections.forEach(function (section) {
            section.items.forEach(function (item) {
                rows.push([item.name, item.price]);
            });
        });

        var csv = rows.map(function (r) {
            return r.map(function (c) {
                var s = String(c).replace(/"/g, '""');
                return /[,"\n]/.test(s) ? '"' + s + '"' : s;
            }).join(',');
        }).join('\n');

        var blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
        var a    = document.createElement('a');
        a.href   = URL.createObjectURL(blob);
        a.download = 'menu-extract.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    };

    // ── Review modal ─────────────────────────────────────────────────────────
    window.openReview = function (sections) {
        var body = document.getElementById('reviewBody');
        body.innerHTML = '';

        sections.forEach(function (section, si) {
            var wrap = document.createElement('div');
            wrap.style.cssText = 'margin-bottom:20px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;';
            wrap.setAttribute('data-si', si);

            // Category header row
            var catRow = document.createElement('div');
            catRow.style.cssText = 'background:#f1f5f9;padding:10px 14px;display:flex;align-items:center;gap:10px;';
            catRow.innerHTML =
                '<span style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;flex-shrink:0;">Category</span>' +
                '<input type="text" value="' + esc(section.category) + '" data-field="category" data-si="' + si + '"' +
                ' style="flex:1;border:1px solid #cbd5e1;border-radius:6px;padding:5px 10px;font-size:13px;font-weight:600;color:#1e293b;">';
            wrap.appendChild(catRow);

            // Items table
            var tbl = document.createElement('table');
            tbl.style.cssText = 'width:100%;border-collapse:collapse;';
            tbl.innerHTML = '<thead><tr style="background:#fafafa;">' +
                '<th style="padding:8px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:700;border-bottom:1px solid #e2e8f0;">Item Name</th>' +
                '<th style="padding:8px 14px;font-size:11px;color:#64748b;text-align:left;font-weight:700;border-bottom:1px solid #e2e8f0;width:120px;">Price (₹)</th>' +
                '<th style="padding:8px 14px;width:40px;border-bottom:1px solid #e2e8f0;"></th>' +
                '</tr></thead>';
            var tbody = document.createElement('tbody');
            tbody.setAttribute('data-si', si);

            section.items.forEach(function (item, ii) {
                tbody.appendChild(makeItemRow(si, ii, item));
            });

            tbl.appendChild(tbody);
            wrap.appendChild(tbl);

            // Add item button
            var addRow = document.createElement('div');
            addRow.style.cssText = 'padding:8px 14px;border-top:1px solid #f1f5f9;';
            addRow.innerHTML = '<button type="button" onclick="addItem(' + si + ')" style="background:none;border:none;color:#3b82f6;font-size:13px;font-weight:600;cursor:pointer;padding:0;">' +
                '<i class="fas fa-plus me-1"></i>Add item</button>';
            wrap.appendChild(addRow);

            body.appendChild(wrap);
        });

        document.getElementById('reviewOverlay').style.display = '';
    };

    window.makeItemRow = function (si, ii, item) {
        var tr = document.createElement('tr');
        tr.style.borderBottom = '1px solid #f1f5f9';
        tr.setAttribute('data-ii', ii);
        tr.innerHTML =
            '<td style="padding:7px 14px;">' +
                '<input type="text" value="' + esc(item.name) + '" data-field="name" data-si="' + si + '" data-ii="' + ii + '"' +
                ' style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:5px 8px;font-size:13px;">' +
            '</td>' +
            '<td style="padding:7px 14px;">' +
                '<input type="number" value="' + item.price + '" data-field="price" data-si="' + si + '" data-ii="' + ii + '" min="0" step="0.01"' +
                ' style="width:100%;border:1px solid #e2e8f0;border-radius:6px;padding:5px 8px;font-size:13px;">' +
            '</td>' +
            '<td style="padding:7px 14px;text-align:center;">' +
                '<button type="button" onclick="deleteItem(this)" style="background:none;border:none;color:#ef4444;font-size:15px;cursor:pointer;line-height:1;">✕</button>' +
            '</td>';
        return tr;
    };

    window.addItem = function (si) {
        var tbody = document.querySelector('tbody[data-si="' + si + '"]');
        var ii = tbody.rows.length;
        tbody.appendChild(makeItemRow(si, ii, { name: '', price: '' }));
    };

    window.deleteItem = function (btn) {
        btn.closest('tr').remove();
    };

    window.closeReview = function () {
        document.getElementById('reviewOverlay').style.display = 'none';
    };

    // ── Collect edited data and submit ────────────────────────────────────────
    window.submitImport = function () {
        var sections = [];
        document.querySelectorAll('#reviewBody > div[data-si]').forEach(function (wrap) {
            var si       = wrap.getAttribute('data-si');
            var catInput = wrap.querySelector('input[data-field="category"]');
            var category = catInput ? catInput.value.trim() : '';
            var items    = [];

            wrap.querySelectorAll('tbody tr').forEach(function (tr) {
                var nameEl  = tr.querySelector('input[data-field="name"]');
                var priceEl = tr.querySelector('input[data-field="price"]');
                var name    = nameEl  ? nameEl.value.trim()  : '';
                var price   = priceEl ? parseFloat(priceEl.value) : 0;
                if (name && price > 0) items.push({ name: name, price: price });
            });

            if (category && items.length) sections.push({ category: category, items: items });
        });

        if (!sections.length) {
            alert('No valid items to import. Add at least one item with a name and price.');
            return;
        }

        document.getElementById('importSections').value = JSON.stringify(sections);
        document.getElementById('importBtn').disabled = true;
        document.getElementById('importBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Importing...';
        document.getElementById('importForm').submit();
    };

    function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Auto-open review modal if Excel sections were flashed from server
    @if(session('excel_sections'))
    openReview({!! json_encode(session('excel_sections')) !!});
    @endif

    // Excel form spinner
    document.getElementById('excelForm').addEventListener('submit', function () {
        var btn = document.getElementById('excelBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';
    });


}());
</script>
@endpush
