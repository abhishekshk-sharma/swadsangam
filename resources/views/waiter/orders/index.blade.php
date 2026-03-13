@extends('layouts.waiter')

@section('title', 'Today\'s Orders')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Today's Orders</h1>
    <p class="text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="space-y-3">
    @forelse($orders as $order)
    <div class="bg-white p-4 rounded-lg shadow">
        <div class="flex justify-between items-start mb-3">
            <div>
                <h3 class="text-lg font-bold">Order #{{ $order->id }}</h3>
                <p class="text-xs text-gray-500">Table {{ $order->table->table_number }}</p>
                <p class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
            </div>
            <span class="px-2 py-1 rounded text-xs font-semibold 
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>
        
        <div class="mb-3">
            <h4 class="font-semibold mb-1 text-xs text-gray-600">Items:</h4>
            <ul class="space-y-1">
                @foreach($order->items as $item)
                <li class="text-sm">
                    {{ $item->quantity }}x {{ $item->menuItem->name }}
                    @if($item->notes)
                        <span class="block text-xs text-orange-600 italic ml-4">→ {{ $item->notes }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        
        @if($order->customer_notes)
        <div class="mb-3 bg-yellow-50 border-l-4 border-yellow-400 p-2 rounded">
            <h4 class="font-semibold mb-1 text-xs text-yellow-800 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                </svg>
                Customer Request:
            </h4>
            <p class="text-xs text-gray-700 italic">{{ $order->customer_notes }}</p>
        </div>
        @endif
        
        <div class="border-t pt-2">
            <div class="flex justify-between items-center mb-2">
                <div class="font-bold">
                    <span>Total:</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
            
            <div class="flex gap-2">
                @if($order->payment_status !== 'paid')
                <button onclick="addItemsToOrder({{ $order->id }}, '{{ $order->table->table_number }}')" 
                        class="flex-1 bg-blue-500 text-white px-4 py-2 rounded text-sm font-semibold">
                    + Add Items
                </button>
                @endif
                
                @if($order->status === 'ready')
                <button onclick="markServed({{ $order->id }})" 
                        class="flex-1 bg-green-500 text-white px-4 py-2 rounded text-sm font-semibold">
                    Mark as Served
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center py-12 text-gray-500">
        <p class="text-lg">No orders today</p>
        <a href="/waiter/orders/create" class="text-blue-500 hover:underline mt-2 inline-block text-sm">Create your first order</a>
    </div>
    @endforelse
</div>

<!-- Add Items Modal -->
<div id="addItemsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="fixed inset-0 flex items-end">
        <div class="bg-white w-full rounded-t-3xl max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex items-center justify-between flex-shrink-0">
                <div>
                    <h2 class="text-xl font-bold">Add Items</h2>
                    <p class="text-sm text-gray-600">Order #<span id="modalOrderId"></span> - Table <span id="modalTableNumber"></span></p>
                </div>
                <button onclick="closeAddItemsModal()" class="text-gray-500 text-2xl">×</button>
            </div>
            
            <div class="p-4 border-b flex-shrink-0">
                <input type="text" id="itemSearch" placeholder="Search menu items..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3"
                       onkeyup="filterModalItems()">
                
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button onclick="filterModalCategory('all')" class="modal-category-btn px-4 py-2 rounded-full bg-blue-600 text-white text-sm whitespace-nowrap">All</button>
                    @php
                        $categories = ($menuItems ?? collect())->pluck('category')->unique();
                    @endphp
                    @foreach($categories as $category)
                        <button onclick="filterModalCategory('{{ $category }}')" class="modal-category-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm whitespace-nowrap">{{ $category }}</button>
                    @endforeach
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4" style="min-height: 0;">
                <div id="modalMenuItems" class="grid grid-cols-2 gap-3">
                    @foreach($menuItems ?? [] as $item)
                    <div class="modal-menu-item bg-white rounded-lg shadow-md border border-gray-200 flex flex-col" data-category="{{ $item->category }}" data-name="{{ strtolower($item->name) }}">
                        @if($item->image)
                            <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="w-full h-32 object-cover flex-shrink-0">
                        @else
                            <div class="w-full h-32 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-4xl">🍽️</span>
                            </div>
                        @endif
                        <div class="p-3 flex flex-col flex-grow">
                            <h3 class="font-semibold text-sm text-gray-800 mb-1">{{ $item->name }}</h3>
                            @if($item->description)
                                <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ $item->description }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-auto">
                                <span class="text-lg font-bold text-blue-600">₹{{ $item->price }}</span>
                                <button onclick="addItemToExistingOrder({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }})" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold hover:bg-blue-700 whitespace-nowrap flex-shrink-0">
                                    + Add
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="p-4 border-t bg-gray-50 flex-shrink-0">
                <div id="selectedItemsList" class="mb-3 max-h-40 overflow-y-auto hidden">
                    <!-- Selected items will appear here -->
                </div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-lg font-semibold">New Items Total:</span>
                    <span id="newItemsTotal" class="text-2xl font-bold text-blue-600">₹0.00</span>
                </div>
                <button onclick="submitAdditionalItems()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-blue-700">
                    Add to Order
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;
let additionalItems = [];

function addItemsToOrder(orderId, tableNumber) {
    currentOrderId = orderId;
    additionalItems = [];
    document.getElementById('modalOrderId').textContent = orderId;
    document.getElementById('modalTableNumber').textContent = tableNumber;
    document.getElementById('selectedItemsList').classList.add('hidden');
    document.getElementById('itemSearch').value = '';
    updateNewItemsTotal();
    
    // Reset category filter to 'All'
    const items = document.querySelectorAll('.modal-menu-item');
    items.forEach(item => {
        item.style.display = 'block';
    });
    
    const buttons = document.querySelectorAll('.modal-category-btn');
    buttons.forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    buttons[0].classList.remove('bg-gray-200', 'text-gray-700');
    buttons[0].classList.add('bg-blue-600', 'text-white');
    
    document.getElementById('addItemsModal').classList.remove('hidden');
}

function closeAddItemsModal() {
    if (additionalItems.length > 0) {
        if (!confirm('You have selected items. Are you sure you want to close?')) {
            return;
        }
    }
    document.getElementById('addItemsModal').classList.add('hidden');
    additionalItems = [];
}

function addItemToExistingOrder(itemId, itemName, itemPrice) {
    const existingItem = additionalItems.find(item => item.id === itemId);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        additionalItems.push({
            id: itemId,
            name: itemName,
            price: itemPrice,
            quantity: 1,
            notes: ''
        });
    }
    
    updateSelectedItemsList();
    updateNewItemsTotal();
    showToast('Added!');
}

function updateSelectedItemsList() {
    const listDiv = document.getElementById('selectedItemsList');
    
    if (additionalItems.length === 0) {
        listDiv.classList.add('hidden');
        return;
    }
    
    listDiv.classList.remove('hidden');
    let html = '<div class="space-y-2">';
    
    additionalItems.forEach((item, index) => {
        html += `
            <div class="bg-white p-3 rounded-lg shadow-sm border-b">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex-1">
                        <h3 class="font-semibold text-sm text-gray-800">${item.name}</h3>
                        <p class="text-xs text-gray-600">₹${item.price} each</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateAdditionalQuantity(${index}, -1)" class="w-7 h-7 bg-gray-200 rounded-full font-bold text-sm">-</button>
                        <span class="font-bold text-base w-7 text-center">${item.quantity}</span>
                        <button onclick="updateAdditionalQuantity(${index}, 1)" class="w-7 h-7 bg-blue-600 text-white rounded-full font-bold text-sm">+</button>
                        <button onclick="removeAdditionalItem(${index})" class="ml-1 text-red-500 text-lg">🗑️</button>
                    </div>
                </div>
                <textarea id="additionalItemNotes${index}" rows="1" 
                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs" 
                    placeholder="Special request for this item..." 
                    onchange="updateAdditionalItemNotes(${index}, this.value)">${item.notes || ''}</textarea>
            </div>
        `;
    });
    
    html += '</div>';
    listDiv.innerHTML = html;
}

function updateAdditionalQuantity(index, change) {
    additionalItems[index].quantity += change;
    
    if (additionalItems[index].quantity <= 0) {
        additionalItems.splice(index, 1);
    }
    
    updateSelectedItemsList();
    updateNewItemsTotal();
}

function removeAdditionalItem(index) {
    additionalItems.splice(index, 1);
    updateSelectedItemsList();
    updateNewItemsTotal();
}

function updateNewItemsTotal() {
    const total = additionalItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('newItemsTotal').textContent = '₹' + total.toFixed(2);
}

function submitAdditionalItems() {
    if (additionalItems.length === 0) {
        alert('Please select at least one item!');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/waiter/orders/${currentOrderId}/add-items`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    additionalItems.forEach((item, index) => {
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

function filterModalItems() {
    const search = document.getElementById('itemSearch').value.toLowerCase();
    const items = document.querySelectorAll('.modal-menu-item');
    
    items.forEach(item => {
        const name = item.dataset.name;
        if (name.includes(search)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function filterModalCategory(category) {
    const items = document.querySelectorAll('.modal-menu-item');
    const buttons = document.querySelectorAll('.modal-category-btn');
    
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

function markServed(orderId) {
    if (!confirm('Mark this order as served?')) return;
    
    fetch(`/waiter/orders/${orderId}/serve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(() => location.reload());
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 1500);
}

function updateAdditionalItemNotes(index, notes) {
    additionalItems[index].notes = notes;
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
