@extends('layouts.waiter')
@section('title', 'Settings')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-xl font-bold text-gray-800">⚙️ Settings</h1>
</div>

{{-- Category Order Card --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between"
         style="background:linear-gradient(135deg,#1e3a5f,#2a4f7c);">
        <div>
            <div class="text-white font-bold text-base">Menu Category Order</div>
            <div class="text-indigo-200 text-xs mt-0.5">Drag rows or use the number picker to set your preferred order</div>
        </div>
        <button onclick="saveOrder()" id="saveOrderBtn"
            class="hidden bg-green-500 hover:bg-green-600 text-white text-sm font-bold px-4 py-2 rounded-lg transition-all">
            💾 Save Order
        </button>
    </div>

    @if($categories->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <div class="text-5xl mb-3">🍽️</div>
            <div class="font-semibold">No categories found</div>
            <div class="text-sm mt-1">Categories will appear here once menu items are added.</div>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-indigo-50 text-indigo-700 text-xs font-bold uppercase tracking-wide">
                    <th class="px-4 py-3 text-left w-20">Order</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-center w-16">Items</th>
                    <th class="px-4 py-3 text-center w-10"></th>
                </tr>
            </thead>
            <tbody id="sortableBody">
                @foreach($categories as $i => $cat)
                <tr data-id="{{ $cat->id }}"
                    class="border-b border-gray-100 hover:bg-gray-50 transition-colors cursor-grab active:cursor-grabbing"
                    draggable="true"
                    ondragstart="dragStart(event)"
                    ondragover="dragOver(event)"
                    ondrop="dropRow(event)"
                    ondragend="dragEnd(event)">
                    <td class="px-4 py-3">
                        <select onchange="moveRowTo(this)" data-id="{{ $cat->id }}"
                            class="border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-gray-50 px-2 py-1 w-16 cursor-pointer">
                            @foreach($categories as $j => $c)
                                <option value="{{ $j + 1 }}" {{ $c->id === $cat->id ? 'selected' : '' }}>{{ $j + 1 }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
                                {{ substr($cat->name, 0, 1) }}
                            </span>
                            <div>
                                <div class="font-semibold text-gray-800">{{ $cat->name }}</div>
                                @if($cat->description)
                                    <div class="text-xs text-gray-400">{{ $cat->description }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">
                            {{ $cat->menuItems()->where('is_available', true)->count() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400 text-lg select-none">⠿</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">
            <span class="font-semibold text-gray-500">Note:</span> This order only affects your view. Admin order is unchanged.
        </p>
        <button onclick="resetOrder()" class="text-xs text-red-400 hover:text-red-600 font-semibold transition-colors">
            ↺ Reset to default
        </button>
    </div>
    @endif
</div>

<script>
const REORDER_URL = '{{ route('waiter.settings.reorder') }}';
const RESET_URL   = '{{ route('waiter.preferences.category') }}';
const CSRF        = '{{ csrf_token() }}';

// ── Drag & Drop ──────────────────────────────────────────────────────────────
let dragSrc = null;

function dragStart(e) {
    dragSrc = e.currentTarget;
    e.dataTransfer.effectAllowed = 'move';
    dragSrc.style.opacity = '0.5';
}

function dragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const target = e.currentTarget;
    if (target !== dragSrc) {
        target.style.background = '#e0e7ff';
    }
}

function dropRow(e) {
    e.preventDefault();
    const target = e.currentTarget;
    target.style.background = '';
    if (dragSrc && target !== dragSrc) {
        const tbody = document.getElementById('sortableBody');
        const rows  = Array.from(tbody.querySelectorAll('tr[data-id]'));
        const srcIdx = rows.indexOf(dragSrc);
        const tgtIdx = rows.indexOf(target);
        if (srcIdx < tgtIdx) {
            tbody.insertBefore(dragSrc, target.nextSibling);
        } else {
            tbody.insertBefore(dragSrc, target);
        }
        updateDropdowns();
        markDirty();
    }
}

function dragEnd(e) {
    e.currentTarget.style.opacity = '';
    document.querySelectorAll('#sortableBody tr').forEach(r => r.style.background = '');
}

// ── Dropdown reorder ─────────────────────────────────────────────────────────
function moveRowTo(select) {
    const newPos = parseInt(select.value) - 1;
    const row    = select.closest('tr[data-id]');
    const tbody  = document.getElementById('sortableBody');
    const rows   = Array.from(tbody.querySelectorAll('tr[data-id]'));
    const curPos = rows.indexOf(row);
    if (curPos === newPos) return;

    rows.splice(curPos, 1);
    rows.splice(newPos, 0, row);
    rows.forEach(r => tbody.appendChild(r));

    updateDropdowns();
    markDirty();
}

function updateDropdowns() {
    const rows = Array.from(document.querySelectorAll('#sortableBody tr[data-id]'));
    rows.forEach((row, idx) => {
        const sel = row.querySelector('select[data-id]');
        if (sel) sel.value = idx + 1;
    });
}

function markDirty() {
    document.getElementById('saveOrderBtn').classList.remove('hidden');
}

// ── Save ─────────────────────────────────────────────────────────────────────
function saveOrder() {
    const ids = Array.from(document.querySelectorAll('#sortableBody tr[data-id]')).map(r => r.dataset.id);
    const btn = document.getElementById('saveOrderBtn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    fetch(REORDER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ ids })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            btn.textContent = '✓ Saved!';
            btn.classList.remove('bg-green-500', 'hover:bg-green-600');
            btn.classList.add('bg-gray-400');
            setTimeout(() => {
                btn.textContent = '💾 Save Order';
                btn.classList.add('hidden', 'bg-green-500', 'hover:bg-green-600');
                btn.classList.remove('bg-gray-400');
                btn.disabled = false;
            }, 1500);
        }
    });
}

// ── Reset to admin default ────────────────────────────────────────────────────
function resetOrder() {
    if (!confirm('Reset to admin default order?')) return;

    // Clear waiter sort orders by posting empty category preference
    const body = new FormData();
    body.append('_token', CSRF);
    body.append('menu_category_id', '');

    // Delete all waiter sort rows via reorder with empty ids
    fetch(REORDER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ ids: [], reset: true })
    }).then(() => location.reload());
}
</script>
@endsection
