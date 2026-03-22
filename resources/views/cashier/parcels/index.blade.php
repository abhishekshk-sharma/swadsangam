@extends('layouts.cashier')

@section('title', 'Parcel Orders')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold">Parcel Orders ({{ $orders->count() }})</h2>
    <a href="{{ route('cashier.parcels.create') }}"
       class="flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-4 py-2 rounded-lg shadow text-sm">
        📦 New Parcel
    </a>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">{{ session('error') }}</div>
@endif

<div class="space-y-3">
    @forelse($orders as $order)
    <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4
        {{ $order->status === 'ready' ? 'border-green-500' : ($order->status === 'preparing' ? 'border-orange-500' : 'border-yellow-500') }}"
        data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
        <div class="p-4">
            {{-- Header --}}
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">📦 Parcel</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('h:i A') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold
                    {{ $order->status === 'ready'     ? 'bg-green-100 text-green-800'  : '' }}
                    {{ $order->status === 'preparing' ? 'bg-orange-100 text-orange-800': '' }}
                    {{ $order->status === 'pending'   ? 'bg-yellow-100 text-yellow-800': '' }}"
                    data-order-status-badge>
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            @if($order->customer_notes)
            <div class="mb-3 bg-yellow-50 border-l-4 border-yellow-400 p-2 rounded">
                <p class="text-xs font-semibold text-yellow-800">Note:</p>
                <p class="text-xs text-gray-700 italic">{{ $order->customer_notes }}</p>
            </div>
            @endif

            {{-- Items --}}
            <div class="space-y-1 mb-3">
                @foreach($order->orderItems as $item)
                <div class="py-1.5 border-b last:border-0" data-item-id="{{ $item->id }}">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-sm {{ $item->status === 'prepared' ? 'line-through text-gray-400' : ($item->status === 'cancelled' ? 'line-through text-red-300' : '') }}">
                                    {{ $item->menuItem?->name ?? '[Deleted Item]' }}
                                </span>
                                @if($item->status === 'cancelled')
                                    <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>
                                @elseif($item->status === 'prepared')
                                    <span class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded">Ready</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">Qty: {{ $item->quantity }}</div>
                            @if($item->notes)
                                <div class="text-xs text-orange-600 italic mt-0.5 bg-orange-50 px-2 py-0.5 rounded">→ {{ $item->notes }}</div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 ml-2">
                            @if($item->status === 'pending')
                                {{-- Edit --}}
                                <button type="button" onclick="toggleEdit('edit-{{ $item->id }}')"
                                    style="width:32px;height:32px;border-radius:8px;background:#eff6ff;color:#2563eb;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                {{-- Cancel item --}}
                                <form action="{{ route('cashier.parcelItems.cancel', $item->id) }}" method="POST" style="display:flex;">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        style="width:32px;height:32px;border-radius:8px;background:#fef2f2;color:#dc2626;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;">
                                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @if($item->status === 'pending')
                    <div id="edit-{{ $item->id }}" class="hidden mt-2 bg-gray-50 rounded p-2">
                        <form action="{{ route('cashier.parcelItems.update', $item->id) }}" method="POST" class="flex flex-col gap-1">
                            @csrf @method('PATCH')
                            <div class="flex gap-2 items-center">
                                <label class="text-xs text-gray-500">Qty:</label>
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="w-16 border rounded px-2 py-0.5 text-sm">
                            </div>
                            <div class="flex gap-2 items-center">
                                <label class="text-xs text-gray-500">Note:</label>
                                <input type="text" name="notes" value="{{ $item->notes }}" class="flex-1 border rounded px-2 py-0.5 text-sm" placeholder="Special request...">
                            </div>
                            <button class="self-end bg-blue-600 text-white px-3 py-0.5 rounded text-xs">Save</button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="pt-2 border-t flex justify-between items-center">
                <span class="font-bold text-base">₹{{ number_format($order->total_amount, 2) }}</span>
                <div class="flex items-center gap-2">
                    {{-- Add items --}}
                    @if(!in_array($order->status, ['paid','cancelled']))
                    <button type="button" onclick="openAddItems({{ $order->id }})"
                        style="width:38px;height:38px;border-radius:10px;background:#2563eb;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(37,99,235,.35);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
                        </svg>
                    </button>
                    @endif
                    {{-- Cancel order --}}
                    @if(!in_array($order->status, ['paid','cancelled']) && $order->orderItems->where('status','prepared')->count() === 0)
                    <form action="{{ route('cashier.parcels.cancel', $order->id) }}" method="POST"
                          onsubmit="return confirm('Cancel parcel order #{{ $order->id }}?')">
                        @csrf @method('PATCH')
                        <button type="submit"
                            style="width:38px;height:38px;border-radius:10px;background:#dc2626;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(220,38,38,.35);">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Bill button when ready --}}
            @if($order->status === 'ready')
            <div class="mt-3">
                <a href="{{ route('cashier.payments.index') }}"
                   class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold text-sm">
                    💳 Go to Billing
                </a>
            </div>
            @endif
        </div>
    </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">📦</div>
            <p class="text-gray-600">No active parcel orders</p>
            <a href="{{ route('cashier.parcels.create') }}" class="text-orange-500 font-semibold mt-2 inline-block">+ New Parcel Order</a>
        </div>
    @endforelse
