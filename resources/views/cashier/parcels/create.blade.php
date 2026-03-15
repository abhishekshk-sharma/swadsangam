@extends('layouts.cashier')

@section('title', 'New Parcel Order')

@section('content')
<div class="flex items-center gap-3 mb-4">
    <a href="{{ route('cashier.parcels.index') }}" class="text-blue-600 font-semibold flex items-center">
        <span class="text-xl mr-1">←</span> Back
    </a>
    <h1 class="text-xl font-bold">New Parcel Order</h1>
</div>

<div class="sticky top-0 bg-gray-100 pb-3 z-10">
    <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-orange-600 font-semibold">📦 Parcel</span>
        <button onclick="viewCart()" class="relative">
            <span class="text-2xl">🛒</span>
            <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold hidden">0</span>
        </button>
    </div>
    <input type="text" id="menuSearch" placeholder="Search menu items..."
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
           onkeyup="filterMenu()">
</div>

<div class="mb-4">
    <div class="flex gap-2 overflow-x-auto pb-2">
        <button onclick="filterCategory('all', this)" class="category-btn px-4 py-2 rounded-full bg-blue-600 text-white text-sm whitespace-nowrap">All</button>
        @foreach($menuCategories as $cat)
            <button onclick="filterCategory('{{ $cat->id }}', this)" class="category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm whitespace-nowrap">{{ $cat->name }}</button>
        @endforeach
    </div>
</div>

<div id="menuItems" class="flex flex-col gap-2 pb-24">
    @foreach($menuItems as $item)
        <div class="menu-item bg-white rounded-lg shadow-sm border border-gray-200"
             data-category="{{ $item->menu_category_id }}" data-name="{{ strtolower($item->name) }}"
             data-id="{{ $item->id }}">
            <div class="flex items-center justify-between px-4 py-3 cursor-pointer"
                 onclick="toggleItemRow({{ $item->id }})">
                <div>
                    <div class="font-semibold text-sm text-gray-800">{{ $item->name }}</div>
                    @if($item->description)
                        <div class="text-xs text-gray-400 mt-0.5">{{ $item->description }}</div>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-bold text-blue-600">₹{{ $item->price }}</span>
                    <span id="addIcon{{ $item->id }}" class="text-blue-600 text-xl font-bold leading-none">+</span>
                </div>
            </div>
            <div id="qtyRow{{ $item->id }}" class="hidden px-4 pb-3">
                <div class="flex items-center gap-3 mb-2">
                    <button onclick="changeInlineQty({{ $item->id }}, -1)" class="w-8 h-8 bg-gray-200 rounded-full font-bold text-lg">−</button>
                    <span id="inlineQty{{ $item->id }}" class="font-bold text-lg w-8 text-center">1</span>
                    <button onclick="changeInlineQty({{ $item->id }}, 1)" class="w-8 h-8 bg-blue-600 text-white rounded-full font-bold text-lg">+</button>
                    <button onclick="confirmAdd({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }})"
                            class="ml-2 bg-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-semibold">
                        Add to Order
                    </button>
                    <button onclick="toggleItemRow({{ $item->id }})" class="text-gray-400 text-xl ml-auto">×</button>
                </div>
                <textarea id="inlineNotes{{ $item->id }}" rows="2"
                    class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Special request..."></textarea>
            </div>
        </div>
    @endforeach
</div>

<!-- Cart Modal -->
<div id="cartModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end">
    <div class="bg-white w-full rounded-t-3xl max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-xl font-bold">📦 Parcel Order</h2>
            <button onclick="closeCart()" class="text-gray-500 text-2xl">×</button>
        </div>
        <div id="cartItems" class="flex-1 overflow-y-auto p-4"></div>
        <div class="p-4 border-t bg-gray-50">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Customer Notes (Optional)</label>
                <textarea id="customerNotes" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    placeholder="e.g., Less spicy, No onions..."></textarea>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-semibold">Total:</span>
                <span id="cartTotal" class="text-2xl font-bold text-blue-600">₹0.00</span>
            </div>
            <button onclick="submitOrder()" class="w-full bg-orange-500 text-white py-3 rounded-lg font-semibold text-lg hover:bg-orange-600">
                Place Parcel Order
            </button>
        </div>
    </div>
</div>

<script>
let cart = [];

function toggleItemRow(itemId) {
    const row = document.getElementById('qtyRow' + itemId);
    const icon = document.getElementById('addIcon' + itemId);
    const isHidden = row.classList.contains('hidden');
    document.querySelectorAll('[id^="qtyRow"]').forEach(r => r.classList.add('hidden'));
    document.querySelectorAll('[id^="addIcon"]').forEach(i => { i.textContent = '+'; });
    if (isHidden) {
        row.classList.remove('hidden');
        icon.textContent = '−';
        document.getElementById('inlineQty' + itemId).textContent = '1';
    }
}

