@extends('layouts.admin')

@section('title', 'Create Order')

@section('content')
<div style="max-width:680px;margin:0 auto;">

{{-- Branch selector --}}
@if($branches->count() > 0)
<div style="margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
    <label style="font-size:13px;font-weight:600;color:var(--gray-600);">Branch:</label>
    <select onchange="window.location.href='{{ route('admin.orders.create') }}?branch_id='+this.value"
            style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;min-width:180px;">
        <option value="">All / No Branch</option>
        @foreach($branches as $branch)
            <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
        @endforeach
    </select>
    @if($branchId)
        <span style="font-size:12px;color:#16a34a;font-weight:600;"><i class="fas fa-check-circle me-1"></i>Remembered for all orders</span>
    @endif
</div>
@endif

{{-- Step 1: Table selection --}}
<div id="tableSelection">
    <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Select Table</h2>

    <button onclick="selectParcel()"
            style="width:100%;margin-bottom:16px;display:flex;align-items:center;justify-content:center;gap:8px;background:#ea580c;color:#fff;font-weight:700;padding:14px;border-radius:10px;border:none;cursor:pointer;font-size:15px;">
        📦 Parcel Order
    </button>

    @foreach($allTables as $catName => $catTables)
    <div style="margin-bottom:20px;">
        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">{{ $catName }}</div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
            @foreach($catTables as $table)
            @php
                $occupied = $table->is_occupied;
                $activeOrder = $table->orders->first();
                $mins = $activeOrder ? (int) $activeOrder->created_at->diffInMinutes(now()) : null;
            @endphp
            <div onclick="selectTable({{ $table->id }}, '{{ $table->table_number }}', {{ $occupied ? 'false' : 'true' }})"
                 style="border-radius:10px;padding:12px 8px;border:2px solid {{ $occupied ? '#ef4444' : '#22c55e' }};
                        background:{{ $occupied ? '#fef2f2' : '#f0fdf4' }};
                        display:flex;flex-direction:column;align-items:center;justify-content:center;
                        text-align:center;gap:4px;cursor:pointer;min-height:80px;">
                <div style="font-weight:700;font-size:18px;color:#1e293b;">{{ $table->table_number }}</div>
                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;
                             background:{{ $occupied ? '#ef4444' : '#22c55e' }};color:#fff;">
                    {{ $occupied ? 'Occupied' : 'Free' }}
                </span>
                @if($occupied && $mins !== null)
                    <div style="font-size:11px;color:#ef4444;font-weight:600;">{{ $mins }}m</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- Step 2: Menu selection --}}
<div id="menuSelection" style="display:none;">
    <div style="position:sticky;top:0;background:#f9fafb;padding-bottom:12px;z-index:10;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <button onclick="backToTables()" style="background:none;border:none;color:#2563eb;font-weight:600;font-size:15px;cursor:pointer;">← Back</button>
            <span style="font-weight:700;font-size:16px;" id="selectedTableName"></span>
            <button onclick="viewCart()" style="background:none;border:none;cursor:pointer;position:relative;font-size:24px;">
                🛒<span id="cartBadge" style="display:none;position:absolute;top:-8px;right:-8px;background:#ef4444;color:#fff;font-size:11px;border-radius:50%;width:18px;height:18px;display:none;align-items:center;justify-content:center;font-weight:700;">0</span>
            </button>
        </div>
        <input type="text" id="menuSearch" placeholder="Search menu items..."
               style="width:100%;padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;"
               onkeyup="filterMenu()">
        <div style="display:flex;gap:8px;overflow-x:auto;padding:10px 0 4px;scrollbar-width:none;">
            <button onclick="filterCategory('all',this)" class="cat-btn" style="padding:6px 16px;border-radius:20px;background:#2563eb;color:#fff;border:none;font-size:13px;white-space:nowrap;cursor:pointer;">All</button>
            @foreach($menuCategories as $cat)
                <button onclick="filterCategory('{{ $cat->id }}',this)" class="cat-btn" style="padding:6px 16px;border-radius:20px;background:#f3f4f6;color:#374151;border:none;font-size:13px;white-space:nowrap;cursor:pointer;">{{ $cat->name }}</button>
            @endforeach
        </div>
    </div>

    <div id="menuItems" style="display:flex;flex-direction:column;gap:8px;padding-bottom:80px;">
        @foreach($menuItems as $item)
        <div class="menu-item" data-category="{{ $item->menu_category_id }}" data-name="{{ strtolower($item->name) }}"
             style="background:#fff;border-radius:10px;border:1px solid #e5e7eb;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;cursor:pointer;"
                 onclick="toggleItemRow({{ $item->id }})">
                <div>
                    <div style="font-weight:600;font-size:14px;">{{ $item->name }}</div>
                    @if($item->description)
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;">{{ $item->description }}</div>
                    @endif
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-weight:700;color:#2563eb;">₹{{ $item->price }}</span>
                    <span id="addIcon{{ $item->id }}" style="color:#2563eb;font-size:22px;font-weight:700;line-height:1;">+</span>
                </div>
            </div>
            <div id="qtyRow{{ $item->id }}" style="display:none;padding:0 16px 12px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <button onclick="changeInlineQty({{ $item->id }},-1)" style="width:32px;height:32px;border-radius:50%;background:#f3f4f6;border:none;font-size:18px;cursor:pointer;">−</button>
                    <span id="inlineQty{{ $item->id }}" style="font-weight:700;font-size:18px;min-width:28px;text-align:center;">1</span>
                    <button onclick="changeInlineQty({{ $item->id }},1)" style="width:32px;height:32px;border-radius:50%;background:#2563eb;color:#fff;border:none;font-size:18px;cursor:pointer;">+</button>
                    <button onclick="confirmAdd({{ $item->id }},'{{ addslashes($item->name) }}',{{ $item->price }})"
                            style="margin-left:8px;background:#2563eb;color:#fff;padding:6px 16px;border-radius:20px;border:none;font-size:13px;font-weight:600;cursor:pointer;">
                        Add to Order
                    </button>
                    <button onclick="toggleItemRow({{ $item->id }})" style="background:none;border:none;color:#9ca3af;font-size:20px;margin-left:auto;cursor:pointer;">×</button>
                </div>
                <textarea id="inlineNotes{{ $item->id }}" rows="2"
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:12px;"
                    placeholder="Special request (e.g. less spicy, no onion)..."></textarea>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Cart Modal --}}
