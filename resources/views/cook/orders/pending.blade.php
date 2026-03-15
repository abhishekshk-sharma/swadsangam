@extends('layouts.cook')

@section('title', 'Orders')

@section('content')
<style>
.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
}
.action-btn svg { width: 22px; height: 22px; }
.action-btn:active { transform: scale(0.93); }
.action-btn-done  { background:#16a34a; color:#fff; box-shadow:0 2px 8px rgba(22,163,74,.35); }
.action-btn-done:hover  { background:#15803d; }
.action-btn-edit  { background:#eff6ff; color:#2563eb; box-shadow:0 2px 8px rgba(37,99,235,.15); }
.action-btn-edit:hover  { background:#dbeafe; }
.action-btn-cancel{ background:#fef2f2; color:#dc2626; box-shadow:0 2px 8px rgba(220,38,38,.15); }
.action-btn-cancel:hover{ background:#fee2e2; }
.action-btn-wrap { display:flex; align-items:center; gap:8px; }
.action-btn-wrap form { display:flex; align-items:center; margin:0; padding:0; }
.action-btn-done-static {
    display:flex; align-items:center; justify-content:center;
    width:44px; height:44px; border-radius:10px;
    background:#dcfce7; color:#16a34a;
}
.order-timer { letter-spacing:0.04em; font-size:11px; }
.order-timer.timer-ok   { background:#dcfce7; color:#15803d; }
.order-timer.timer-warn { background:#fef9c3; color:#a16207; }
.order-timer.timer-late { background:#fee2e2; color:#b91c1c; animation:timerPulse 1s ease-in-out infinite; }
@keyframes timerPulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>
<div class="space-y-3">
    <h2 class="text-xl font-bold">Active Orders ({{ $orders->count() }})</h2>

    @forelse($orders as $order)
        @php
            $pendingItems = $order->orderItems->where('status', 'pending');
            $preparedItems = $order->orderItems->where('status', 'prepared');
            $hasNewItems = $order->orderItems->where('status', 'pending')->count() > 0 && $preparedItems->count() > 0;
        @endphp
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 {{ $hasNewItems ? 'border-red-500' : ($order->status === 'preparing' ? 'border-orange-500' : 'border-yellow-500') }}"
             data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}" data-created-at="{{ $order->created_at->timestamp }}">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <p class="text-sm text-gray-500">Table {{ $order->table->table_number }} • {{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="order-timer text-xs font-mono font-bold px-2 py-0.5 rounded-full" data-timer></span>
                        @if($hasNewItems)
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">+ New Items</span>
                        @elseif($order->status === 'preparing')
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">Cooking</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">New</span>
                        @endif
                    </div>
                </div>

                @if($order->customer_notes)
                <div class="mb-3 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <p class="text-sm text-yellow-800 font-semibold">Customer Note:</p>
                    <p class="text-sm text-gray-700 italic">{{ $order->customer_notes }}</p>
                </div>
                @endif

                <div class="space-y-2">
                    @foreach($order->orderItems as $item)
                        <div class="py-2 border-b last:border-0"
                             data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold {{ $item->status === 'prepared' ? 'line-through text-gray-400' : ($item->status === 'cancelled' ? 'line-through text-red-300' : '') }}" data-item-name>
                                            {{ $item->menuItem->name }}
                                        </span>
                                        @if($item->status === 'cancelled')
                                            <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>
                                        @elseif($item->status === 'pending' && $preparedItems->count() > 0)
                                            <span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-semibold">NEW</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">Qty: {{ $item->quantity }}</div>
                                    @if($item->notes)
                                        <div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ {{ $item->notes }}</div>
                                    @endif
                                </div>
                                <div class="ml-3 action-btn-wrap" data-item-actions>
                                    @if($item->status === 'pending')
                                        {{-- Mark Prepared --}}
                                        <form action="{{ route('cook.orderItems.updateStatus', $item) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="prepared">
                                            <button type="submit" title="Mark as Prepared"
                                                class="action-btn action-btn-done">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 6L9 17l-5-5"/>
                                                </svg>
                                            </button>
                                        </form>
                                        {{-- Edit --}}
                                        <button type="button" title="Edit Item"
                                            onclick="toggleCookEdit('cedit-{{ $item->id }}')"
                                            class="action-btn action-btn-edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                            </svg>
                                        </button>
                                        {{-- Cancel Item --}}
                                        <form action="{{ route('cook.orderItems.cancel', $item->id) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Cancel Item"
                                                class="action-btn action-btn-cancel">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @elseif($item->status === 'cancelled')
                                        <span class="text-xs text-red-400 px-2">Cancelled</span>
                                    @else
                                        <span class="action-btn-done-static" title="Prepared">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;">
                                                <path d="M20 6L9 17l-5-5"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if($item->status === 'pending')
                            <div id="cedit-{{ $item->id }}" class="hidden mt-2 bg-gray-50 rounded p-2">
                                <form action="{{ route('cook.orderItems.update', $item->id) }}" method="POST" class="flex flex-col gap-1">
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
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-2 border-t flex justify-between items-center">
                    <span class="text-sm text-gray-500">{{ $preparedItems->count() }} / {{ $order->orderItems->where('status','!=','cancelled')->count() }} items prepared</span>
                    @if($preparedItems->count() === 0)
                    <form action="{{ route('cook.orders.cancel', $order->id) }}" method="POST"
                          onsubmit="return confirm('Cancel entire order #{{ $order->id }}?')">
                        @csrf @method('PATCH')
                        <button class="bg-red-500 text-white px-3 py-1 rounded text-sm">Cancel Order</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">✓</div>
            <p class="text-gray-600">No active orders</p>
        </div>
    @endforelse
</div>
@endsection

<script>
function toggleCookEdit(id) {
    document.getElementById(id).classList.toggle('hidden');
}

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
            if (elapsed >= 1200)      el.classList.add('timer-late');
            else if (elapsed >= 600)  el.classList.add('timer-warn');
            else                      el.classList.add('timer-ok');
        });
    }
    document.addEventListener('DOMContentLoaded', function() { tick(); setInterval(tick, 60000); });
})();
</script>

<script>window.ORDER_POLL = { panel: 'cook' };</script>
<script src="/js/order-poll.js"></script>
