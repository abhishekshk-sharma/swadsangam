@extends('layouts.waiter')

@section('title', 'Create Order')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold">Create New Order</h1>
</div>

<div class="bg-white p-4 rounded-lg shadow">
    <form action="/waiter/orders" method="POST" id="orderForm">
        @csrf
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-semibold text-sm">Select Table</label>
            <input type="text" id="tableSearch" placeholder="Search table..." class="w-full border rounded px-3 py-2 mb-2 text-sm">
            <select name="table_id" id="tableSelect" class="w-full border rounded px-3 py-2" required>
                <option value="">Choose a table</option>
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">Table {{ $table->table_number }} ({{ $table->capacity }} seats)</option>
                @endforeach
            </select>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700 mb-2 font-semibold text-sm">Add Items</label>
            <div id="items-container" class="space-y-3">
                <div class="item-row border p-3 rounded">
                    <input type="text" placeholder="Search item..." class="w-full border rounded px-3 py-2 mb-2 text-sm item-search" onkeyup="filterItems(this)">
                    <select name="items[0][menu_item_id]" class="w-full border rounded px-3 py-2 mb-2 menu-item text-sm" required>
                        <option value="">Select item</option>
                        @foreach($menuItems as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->price }}">{{ $item->name }} - ₹{{ $item->price }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <input type="number" name="items[0][quantity]" min="1" value="1" class="flex-1 border rounded px-3 py-2 quantity" required>
                        <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-3 py-2 rounded text-sm">Remove</button>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addItem()" class="bg-green-500 text-white px-4 py-2 rounded w-full mt-3 text-sm">+ Add Item</button>
        </div>
        
        <div class="mb-4 p-4 bg-gray-50 rounded">
            <div class="flex justify-between text-xl font-bold">
                <span>Total:</span>
                <span id="total">₹0.00</span>
            </div>
        </div>
        
        <div class="space-y-2">
            <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded w-full font-semibold">Create Order</button>
            <a href="/waiter/dashboard" class="bg-gray-500 text-white px-6 py-3 rounded w-full block text-center">Cancel</a>
        </div>
    </form>
</div>

<script>
let itemIndex = 1;

function addItem() {
    const container = document.getElementById('items-container');
    const newItem = `
        <div class="item-row border p-3 rounded">
            <input type="text" placeholder="Search item..." class="w-full border rounded px-3 py-2 mb-2 text-sm item-search" onkeyup="filterItems(this)">
            <select name="items[${itemIndex}][menu_item_id]" class="w-full border rounded px-3 py-2 mb-2 menu-item text-sm" required>
                <option value="">Select item</option>
                @foreach($menuItems as $item)
                    <option value="{{ $item->id }}" data-price="{{ $item->price }}">{{ $item->name }} - ₹{{ $item->price }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1" class="flex-1 border rounded px-3 py-2 quantity" required>
                <button type="button" onclick="removeItem(this)" class="bg-red-500 text-white px-3 py-2 rounded text-sm">Remove</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', newItem);
    itemIndex++;
    calculateTotal();
}

function removeItem(btn) {
    if (document.querySelectorAll('.item-row').length > 1) {
        btn.closest('.item-row').remove();
        calculateTotal();
    }
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const select = row.querySelector('.menu-item');
        const quantity = row.querySelector('.quantity').value;
        const price = select.options[select.selectedIndex]?.dataset.price || 0;
        total += price * quantity;
    });
    document.getElementById('total').textContent = '₹' + total.toFixed(2);
}

document.getElementById('orderForm').addEventListener('change', calculateTotal);
document.getElementById('orderForm').addEventListener('input', calculateTotal);

// Table search
document.getElementById('tableSearch').addEventListener('keyup', function() {
    const search = this.value.toLowerCase();
    const select = document.getElementById('tableSelect');
    Array.from(select.options).forEach(option => {
        if (option.value === '') return;
        const text = option.textContent.toLowerCase();
        option.style.display = text.includes(search) ? '' : 'none';
    });
});

// Item search
function filterItems(input) {
    const search = input.value.toLowerCase();
    const select = input.nextElementSibling;
    Array.from(select.options).forEach(option => {
        if (option.value === '') return;
        const text = option.textContent.toLowerCase();
        option.style.display = text.includes(search) ? '' : 'none';
    });
}
</script>
@endsection
