{{-- Quick Add Category Modal --}}
<div id="quickCategoryModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:10px;padding:28px;width:100%;max-width:400px;box-shadow:0 8px 32px rgba(0,0,0,0.2);">
        <h5 style="font-weight:700;color:#232f3e;margin-bottom:20px;">
            <i class="fas fa-plus-circle" style="color:#ff9900;"></i> Add New Category
        </h5>
        <div class="mb-3">
            <label class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" id="quickCategoryName" class="form-control" placeholder="Enter New Category">
            <div id="quickCategoryError" class="text-danger small mt-1" style="display:none;"></div>
        </div>
        <div class="mb-4">
            <label class="form-label">Description <span class="text-muted">(optional)</span></label>
            <input type="text" id="quickCategoryDesc" class="form-control" placeholder="Short description...">
        </div>
        <div class="d-flex gap-2">
            <button onclick="saveQuickCategory()" class="btn-primary" style="flex:1;">
                <i class="fas fa-save me-1"></i> Save & Select
            </button>
            <button onclick="closeQuickCategoryModal()" class="btn-secondary" style="flex:1;">Cancel</button>
        </div>
    </div>
</div>

<script>
let _quickCatSelectId = null;
let _quickCatUrl = null;

function openQuickCategoryModal(selectId, url) {
    _quickCatSelectId = selectId;
    _quickCatUrl = url;
    document.getElementById('quickCategoryName').value = '';
    document.getElementById('quickCategoryDesc').value = '';
    document.getElementById('quickCategoryError').style.display = 'none';
    const modal = document.getElementById('quickCategoryModal');
    modal.style.display = 'flex';
    setTimeout(() => document.getElementById('quickCategoryName').focus(), 100);
}

function closeQuickCategoryModal() {
    document.getElementById('quickCategoryModal').style.display = 'none';
}

function saveQuickCategory() {
    const name = document.getElementById('quickCategoryName').value.trim();
    const desc = document.getElementById('quickCategoryDesc').value.trim();
    const errEl = document.getElementById('quickCategoryError');

    if (!name) {
        errEl.textContent = 'Category name is required.';
        errEl.style.display = 'block';
        return;
    }
    errEl.style.display = 'none';

    fetch(_quickCatUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                         || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name, description: desc })
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            const select = document.getElementById(_quickCatSelectId);
            // Insert new option before the "Add New" option
            const newOpt = new Option(data.name, data.id);
            const newOptEl = select.querySelector('option[value="__new__"]');
            select.insertBefore(newOpt, newOptEl);
            select.value = data.id;
            closeQuickCategoryModal();
        } else {
            errEl.textContent = data.message || 'Failed to create category.';
            errEl.style.display = 'block';
        }
    })
    .catch(() => {
        errEl.textContent = 'Something went wrong. Please try again.';
        errEl.style.display = 'block';
    });
}

// Close on backdrop click
document.getElementById('quickCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeQuickCategoryModal();
});

// Save on Enter key
document.getElementById('quickCategoryName').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); saveQuickCategory(); }
});
</script>