</div>

{{-- Add Items Modal --}}
<div id="addItemsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="fixed inset-0 flex items-end">
        <div class="bg-white w-full rounded-t-3xl max-h-[90vh] flex flex-col">
            <div class="p-4 border-b flex items-center justify-between flex-shrink-0">
                <div>
                    <h2 class="text-xl font-bold">Add Items</h2>
                    <p class="text-sm text-gray-600">📦 Parcel Order #<span id="modalOrderId"></span></p>
                </div>
                <button onclick="closeAddItems()" class="text-gray-500 text-2xl">×</button>
            </div>

            <div class="p-4 border-b flex-shrink-0">
                <input type="text" id="parcelItemSearch" placeholder="Search menu items..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 mb-3"
                       onkeyup="filterParcelItems()">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button onclick="filterParcelCategory('all')" class="parcel-cat-btn px-4 py-2 rounded-full bg-blue-600 text-white text-sm whitespace-nowrap">All</button>
                    @php
                        $parcelCategories = ($menuItems ?? collect())->pluck('category.name')->filter()->unique();
                    @endphp
                    @foreach($parcelCategories as $cat)
                        <button onclick="filterParcelCategory('{{ $cat }}')" class="parcel-cat-btn px-4 py-2 rounded-full bg-gray-200 text-gray-700 text-sm whitespace-nowrap">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4" style="min-height:0;">
                <div id="modalMenuItems" class="grid grid-cols-2 gap-3">
                    @foreach($menuItems as $item)
                    <div class="parcel-menu-item bg-white rounded-lg shadow-md border border-gray-200 flex flex-col"
                         data-category="{{ $item->category?->name }}" data-name="{{ strtolower($item->name) }}">
                        @if($item->image)
                            <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="w-full h-32 object-cover flex-shrink-0">
                        @else
                            <div class="w-full h-32 bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-4xl">📦</span>
                            </div>
                        @endif
                        <div class="p-3 flex flex-col flex-grow">
                            <h3 class="font-semibold text-sm text-gray-800 mb-1">{{ $item->name }}</h3>
                            @if($item->description)
                                <p class="text-xs text-gray-600 mb-2 line-clamp-2">{{ $item->description }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-auto">
                                <span class="text-lg font-bold text-blue-600">₹{{ $item->price }}</span>
                                <button onclick="addModalItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }})"
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
                <div id="selectedItemsList" class="mb-3 max-h-40 overflow-y-auto hidden"></div>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-lg font-semibold">New Items Total:</span>
                    <span id="newItemsTotal" class="text-2xl font-bold text-blue-600">₹0.00</span>
                </div>
                <button onclick="submitAddItems()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-lg hover:bg-blue-700">Add to Order</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderId = null;
let additionalItems = [];

function toggleEdit(id) { document.getElementById(id).classList.toggle('hidden'); }

function openAddItems(orderId) {
    currentOrderId = orderId;
    additionalItems = [];
    document.getElementById('modalOrderId').textContent = orderId;
    document.getElementById('selectedItemsList').classList.add('hidden');
    document.getElementById('selectedItemsList').innerHTML = '';
    document.getElementById('newItemsTotal').textContent = '₹0.00';
    document.getElementById('parcelItemSearch').value = '';
    document.querySelectorAll('.parcel-menu-item').forEach(i => i.style.display = 'block');
    const btns = document.querySelectorAll('.parcel-cat-btn');
    btns.forEach(b => { b.classList.remove('bg-blue-600','text-white'); b.classList.add('bg-gray-200','text-gray-700'); });
    if (btns.length) { btns[0].classList.remove('bg-gray-200','text-gray-700'); btns[0].classList.add('bg-blue-600','text-white'); }
    document.getElementById('addItemsModal').classList.remove('hidden');
}

function closeAddItems() {
    if (additionalItems.length > 0 && !confirm('You have selected items. Close anyway?')) return;
    document.getElementById('addItemsModal').classList.add('hidden');
    additionalItems = [];
}

function addModalItem(itemId, itemName, itemPrice) {
    const existing = additionalItems.find(i => i.id === itemId);
    if (existing) { existing.quantity++; }
    else { additionalItems.push({ id: itemId, name: itemName, price: itemPrice, quantity: 1, notes: '' }); }
    renderSelectedItems();
}

function renderSelectedItems() {
    const div = document.getElementById('selectedItemsList');
    if (!additionalItems.length) { div.classList.add('hidden'); div.innerHTML = ''; }
    else {
        div.classList.remove('hidden');
        div.innerHTML = '<div class="space-y-2">' + additionalItems.map((item, i) => `
            <div class="bg-white p-3 rounded-lg shadow-sm border-b">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex-1"><h3 class="font-semibold text-sm">${item.name}</h3><p class="text-xs text-gray-600">₹${item.price} each</p></div>
                    <div class="flex items-center gap-2">
                        <button onclick="changeModalQty(${i},-1)" class="w-7 h-7 bg-gray-200 rounded-full font-bold text-sm">-</button>
                        <span class="font-bold w-7 text-center">${item.quantity}</span>
                        <button onclick="changeModalQty(${i},1)" class="w-7 h-7 bg-blue-600 text-white rounded-full font-bold text-sm">+</button>
                        <button onclick="removeModalItem(${i})" class="ml-1 text-red-500 text-lg">🗑️</button>
                    </div>
                </div>
                <textarea rows="1" class="w-full px-2 py-1 border border-gray-300 rounded text-xs"
                    placeholder="Special request..." onchange="additionalItems[${i}].notes=this.value">${item.notes}</textarea>
            </div>`).join('') + '</div>';
    }
    const total = additionalItems.reduce((s, i) => s + i.price * i.quantity, 0);
    document.getElementById('newItemsTotal').textContent = '₹' + total.toFixed(2);
}

function changeModalQty(index, change) {
    additionalItems[index].quantity += change;
    if (additionalItems[index].quantity <= 0) additionalItems.splice(index, 1);
    renderSelectedItems();
}

function removeModalItem(index) {
    additionalItems.splice(index, 1);
    renderSelectedItems();
}

function filterParcelItems() {
    const q = document.getElementById('parcelItemSearch').value.toLowerCase();
    document.querySelectorAll('.parcel-menu-item').forEach(el => {
        el.style.display = el.dataset.name.includes(q) ? 'block' : 'none';
    });
}

function filterParcelCategory(cat) {
    document.querySelectorAll('.parcel-cat-btn').forEach(b => {
        b.classList.remove('bg-blue-600','text-white'); b.classList.add('bg-gray-200','text-gray-700');
    });
    event.target.classList.remove('bg-gray-200','text-gray-700');
    event.target.classList.add('bg-blue-600','text-white');
    document.querySelectorAll('.parcel-menu-item').forEach(el => {
        el.style.display = (cat === 'all' || el.dataset.category === cat) ? 'block' : 'none';
    });
}

function submitAddItems() {
    if (!additionalItems.length) { alert('Please select at least one item!'); return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/cashier/parcels/${currentOrderId}/add-items`;
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);
    additionalItems.forEach((item, i) => {
        [['menu_item_id', item.id], ['quantity', item.quantity], ['notes', item.notes || '']].forEach(([k, v]) => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = `items[${i}][${k}]`; inp.value = v;
            form.appendChild(inp);
        });
    });
    document.body.appendChild(form);
    form.submit();
}
</script>

<script>window.ORDER_POLL = { panel: 'cashier_parcels' };</script>
<script src="/js/order-poll.js"></script>

<style>.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}</style>
@endsection
