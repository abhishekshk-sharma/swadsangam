<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menu - Table {{ $table->table_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <div class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Table {{ $table->table_number }}</h1>
                        <p class="text-sm text-gray-500">Capacity: {{ $table->capacity }} seats</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Total Items</p>
                        <p id="total-items" class="text-2xl font-bold text-blue-600">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto p-4">
            @if($activeOrders->isNotEmpty())
            <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-800">Your Active Orders</h2>
                @foreach($activeOrders as $order)
                <div class="bg-gray-50 rounded-lg p-4 mb-3 order-status" data-order-id="{{ $order->id }}">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-semibold text-gray-700">Order #{{ $order->id }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">{{ ucfirst($order->status) }}</span>
                    </div>
                    <ul class="text-sm text-gray-600 mb-2 space-y-2">
                        @foreach($order->items as $item)
                        <li class="flex items-center gap-3">
                            @if($item->menuItem->image)
                            <img src="{{ asset($item->menuItem->image) }}" class="w-12 h-12 object-cover rounded">
                            @else
                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400 text-xs">No Img</div>
                            @endif
                            <div class="flex-1 flex justify-between">
                                <span>{{ $item->quantity }}x {{ $item->menuItem->name }}</span>
                                <span class="font-medium">${{ number_format($item->price * $item->quantity, 2) }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <div class="border-t pt-2 flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Total</span>
                        <span class="font-bold text-gray-800">${{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    @if($order->status === 'preparing' && $order->ready_at)
                    <div class="mt-2 pt-2 border-t">
                        <p class="text-sm text-green-600 font-semibold">Ready in: <span class="countdown" data-ready-at="{{ $order->ready_at }}">{{ $order->preparation_time }} min</span></p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            <div class="mb-20">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Menu</h2>
                @foreach($menuItems as $category => $items)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">{{ $category }}</h3>
                    <div class="space-y-3">
                        @foreach($items as $item)
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex gap-4">
                                @if($item->image)
                                <img src="{{ asset($item->image) }}" class="w-24 h-24 object-cover rounded flex-shrink-0">
                                @else
                                <div class="w-24 h-24 bg-gray-200 rounded flex-shrink-0 flex items-center justify-center text-gray-400 text-xs">No Image</div>
                                @endif
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800">{{ $item->name }}</h4>
                                    @if($item->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ $item->description }}</p>
                                    @endif
                                    <p class="text-lg font-bold text-green-600 mt-2">${{ number_format($item->price, 2) }}</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button onclick="changeQuantity({{ $item->id }}, -1)" class="w-10 h-10 bg-gray-200 rounded-full hover:bg-gray-300 flex items-center justify-center font-bold text-xl">-</button>
                                    <span class="quantity-{{ $item->id }} font-semibold text-xl w-10 text-center">0</span>
                                    <button onclick="changeQuantity({{ $item->id }}, 1)" class="w-10 h-10 bg-blue-500 text-white rounded-full hover:bg-blue-600 flex items-center justify-center font-bold text-xl">+</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t">
            <div class="max-w-4xl mx-auto p-4">
                <button onclick="placeOrder()" class="w-full bg-blue-600 text-white py-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition">Place Order</button>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
    const cart = {};
    const tableQrCode = '{{ $table->qr_code }}';
    const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {cluster: '{{ env("PUSHER_APP_CLUSTER") }}'});
    const channel = pusher.subscribe('orders');
    channel.bind('order-updated', function(data) {
        const orderCard = document.querySelector(`[data-order-id="${data.order.id}"]`);
        if (orderCard && data.order.status === 'ready') {
            orderCard.querySelector('.px-3').textContent = 'Ready';
            orderCard.querySelector('.px-3').className = 'px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800';
        } else if (orderCard && data.order.status === 'preparing') location.reload();
    });
    function changeQuantity(itemId, change) {
        cart[itemId] = (cart[itemId] || 0) + change;
        if (cart[itemId] < 0) cart[itemId] = 0;
        document.querySelector(`.quantity-${itemId}`).textContent = cart[itemId];
        updateTotal();
    }
    function updateTotal() {
        document.getElementById('total-items').textContent = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
    }
    function placeOrder() {
        const items = Object.entries(cart).filter(([id, qty]) => qty > 0).map(([id, qty]) => ({ id: parseInt(id), quantity: qty }));
        if (items.length === 0) {
            alert('Please select items to order');
            return;
        }

        fetch(`/table/${tableQrCode}/order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ items })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order placed successfully!');
                location.reload();
            } else {
                alert('Error placing order. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error placing order. Please try again.');
        });
    }
    setInterval(() => {
        document.querySelectorAll('.countdown').forEach(el => {
            const diff = Math.max(0, Math.floor((new Date(el.dataset.readyAt) - new Date()) / 60000));
            el.textContent = diff === 0 ? 'Ready!' : diff + ' min';
        });
    }, 1000);
    </script>
</body>
</html>
