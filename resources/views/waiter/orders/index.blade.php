@extends('layouts.waiter')

@section('title', 'Today\'s Orders')

@section('content')
<div class="mb-4 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold">Today's Orders</h1>
        <p class="text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
    </div>
    <button onclick="location.reload()" class="flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold">
        🔄 Refresh
    </button>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
        {{ session('error') }}
    </div>
@endif

<div class="space-y-3">
    @forelse($orders as $order)
    <div class="order-card bg-white p-4 rounded-lg shadow" data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}" data-table-number="{{ $order->table->table_number }}" data-created-at="{{ $order->created_at->timestamp }}">
        <div class="flex justify-between items-start mb-3">
            <div>
                <h3 class="text-lg font-bold">Order #{{ $order->id }}</h3>
                <p class="text-xs text-gray-500">Table {{ $order->table->table_number }}</p>
                <p class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="waiter-order-timer" data-timer></span>
                <span class="order-status-badge px-2 py-1 rounded text-xs font-semibold 
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'ready' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'served' ? 'bg-purple-100 text-purple-800' : '' }}
                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}"
                data-order-status-badge>
                {{ ucfirst($order->status) }}
            </span>
        </div>
        </div>
        
        <div class="mb-3">
            <h4 class="font-semibold mb-1 text-xs text-gray-600">Items:</h4>
            <ul class="space-y-1">
                @foreach($order->items as $item)
                <li class="text-sm flex flex-col gap-1" data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                    <div class="flex justify-between items-start gap-2">
                        <div class="flex-1">
                            <span class="{{ $item->status === 'cancelled' ? 'line-through text-gray-400' : '' }}" data-item-name>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                            @if($item->status === 'cancelled')
                                <span class="text-xs text-red-500 ml-1">(cancelled)</span>
                            @endif
                            @if($item->notes)
                                <span class="block text-xs text-orange-600 italic ml-4">→ {{ $item->notes }}</span>
                            @endif
                        </div>
                        @if(!in_array($order->status, ['paid', 'cancelled']))
                            <div class="waiter-item-actions" data-item-actions>
                                @if($item->status === 'pending')
                                    <button type="button" title="Edit Item"
                                            onclick="toggleEdit('edit-{{ $item->id }}')"
                                            class="waiter-action-btn waiter-btn-edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </button>
                                @endif
                                @if($item->status !== 'prepared' && $item->status !== 'cancelled')
                                    <form action="{{ route('waiter.orderItems.cancel', $item->id) }}" method="POST" style="display:flex;align-items:center;margin:0;">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="Cancel Item"
                                                class="waiter-action-btn waiter-btn-cancel">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"/>
                                                <line x1="6" y1="6" x2="18" y2="18"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                    @if($item->status === 'pending')
                    <div id="edit-{{ $item->id }}" class="hidden mt-1 bg-gray-50 rounded p-2">
                        <form action="{{ route('waiter.orderItems.update', $item->id) }}" method="POST" class="flex flex-col gap-1">
                            @csrf @method('PATCH')
                            <div class="flex gap-2 items-center">
                                <label class="text-xs text-gray-500">Qty:</label>
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                       class="w-16 border rounded px-2 py-0.5 text-sm">
                            </div>
                            <div class="flex gap-2 items-center">
                                <label class="text-xs text-gray-500">Note:</label>
                                <input type="text" name="notes" value="{{ $item->notes }}"
                                       class="flex-1 border rounded px-2 py-0.5 text-sm" placeholder="Special request...">
                            </div>
                            <button class="self-end bg-blue-600 text-white px-3 py-0.5 rounded text-xs">Save</button>
                        </form>
                    </div>
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
                    <span data-order-total>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
            
            <div class="order-actions waiter-order-actions">
                @if(!in_array($order->status, ['paid', 'cancelled', 'checkout']))
                <button type="button" onclick="addItemsToOrder({{ $order->id }}, '{{ $order->table->table_number }}')" 
                        data-add-items-btn title="Add Items"
                        class="waiter-action-btn waiter-btn-add">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </button>
                @endif
                @if($order->status === 'ready')
                <button type="button" onclick="markServed({{ $order->id }})" 
                        data-serve-btn title="Mark as Served"
                        class="waiter-action-btn waiter-btn-serve">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </button>
                @endif
                @if(!in_array($order->status, ['paid', 'cancelled', 'served', 'checkout']) && $order->items->where('status', 'prepared')->count() === 0)
                <form action="{{ route('waiter.orders.cancel', $order->id) }}" method="POST"
                      onsubmit="return confirm('Cancel entire order #{{ $order->id }}?')"
                      style="display:flex;align-items:center;margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" title="Cancel Order"
                            class="waiter-action-btn waiter-btn-cancel-order">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                    </button>
                </form>
                @endif
            </div>
            @if($order->status === 'served')
            <div class="checkout-section">
                <button type="button" onclick="checkoutOrder({{ $order->id }})" class="checkout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:6px;">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Checkout Table
                </button>
                <p class="checkout-hint">Customer is done. Free the table now — cashier will collect payment separately.</p>
            </div>
            @endif
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

