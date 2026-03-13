@extends('layouts.waiter')

@section('title', 'Create Order')

@section('content')
<div id="tableSelection" class="mb-4">
    <h1 class="text-xl font-bold mb-4 text-gray-800">Select Table</h1>
    
    <div class="grid grid-cols-3 gap-3">
        @foreach($tables as $table)
            <div onclick="selectTable({{ $table->id }}, '{{ $table->table_number }}', {{ $table->is_occupied ? 'false' : 'true' }})" 
                 class="relative aspect-square rounded-lg shadow-md flex flex-col items-center justify-center cursor-pointer transition-all
                        {{ $table->is_occupied ? 'bg-red-100 border-2 border-red-300' : 'bg-green-100 border-2 border-green-300 hover:shadow-lg hover:scale-105' }}">
                <div class="text-3xl font-bold text-gray-700">{{ $table->table_number }}</div>
                <div class="text-xs mt-1 px-2 py-1 rounded-full {{ $table->is_occupied ? 'bg-red-500' : 'bg-green-500' }} text-white font-semibold">
                    {{ $table->is_occupied ? 'Occupied' : 'Available' }}
                </div>
                <div class="text-xs text-gray-600 mt-1">{{ $table->capacity }} seats</div>
            </div>
        @endforeach
    </div>
</div>

<div id="menuSelection" class="hidden">
    <div class="sticky top-0 bg-gray-100 pb-3 z-10">
        <div class="flex items-center justify-between mb-3">
            <button onclick="backToTables()" class="text-blue-600 font-semibold flex items-center">
                <span class="text-xl mr-1">←</span> Back
            </button>
            <h1 class="text-lg font-bold text-gray-800">Table <span id="selectedTableName"></span></h1>
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
            <button onclick="filterCategory('all')" class="category-btn px-4 py-2 rounded-full bg-blue-600 text-white text-sm whitespace-nowrap">All</button>
            @php
                $categories = $menuItems->pluck('category')->unique();
            @endphp
            @foreach($categories as $category)
                <button onclick="filterCategory('{{ $category }}')" class="category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm whitespace-nowrap">{{ $category }}</button>
            @endforeach
        </div>
    </div>
    
    <div id="menuItems" class="grid grid-cols-2 gap-3 pb-24">
        @foreach($menuItems as $item)
            <div class="menu-item bg-white rounded-lg shadow-md overflow-hidden" data-category="{{ $item->category }}" data-name="{{ strtolower($item->name) }}">
                @if($item->image)
                    <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="w-full h-32 object-cover">
                @else
                    <div class="w-full h-32 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                        <span class="text-4xl">🍽️</span>
                    </div>
                @endif
                <div class="p-3">
                    <h3 class="font-semibold text-sm text-gray-800 mb-1">{{ $item->name }}</h3>
                    <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ $item->description }}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-bold text-blue-600">₹{{ $item->price }}</span>
                        <button onclick="addToCart({{ $item->id }}, '{{ $item->name }}', {{ $item->price }})" 
                                class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold hover:bg-blue-700">
                            + Add
                        </button>
                    </div>
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
let cart = [];

function selectTable(tableId, tableName, isAvailable) {
    if (!isAvailable) {
        alert('This table is occupied. Please select an available table.');
        return;
    }
    
    selectedTableId = tableId;
    selectedTableName = tableName;
    document.getElementById('selectedTableName').textContent = tableName;
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
    document.getElementById('tableSelection').classList.remove('hidden');
    document.getElementById('menuSelection').classList.add('hidden');
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
            quantity: 1
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
            <div class="flex items-center justify-between mb-4 pb-4 border-b">
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
    
    const tableInput = document.createElement('input');
    tableInput.type = 'hidden';
    tableInput.name = 'table_id';
    tableInput.value = selectedTableId;
    form.appendChild(tableInput);
    
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
    });
    
    document.body.appendChild(form);
    form.submit();
}

function filterCategory(category) {
    const items = document.querySelectorAll('.menu-item');
    const buttons = document.querySelectorAll('.category-btn');
    
    buttons.forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    
    event.target.classList.remove('bg-gray-200', 'text-gray-700');
    event.target.classList.add('bg-blue-600', 'text-white');
    
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
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