<div id="cartModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;display:none;align-items:flex-end;">
    <div style="background:#fff;width:100%;border-radius:24px 24px 0 0;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;">
        <div style="padding:16px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:700;font-size:18px;">Your Order</span>
            <button onclick="closeCart()" style="font-size:24px;background:none;border:none;cursor:pointer;color:#6b7280;">×</button>
        </div>
        <div id="cartItems" style="flex:1;overflow-y:auto;padding:16px;"></div>
        <div style="padding:16px;border-top:1px solid #e5e7eb;background:#f9fafb;">
            <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Customer Notes (Optional)</label>
                <textarea id="customerNotes" rows="2"
                    style="width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;"
                    placeholder="e.g. Less spicy, No onions..."></textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <span style="font-weight:600;font-size:15px;">Total:</span>
                <span id="cartTotal" style="font-weight:700;font-size:22px;color:#2563eb;">₹0.00</span>
            </div>
            <button onclick="submitOrder()" style="width:100%;background:#2563eb;color:#fff;border:none;padding:14px;border-radius:10px;font-weight:700;font-size:16px;cursor:pointer;">
                Place Order
            </button>
        </div>
    </div>
</div>

</div>

<script>
let selectedTableId = null, selectedTableName = null, isParcel = false, cart = [];
const branchId = {{ $branchId ?? 'null' }};
const preTableId = {{ $preTableId ?? 'null' }};

function selectParcel() {
    isParcel = true; selectedTableId = null; selectedTableName = null;
    document.getElementById('selectedTableName').textContent = '📦 Parcel';
    document.getElementById('tableSelection').style.display = 'none';
    document.getElementById('menuSelection').style.display = 'block';
}

function selectTable(tableId, tableName, isAvailable) {
    if (!isAvailable) { alert('This table is occupied.'); return; }
    isParcel = false; selectedTableId = tableId; selectedTableName = tableName;
    document.getElementById('selectedTableName').textContent = 'Table ' + tableName;
    document.getElementById('tableSelection').style.display = 'none';
    document.getElementById('menuSelection').style.display = 'block';
}

function backToTables() {
    if (cart.length && !confirm('You have items in cart. Go back?')) return;
    cart = []; isParcel = false;
    document.getElementById('tableSelection').style.display = 'block';
    document.getElementById('menuSelection').style.display = 'none';
}

function toggleItemRow(itemId) {
    const row = document.getElementById('qtyRow' + itemId);
    const icon = document.getElementById('addIcon' + itemId);
    const hidden = row.style.display === 'none';
    document.querySelectorAll('[id^="qtyRow"]').forEach(r => r.style.display = 'none');
    document.querySelectorAll('[id^="addIcon"]').forEach(i => i.textContent = '+');
    if (hidden) { row.style.display = 'block'; icon.textContent = '−'; document.getElementById('inlineQty' + itemId).textContent = '1'; }
}

function changeInlineQty(itemId, d) {
    const el = document.getElementById('inlineQty' + itemId);
    let q = parseInt(el.textContent) + d; if (q < 1) q = 1; el.textContent = q;
}