function toggleEdit(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}

function addItemsToOrder(orderId, tableNumber) {
    const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
    const status = card ? card.dataset.orderStatus : '';
    if (status === 'paid' || status === 'cancelled') {
        alert('Cannot add items to this order.');
        return;
    }

    currentOrderId = orderId;
    additionalItems = [];
    document.getElementById('modalOrderId').textContent = orderId;
    document.getElementById('modalTableNumber').textContent = tableNumber;
    document.getElementById('selectedItemsList').classList.add('hidden');
    document.getElementById('itemSearch').value = '';
    updateNewItemsTotal();
    
    // Reset all menu items visible
    document.querySelectorAll('.modal-menu-item').forEach(item => {
        item.style.display = 'block';
    });
    
    // Reset category filter to 'All'
    const buttons = document.querySelectorAll('.modal-category-btn');
    buttons.forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-gray-200', 'text-gray-700');
    });
    if (buttons.length > 0) {
        buttons[0].classList.remove('bg-gray-200', 'text-gray-700');
        buttons[0].classList.add('bg-blue-600', 'text-white');
    }
    
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

function checkoutOrder(orderId) {
    if (!confirm('Checkout this table?\n\nThis will free the table immediately. The cashier will collect payment separately.')) return;
    fetch(`/waiter/orders/${orderId}/checkout`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
            if (card) {
                card.style.transition = 'opacity 0.4s, transform 0.4s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 400);
            }
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
    }, 1500);
}

function updateAdditionalItemNotes(index, notes) {
    additionalItems[index].notes = notes;
}
</script>

<script>window.ORDER_POLL = { panel: 'waiter' };</script>
<script src="/js/order-poll.js"></script>

<script>
(function() {
    function tick() {
        var now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('[data-created-at]').forEach(function(card) {
            var el = card.querySelector('[data-timer]');
            if (!el) return;
            var elapsed = now - parseInt(card.dataset.createdAt, 10);
            if (elapsed < 0) elapsed = 0;
            var m = Math.floor(elapsed / 60);
            el.textContent = '⏱ ' + m + 'm';
            el.classList.remove('timer-ok','timer-warn','timer-late');
            if (elapsed >= 1200)     el.classList.add('timer-late');
            else if (elapsed >= 600) el.classList.add('timer-warn');
            else                     el.classList.add('timer-ok');
        });
    }
    document.addEventListener('DOMContentLoaded', function() { tick(); setInterval(tick, 60000); });
})();
</script>

<style>
.waiter-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    flex-shrink: 0;
}
.waiter-action-btn svg { width: 18px; height: 18px; }
.waiter-action-btn:active { transform: scale(0.93); }

.waiter-item-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.waiter-btn-edit {
    background: #eff6ff;
    color: #2563eb;
    box-shadow: 0 2px 6px rgba(37,99,235,0.15);
}
.waiter-btn-edit:hover { background: #dbeafe; }

.waiter-btn-cancel {
    background: #fef2f2;
    color: #dc2626;
    box-shadow: 0 2px 6px rgba(220,38,38,0.15);
}
.waiter-btn-cancel:hover { background: #fee2e2; }

.waiter-order-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.waiter-btn-add {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #2563eb;
    color: #fff;
    box-shadow: 0 2px 8px rgba(37,99,235,0.35);
}
.waiter-btn-add svg { width: 22px; height: 22px; }
.waiter-btn-add:hover { background: #1d4ed8; }

.waiter-btn-serve {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #16a34a;
    color: #fff;
    box-shadow: 0 2px 8px rgba(22,163,74,0.35);
}
.waiter-btn-serve svg { width: 22px; height: 22px; }
.waiter-btn-serve:hover { background: #15803d; }

.waiter-btn-cancel-order {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #dc2626;
    color: #fff;
    box-shadow: 0 2px 8px rgba(220,38,38,0.35);
}
.waiter-btn-cancel-order svg { width: 22px; height: 22px; }
.waiter-btn-cancel-order:hover { background: #b91c1c; }

.waiter-order-timer {
    font-size: 11px;
    font-weight: 700;
    font-family: monospace;
    letter-spacing: 0.04em;
    padding: 2px 8px;
    border-radius: 20px;
}
.waiter-order-timer.timer-ok   { background:#dcfce7; color:#15803d; }
.waiter-order-timer.timer-warn { background:#fef9c3; color:#a16207; }
.waiter-order-timer.timer-late { background:#fee2e2; color:#b91c1c; animation:waiterTimerPulse 1s ease-in-out infinite; }
@keyframes waiterTimerPulse { 0%,100%{opacity:1} 50%{opacity:.5} }
.checkout-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 2px dashed #d1fae5;
}
.checkout-btn {
    width: 100%;
    background: #059669;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(5,150,105,0.35);
    transition: background 0.15s, transform 0.15s;
}
.checkout-btn:hover  { background: #047857; }
.checkout-btn:active { transform: scale(0.98); }
.checkout-hint {
    margin-top: 6px;
    font-size: 11px;
    color: #6b7280;
    text-align: center;
    font-style: italic;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection
