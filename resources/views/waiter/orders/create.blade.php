@extends('layouts.waiter')

@section('title', 'Create Order')

@section('content')
<div id="tableSelection" class="mb-4">
    <h1 class="text-xl font-bold mb-4 text-gray-800">Select Table</h1>

    <button onclick="selectParcel()"
            class="w-full mb-4 flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg shadow transition-all">
        <span class="text-xl">📦</span> Parcel Order
    </button>

    @foreach($allTables as $catName => $catTables)
        <div class="mb-5">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">{{ $catName }}</div>
            <div class="grid grid-cols-3 gap-3">
                @foreach($catTables as $table)
                    <div onclick="selectTable({{ $table->id }}, '{{ $table->table_number }}', {{ $table->is_occupied ? 'false' : 'true' }})"
                         class="relative aspect-square rounded-lg shadow-md flex flex-col items-center justify-center cursor-pointer transition-all
                                {{ $table->is_occupied ? 'bg-red-100 border-2 border-red-300' : 'bg-green-100 border-2 border-green-300 hover:shadow-lg hover:scale-105' }}">
                        <div class="text-3xl font-bold text-gray-700">{{ $table->table_number }}</div>
                        <div class="text-xs mt-1 px-2 py-1 rounded-full {{ $table->is_occupied ? 'bg-red-500' : 'bg-green-500' }} text-white font-semibold">
                            {{ $table->is_occupied ? 'Occupied' : 'Free' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div id="menuSelection" class="hidden">
    <div class="sticky top-0 bg-gray-100 pb-3 z-10">
        <div class="flex items-center justify-between mb-3">
            <button onclick="backToTables()" class="text-blue-600 font-semibold flex items-center">
                <span class="text-xl mr-1">←</span> Back
            </button>
            <h1 class="text-lg font-bold text-gray-800"><span id="selectedTableName"></span></h1>
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
                        placeholder="Special request (e.g. less spicy, no onion)..."></textarea>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Cart Modal -->
<div id="cartModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end">
    <div class="bg-white w-full rounded-t-3xl max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-xl font-bold">Your Order</h2>
            <button onclick="closeCart()" class="text-gray-500 text-2xl">×</button>
        </div>
        
        <div id="cartItems" class="flex-1 overflow-y-auto p-4">
            <!-- Cart items will be inserted here -->
        </div>
        
        <div class="p-4 border-t bg-gray-50">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Customer Request/Notes (Optional)</label>
                <textarea id="customerNotes" rows="2" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" 
                    placeholder="e.g., Less spicy, No onions, Extra sauce..."></textarea>
            </div>
            <div class="flex justify-between items-center mb-4">
                <span class="text-lg font-semibold">Total:</span>
                <span id="cartTotal" class="text-2xl font-bold text-blue-600">₹0.00</span>
            </div>
            <button onclick="submitOrder()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-blue-700">
                Place Order
            </button>
        </div>
    </div>
</div>

<script>
let selectedTableId = null;
let selectedTableName = null;
let isParcel = false;
let cart = [];

function selectParcel() {
    isParcel = true;
    selectedTableId = null;
    selectedTableName = null;
    document.getElementById('selectedTableName').textContent = '📦 Parcel';
    document.getElementById('tableSelection').classList.add('hidden');
    document.getElementById('menuSelection').classList.remove('hidden');
}

function selectTable(tableId, tableName, isAvailable) {
    if (!isAvailable) {
        alert('This table is occupied. Please select an available table.');
        return;
    }
    isParcel = false;
    selectedTableId = tableId;
    selectedTableName = tableName;
    document.getElementById('selectedTableName').textContent = 'Table ' + tableName;
    document.getElementById('tableSelection').classList.add('hidden');
    document.getElementById('menuSelection').classList.remove('hidden');
}

function backToTables() {
    if (cart.length > 0) {
        if (!confirm('You have items in cart. Are you sure you want to go back?')) {
            return;
        }
        cart = [];
    }
    isParcel = false;
    document.getElementById('tableSelection').classList.remove('hidden');
    document.getElementById('menuSelection').classList.add('hidden');
}

function toggleItemRow(itemId) {
    const row = document.getElementById('qtyRow' + itemId);
    const icon = document.getElementById('addIcon' + itemId);
    const isHidden = row.classList.contains('hidden');

    // Close all others
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
    // Close the row
    document.getElementById('qtyRow' + itemId).classList.add('hidden');
    document.getElementById('addIcon' + itemId).textContent = '+';
}

function addToCart(itemId, itemName, itemPrice) {
    const existingItem = cart.find(item => item.id === itemId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: itemId,
            name: itemName,
            price: itemPrice,
            quantity: 1,
            notes: ''
        });
    }
    
    updateCartBadge();
    showToast('Added to cart!');
}

function updateCartBadge() {
    const badge = document.getElementById('cartBadge');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalItems > 0) {
        badge.textContent = totalItems;
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function viewCart() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    renderCart();
    document.getElementById('cartModal').classList.remove('hidden');
}

function closeCart() {
    document.getElementById('cartModal').classList.add('hidden');
}

function renderCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    let html = '';
    let total = 0;
    
    cart.forEach((item, index) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        html += `
            <div class="mb-4 pb-4 border-b">
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
                <textarea id="itemNotes${index}" rows="1" 
                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs" 
                    placeholder="Special request for this item..." 
                    onchange="updateItemNotes(${index}, this.value)">${item.notes || ''}</textarea>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html || '<p class="text-center text-gray-500">Your cart is empty</p>';
    document.getElementById('cartTotal').textContent = '₹' + total.toFixed(2);
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    
    updateCartBadge();
    renderCart();
    
    if (cart.length === 0) {
        closeCart();
    }
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartBadge();
    renderCart();
    
    if (cart.length === 0) {
        closeCart();
    }
}

function submitOrder() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/waiter/orders';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    const parcelInput = document.createElement('input');
    parcelInput.type = 'hidden';
    parcelInput.name = 'is_parcel';
    parcelInput.value = isParcel ? '1' : '0';
    form.appendChild(parcelInput);

    if (!isParcel) {
        const tableInput = document.createElement('input');
        tableInput.type = 'hidden';
        tableInput.name = 'table_id';
        tableInput.value = selectedTableId;
        form.appendChild(tableInput);
    }

    const notesInput = document.createElement('input');
    notesInput.type = 'hidden';
    notesInput.name = 'customer_notes';
    notesInput.value = document.getElementById('customerNotes').value;
    form.appendChild(notesInput);

    cart.forEach((item, index) => {
        const itemIdInput = document.createElement('input');
        itemIdInput.type = 'hidden';
        itemIdInput.name = `items[${index}][menu_item_id]`;
        itemIdInput.value = item.id;
        form.appendChild(itemIdInput);

        const quantityInput = document.createElement('input');
        quantityInput.type = 'hidden';
        quantityInput.name = `items[${index}][quantity]`;
        quantityInput.value = item.quantity;
        form.appendChild(quantityInput);

        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = `items[${index}][notes]`;
        notesInput.value = item.notes || '';
        form.appendChild(notesInput);
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
    const items = document.querySelectorAll('.menu-item');
    
    items.forEach(item => {
        const name = item.dataset.name;
        if (name.includes(search)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 2000);
}

function updateItemNotes(index, notes) {
    cart[index].notes = notes;
}
</script>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