function confirmAdd(itemId, itemName, itemPrice) {
    const qty = parseInt(document.getElementById('inlineQty' + itemId).textContent);
    const notes = (document.getElementById('inlineNotes' + itemId)?.value || '').trim();
    const ex = cart.find(i => i.id === itemId);
    if (ex) { ex.quantity += qty; if (notes) ex.notes = (ex.notes ? ex.notes + '; ' : '') + notes; }
    else cart.push({id: itemId, name: itemName, price: itemPrice, quantity: qty, notes});
    document.getElementById('inlineNotes' + itemId).value = '';
    updateCartBadge();
    showToast(qty + '× ' + itemName + ' added!');
    document.getElementById('qtyRow' + itemId).style.display = 'none';
    document.getElementById('addIcon' + itemId).textContent = '+';
}

function updateCartBadge() {
    const badge = document.getElementById('cartBadge');
    const total = cart.reduce((s,i) => s + i.quantity, 0);
    badge.textContent = total;
    badge.style.display = total > 0 ? 'flex' : 'none';
}

function viewCart() {
    if (!cart.length) { alert('Cart is empty!'); return; }
    renderCart();
    document.getElementById('cartModal').style.display = 'flex';
}

function closeCart() { document.getElementById('cartModal').style.display = 'none'; }

function renderCart() {
    let html = '', total = 0;
    cart.forEach((item, i) => {
        total += item.price * item.quantity;
        html += `<div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #f3f4f6;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <div><div style="font-weight:600;">${item.name}</div><div style="font-size:12px;color:#6b7280;">₹${item.price} each</div></div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <button onclick="updateQty(${i},-1)" style="width:30px;height:30px;border-radius:50%;background:#f3f4f6;border:none;font-weight:700;cursor:pointer;">-</button>
                    <span style="font-weight:700;font-size:16px;min-width:24px;text-align:center;">${item.quantity}</span>
                    <button onclick="updateQty(${i},1)" style="width:30px;height:30px;border-radius:50%;background:#2563eb;color:#fff;border:none;font-weight:700;cursor:pointer;">+</button>
                    <button onclick="removeItem(${i})" style="background:none;border:none;color:#dc2626;font-size:18px;cursor:pointer;">🗑️</button>
                </div>
            </div>
            <textarea rows="1" style="width:100%;padding:4px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;"
                placeholder="Special request..." onchange="cart[${i}].notes=this.value">${item.notes||''}</textarea>
        </div>`;
    });
    document.getElementById('cartItems').innerHTML = html || '<p style="text-align:center;color:#6b7280;">Cart is empty</p>';
    document.getElementById('cartTotal').textContent = '₹' + total.toFixed(2);
}

function updateQty(i, d) { cart[i].quantity += d; if (cart[i].quantity <= 0) cart.splice(i,1); updateCartBadge(); renderCart(); if (!cart.length) closeCart(); }
function removeItem(i) { cart.splice(i,1); updateCartBadge(); renderCart(); if (!cart.length) closeCart(); }

function submitOrder() {
    if (!cart.length) { alert('Cart is empty!'); return; }
    const form = document.createElement('form');
    form.method = 'POST'; form.action = '{{ route('admin.orders.store') }}';
    const add = (n,v) => { const i = document.createElement('input'); i.type='hidden'; i.name=n; i.value=v; form.appendChild(i); };
    add('_token', '{{ csrf_token() }}');
    add('is_parcel', isParcel ? '1' : '0');
    if (!isParcel) add('table_id', selectedTableId);
    if (branchId) add('branch_id', branchId);
    add('customer_notes', document.getElementById('customerNotes').value);
    cart.forEach((item,i) => { add(`items[${i}][menu_item_id]`,item.id); add(`items[${i}][quantity]`,item.quantity); add(`items[${i}][notes]`,item.notes||''); });
    document.body.appendChild(form); form.submit();
}

function filterCategory(cat, btn) {
    document.querySelectorAll('.cat-btn').forEach(b => { b.style.background='#f3f4f6'; b.style.color='#374151'; });
    btn.style.background='#2563eb'; btn.style.color='#fff';
    document.querySelectorAll('.menu-item').forEach(el => { el.style.display = (cat==='all'||el.dataset.category===String(cat)) ? 'block' : 'none'; });
}

function filterMenu() {
    const q = document.getElementById('menuSearch').value.toLowerCase();
    document.querySelectorAll('.menu-item').forEach(el => { el.style.display = el.dataset.name.includes(q) ? 'block' : 'none'; });
}

function showToast(msg) {
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;top:80px;left:50%;transform:translateX(-50%);background:#16a34a;color:#fff;padding:10px 20px;border-radius:8px;font-weight:600;z-index:99999;font-size:14px;';
    t.textContent = msg; document.body.appendChild(t); setTimeout(() => t.remove(), 1800);
}

// Auto-select table if pre-selected from dashboard
document.addEventListener('DOMContentLoaded', function() {
    if (preTableId) {
        const tableEl = document.querySelector(`[onclick*="selectTable(${preTableId},"]`);
        if (tableEl) tableEl.click();
    }
});
</script>
@endsection