function changeInlineQty(itemId, change) {
    const el = document.getElementById('inlineQty' + itemId);
    let qty = parseInt(el.textContent) + change;
    if (qty < 1) qty = 1;
    el.textContent = qty;
}

function confirmAdd(itemId, itemName, itemPrice) {
    const qty = parseInt(document.getElementById('inlineQty' + itemId).textContent);
    const notesEl = document.getElementById('inlineNotes' + itemId);
    const notes = notesEl ? notesEl.value.trim() : '';
    const existing = cart.find(i => i.id === itemId);
    if (existing) {
        existing.quantity += qty;
        if (notes) existing.notes = (existing.notes ? existing.notes + '; ' : '') + notes;
    } else {
        cart.push({ id: itemId, name: itemName, price: itemPrice, quantity: qty, notes: notes });
    }
    if (notesEl) notesEl.value = '';
    updateCartBadge();
    showToast(qty + '× ' + itemName + ' added!');
    document.getElementById('qtyRow' + itemId).classList.add('hidden');
    document.getElementById('addIcon' + itemId).textContent = '+';
}

function updateCartBadge() {
    const badge = document.getElementById('cartBadge');
    const total = cart.reduce((s, i) => s + i.quantity, 0);
    badge.textContent = total;
    total > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');
}

function viewCart() {
    if (cart.length === 0) { alert('Your cart is empty!'); return; }
    renderCart();
    document.getElementById('cartModal').classList.remove('hidden');
}

function closeCart() { document.getElementById('cartModal').classList.add('hidden'); }

function renderCart() {
    const div = document.getElementById('cartItems');
    let html = '', total = 0;
    cart.forEach((item, index) => {
        total += item.price * item.quantity;
        html += `<div class="mb-4 pb-4 border-b">
            <div class="flex items-center justify-between mb-2">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800">${item.name}</h3>
                    <p class="text-sm text-gray-600">₹${item.price} each</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="updateQuantity(${index}, -1)" class="w-8 h-8 bg-gray-200 rounded-full font-bold">-</button>
                    <span class="font-bold text-lg w-8 text-center">${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)" class="w-8 h-8 bg-blue-600 text-white rounded-full font-bold">+</button>
                    <button onclick="removeFromCart(${index})" class="ml-2 text-red-500 text-xl">🗑️</button>
                </div>
            </div>
            <textarea rows="1" class="w-full px-2 py-1 border border-gray-300 rounded text-xs"
                placeholder="Special request..." onchange="updateItemNotes(${index}, this.value)">${item.notes || ''}</textarea>
        </div>`;
    });
    div.innerHTML = html || '<p class="text-center text-gray-500">Cart is empty</p>';
    document.getElementById('cartTotal').textContent = '₹' + total.toFixed(2);
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) cart.splice(index, 1);
    updateCartBadge(); renderCart();
    if (cart.length === 0) closeCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartBadge(); renderCart();
    if (cart.length === 0) closeCart();
}

function updateItemNotes(index, notes) { cart[index].notes = notes; }

function submitOrder() {
    if (cart.length === 0) { alert('Cart is empty!'); return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("cashier.parcels.store") }}';
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    const notes = document.createElement('input');
    notes.type = 'hidden'; notes.name = 'customer_notes';
    notes.value = document.getElementById('customerNotes').value;
    form.appendChild(notes);
    cart.forEach((item, index) => {
        [['menu_item_id', item.id], ['quantity', item.quantity], ['notes', item.notes || '']].forEach(([key, val]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = `items[${index}][${key}]`; inp.value = val;
            form.appendChild(inp);
        });
    });
    document.body.appendChild(form);
    form.submit();
}

function filterCategory(category, btn) {
    document.querySelectorAll('.category-btn').forEach(b => {
        b.classList.remove('bg-blue-600', 'text-white');
        b.classList.add('bg-gray-200', 'text-gray-700');
    });
    btn.classList.remove('bg-gray-200', 'text-gray-700');
    btn.classList.add('bg-blue-600', 'text-white');
    document.querySelectorAll('.menu-item').forEach(item => {
        item.style.display = (category === 'all' || item.dataset.category === String(category)) ? 'block' : 'none';
    });
}

function filterMenu() {
    const search = document.getElementById('menuSearch').value.toLowerCase();
    document.querySelectorAll('.menu-item').forEach(item => {
        item.style.display = item.dataset.name.includes(search) ? 'block' : 'none';
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}
</script>
@endsection
