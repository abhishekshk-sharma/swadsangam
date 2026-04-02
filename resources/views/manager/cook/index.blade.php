@extends('layouts.manager')
@php use Illuminate\Support\Facades\URL; @endphp
@section('title', 'Kitchen Panel')
@section('content')
<style>
    .kitchen-tabs { display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid #e3e6e8;overflow-x:auto;scrollbar-width:none; }
    .kitchen-tab { padding:12px 24px;font-size:14px;font-weight:600;text-decoration:none;color:#666;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s;white-space:nowrap; }
    .kitchen-tab:hover,.kitchen-tab.active { color:#3b82f6;border-bottom-color:#3b82f6; }
    .order-card { background:#fff;border-radius:8px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e3e6e8;transition:all .2s; }
    .order-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.12);transform:translateY(-2px); }
    .order-card.pending { border-left:4px solid #ff9900; }
    .order-card.preparing { border-left:4px solid #4facfe; }
    .order-card.ready { border-left:4px solid #43e97b; }
    .order-header { display:flex;justify-content:space-between;align-items:start;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f0f0f0; }
    .status-badge-kitchen { padding:6px 12px;border-radius:4px;font-size:12px;font-weight:600;text-transform:uppercase; }
    .status-pending { background:#fff3cd;color:#856404; } .status-preparing { background:#cfe2ff;color:#084298; }
    .status-ready { background:#d1e7dd;color:#0f5132; } .status-served { background:#e0d7ff;color:#4c1d95; }
    .status-checkout { background:#d1fae5;color:#065f46; }
    .items-list { margin-bottom:16px; }
    .items-title { font-size:13px;font-weight:600;color:#666;text-transform:uppercase;margin-bottom:8px; }
    .item-row { padding:8px 0;border-bottom:1px solid #f9f9f9;display:flex;justify-content:space-between; }
    .item-name { font-size:14px;color:#232f3e; } .item-qty { font-size:14px;font-weight:700;color:#3b82f6; }
    .order-timer-admin { font-size:11px;font-weight:700;font-family:monospace;padding:2px 8px;border-radius:20px; }
    .order-timer-admin.timer-ok { background:#dcfce7;color:#15803d; }
    .order-timer-admin.timer-warn { background:#fef9c3;color:#a16207; }
    .order-timer-admin.timer-late { background:#fee2e2;color:#b91c1c;animation:pulse 1s ease-in-out infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
    .payment-mode-btn:hover,.payment-mode-btn.selected { border-color:#3b82f6!important;background:#eff6ff!important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 style="font-size:1.2rem;font-weight:600;"><i class="fas fa-fire-burner me-2"></i>Kitchen Panel</h1>
    <div class="d-flex gap-2">
        <span class="badge badge-warning">{{ $orders->where('status','pending')->count() }} Pending</span>
        <span class="badge badge-info">{{ $orders->where('status','preparing')->count() }} Preparing</span>
        <span class="badge badge-success">{{ $orders->where('status','ready')->count() }} Ready</span>
    </div>
</div>

<div style="display:flex;gap:10px;margin-bottom:12px;">
    <button onclick="switchType('table')" id="masterTab-table" style="display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;border:2px solid #1e3a5f;background:#1e3a5f;color:#fff;font-size:14px;font-weight:700;cursor:pointer;">
        🍽️ Table Orders <span id="masterCount-table" style="background:rgba(255,255,255,.25);border-radius:20px;padding:1px 8px;font-size:12px;">{{ $orders->where('is_parcel',false)->count() }}</span>
    </button>
    <button onclick="switchType('parcel')" id="masterTab-parcel" style="display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;border:2px solid #d1d5db;background:#fff;color:#6b7280;font-size:14px;font-weight:700;cursor:pointer;">
        📦 Parcel Orders <span id="masterCount-parcel" style="background:#f3f4f6;border-radius:20px;padding:1px 8px;font-size:12px;color:#374151;">{{ $orders->where('is_parcel',true)->count() }}</span>
    </button>
</div>

<div class="kitchen-tabs">
    @foreach(['all'=>'All','pending'=>'Pending','preparing'=>'Preparing','ready'=>'Ready','served'=>'Served','checkout'=>'Checkout','paid'=>'Paid','cancelled'=>'Cancelled'] as $s => $label)
    <a href="#" class="kitchen-tab {{ $s === 'all' ? 'active' : '' }}" onclick="filterOrders('{{ $s }}'); return false;">{{ $label }}</a>
    @endforeach
</div>

<div id="orders-container" class="row g-4">
    @forelse($orders as $order)
    <div class="col-md-6 col-lg-4 order-item" data-status="{{ $order->status }}" data-type="{{ $order->is_parcel ? 'parcel' : 'table' }}" data-created-at="{{ $order->created_at->timestamp }}">
        <div class="order-card {{ $order->status }}">
            <div class="order-header">
                <div>
                    <div style="font-size:18px;font-weight:700;color:#232f3e;">Order #{{ $order->daily_number ?? $order->id }}</div>
                    <div class="mt-1 d-flex align-items-center gap-2">
                        @if($order->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">📦 Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">T{{ $order->table?->table_number }}</span>
                            @if($order->table?->category)
                                <span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;">{{ $order->table->category->name }}</span>
                            @endif
                        @endif
                    </div>
                    <div style="font-size:12px;color:#999;"><i class="fas fa-clock me-1"></i>{{ $order->created_at->diffForHumans() }}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    @if(in_array($order->status, ['paid','cancelled']))
                        @php $dur = (int)$order->created_at->diffInMinutes($order->updated_at); @endphp
                        <span class="order-timer-admin {{ $order->status === 'paid' ? 'timer-ok' : 'timer-late' }}">⏱ {{ $dur }}m</span>
                    @else
                        <span class="order-timer-admin" data-timer></span>
                    @endif
                    <span class="status-badge-kitchen status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                </div>
            </div>

            <div class="items-list">
                <div class="items-title"><i class="fas fa-utensils me-2"></i>Items</div>
                @foreach($order->items as $item)
                <div class="item-row">
                    <div class="flex-grow-1">
                        <span class="item-name {{ $item->status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}">{{ $item->menuItem?->name ?? '[Deleted Item]' }}</span>
                        @if($item->status === 'cancelled')<span style="font-size:11px;color:#dc3545;"> (cancelled)</span>@endif
                        @if($item->notes)<div style="font-size:11px;color:#3b82f6;font-style:italic;margin-top:2px;">→ {{ $item->notes }}</div>@endif
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="item-qty">x{{ $item->quantity }}</span>
                        @if(!in_array($order->status, ['paid','cancelled']) && $item->status === 'pending')
                            <button onclick="toggleEdit('edit-{{ $item->id }}')" style="font-size:11px;padding:2px 6px;border:1px solid #3b82f6;background:#eff6ff;color:#1d4ed8;border-radius:4px;cursor:pointer;">Edit</button>
                            <form action="{{ route('manager.cook.orderItems.cancel', $item->id) }}" method="POST" class="mb-0">
                                @csrf @method('PATCH')
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
                @if($item->status === 'pending' && !in_array($order->status, ['paid','cancelled']))
                <div id="edit-{{ $item->id }}" style="display:none;background:#f8fafc;border-radius:6px;padding:8px;margin-top:4px;">
                    <form action="{{ route('manager.cook.orderItems.update', $item->id) }}" method="POST" class="d-flex flex-column gap-2">
                        @csrf @method('PATCH')
                        <div class="d-flex align-items-center gap-2">
                            <label style="font-size:12px;color:#666;min-width:40px;">Qty:</label>
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width:70px;border:1px solid #d5d9d9;border-radius:4px;padding:4px 8px;font-size:13px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label style="font-size:12px;color:#666;min-width:40px;">Note:</label>
                            <input type="text" name="notes" value="{{ $item->notes }}" style="flex:1;border:1px solid #d5d9d9;border-radius:4px;padding:4px 8px;font-size:13px;" placeholder="Special request...">
                        </div>
                        <button style="align-self:flex-end;background:#2563eb;color:white;border:none;padding:4px 12px;border-radius:4px;font-size:12px;cursor:pointer;">Save</button>
                    </form>
                </div>
                @endif
                @endforeach
            </div>

            @if($order->status === 'pending')
            <button onclick="doAction('{{ route('manager.cook.start', $order->id) }}')" class="btn btn-primary w-100"><i class="fas fa-play me-1"></i>Prepare</button>
            @elseif($order->status === 'preparing')
            <button onclick="doAction('{{ route('manager.cook.ready', $order->id) }}')" class="btn btn-primary w-100"><i class="fas fa-check me-1"></i>Mark Ready</button>
            @elseif($order->status === 'ready')
            <button onclick="doAction('{{ route('manager.cook.served', $order->id) }}')" class="btn btn-primary w-100"><i class="fas fa-utensils me-1"></i>Mark Served</button>
            @elseif(in_array($order->status, ['served','checkout']))
            <div class="mb-3">
                <div style="font-size:18px;font-weight:700;color:#16a34a;text-align:center;margin-bottom:12px;">Total: ₹{{ number_format($order->total_amount, 2) }}</div>
                <button onclick="openPaymentModal({{ $order->id }}, {{ $order->total_amount }}, '{{ $order->is_parcel ? 'Parcel' : $order->table?->table_number }}')" class="btn btn-primary w-100">
                    <i class="fas fa-money-bill me-1"></i>Take Payment
                </button>
            </div>
            @elseif($order->status === 'cancelled')
            <div class="text-center py-2"><span style="color:#dc3545;font-weight:600;"><i class="fas fa-times-circle me-1"></i>Cancelled</span></div>
            @else
            <div class="text-center py-3">
                <i class="fas fa-check-circle" style="font-size:48px;color:#16a34a;"></i>
                <div class="mt-2" style="color:#15803d;font-weight:600;">Payment Completed</div>
                <div style="font-size:14px;color:#666;margin-top:4px;">₹{{ number_format($order->total_amount, 2) }} - {{ ucfirst($order->payment_mode ?? 'cash') }}</div>
                <button onclick="showQr({{ $order->id }})" class="mt-3 w-100" style="background:#f0fdf4;border:2px solid #86efac;color:#15803d;border-radius:8px;padding:10px;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-qrcode me-2"></i>Show Bill QR
                </button>
            </div>
            @endif

            @if(!in_array($order->status, ['paid','cancelled']))
            <form action="{{ route('manager.cook.orders.cancel', $order->id) }}" method="POST" class="mt-2" onsubmit="return confirm('Cancel order #{{ $order->id }}?')">
                @csrf @method('PATCH')
                <button class="w-100" style="background:#fee2e2;color:#dc2626;border:none;padding:8px;border-radius:6px;font-size:13px;font-weight:600;">
                    <i class="fas fa-ban me-1"></i>Cancel Order
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-12"><div class="empty-state"><i class="fas fa-fire-burner"></i><p>No active orders</p></div></div>
    @endforelse
</div>

<!-- Payment Modal -->
<div id="paymentModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:100%;max-width:480px;margin:auto;box-shadow:0 10px 40px rgba(0,0,0,.2);">
        <div style="background:linear-gradient(135deg,#1e3a5f,#2a4f7c);color:#fff;padding:20px 24px;border-radius:12px 12px 0 0;">
            <h5 style="margin:0;font-weight:700;">Process Payment</h5>
            <p style="margin:4px 0 0;font-size:13px;opacity:.9;">Order #<span id="modalOrderId"></span> — <span id="modalTableNumber"></span></p>
        </div>
        <div style="padding:24px;">
            <form id="paymentForm" method="POST">
                @csrf @method('PATCH')
                <div style="background:#f0fdf4;border:2px solid #86efac;border-radius:8px;padding:16px;text-align:center;margin-bottom:20px;">
                    <div style="font-size:14px;color:#166534;font-weight:600;margin-bottom:4px;">Total Amount</div>
                    <div id="modalTotalAmount" style="font-size:32px;font-weight:700;color:#15803d;">₹0.00</div>
                </div>
                <div class="row g-2 mb-4">
                    @foreach(['cash'=>'💵 Cash','upi'=>'📱 UPI','card'=>'💳 Card'] as $mode => $label)
                    <div class="col-4">
                        <button type="button" onclick="selectMode('{{ $mode }}')" class="payment-mode-btn w-100" data-mode="{{ $mode }}" style="padding:16px;border:2px solid #d5d9d9;border-radius:8px;background:#fff;cursor:pointer;transition:all .2s;">
                            <div style="font-size:28px;margin-bottom:6px;">{{ explode(' ',$label)[0] }}</div>
                            <div style="font-size:13px;font-weight:600;">{{ explode(' ',$label)[1] }}</div>
                        </button>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="payment_mode" id="paymentMode" required>
                <div id="cashSection" style="display:none;margin-bottom:16px;">
                    <label class="form-label" style="font-weight:600;">Cash Received</label>
                    <div style="display:flex;gap:8px;">
                        <input type="number" step="0.01" min="0" id="cashReceived" class="form-control" placeholder="Enter amount">
                        <button type="button" onclick="calcChange()" class="btn btn-primary">Calc</button>
                    </div>
                </div>
                <div id="changeSection" style="display:none;background:#fef3c7;border:2px solid #fbbf24;border-radius:8px;padding:16px;text-align:center;margin-bottom:16px;">
                    <div style="font-size:13px;color:#92400e;font-weight:600;margin-bottom:4px;">Change to Return</div>
                    <div id="changeAmount" style="font-size:28px;font-weight:700;color:#b45309;">₹0.00</div>
                </div>
                <button type="submit" id="submitPaymentBtn" class="btn btn-primary w-100" style="padding:14px;font-size:16px;" disabled>
                    <i class="fas fa-check-circle me-2"></i>Complete Payment
                </button>
            </form>
        </div>
        <div style="padding:12px 24px;border-top:1px solid #e5e7eb;text-align:right;">
            <button onclick="closePaymentModal()" class="btn btn-secondary btn-sm">Cancel</button>
        </div>
    </div>
</div>

<!-- Bill QR Modal -->
<div id="qrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:360px;margin:auto;text-align:center;">
        <div style="color:#16a34a;font-size:48px;margin-bottom:8px;">✅</div>
        <h5 style="font-weight:700;margin-bottom:4px;">Bill QR Code</h5>
        <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:16px;"><div id="qrContainer"></div></div>
        <a id="billLink" href="#" target="_blank" style="font-size:13px;color:#2563eb;word-break:break-all;display:block;margin-bottom:16px;"></a>
        <div style="display:flex;gap:8px;">
            <button onclick="closeQr()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Close</button>
            <a id="openBillBtn" href="#" target="_blank" style="flex:1;background:#2563eb;color:#fff;border-radius:8px;padding:10px;font-weight:600;text-decoration:none;display:inline-block;">Open Bill</a>
        </div>
    </div>
</div>

<script>
let activeType = 'table', activeStatus = 'all', currentTotal = 0;

function toggleEdit(id) { const el = document.getElementById(id); el.style.display = el.style.display === 'none' ? 'block' : 'none'; }

function switchType(type) {
    activeType = type; activeStatus = 'all';
    const t = document.getElementById('masterTab-table'), p = document.getElementById('masterTab-parcel');
    if (type === 'table') { t.style.cssText += 'background:#1e3a5f;color:#fff;border-color:#1e3a5f;'; p.style.cssText += 'background:#fff;color:#6b7280;border-color:#d1d5db;'; }
    else { p.style.cssText += 'background:#ea580c;color:#fff;border-color:#ea580c;'; t.style.cssText += 'background:#fff;color:#6b7280;border-color:#d1d5db;'; }
    document.querySelectorAll('.kitchen-tab').forEach((t,i) => t.classList.toggle('active', i === 0));
    applyFilters();
}

function filterOrders(status) {
    activeStatus = status;
    document.querySelectorAll('.kitchen-tab').forEach(t => t.classList.remove('active'));
    event.target.closest('.kitchen-tab').classList.add('active');
    applyFilters();
}

function applyFilters() {
    document.querySelectorAll('.order-item').forEach(item => {
        item.style.display = (item.dataset.type === activeType && (activeStatus === 'all' || item.dataset.status === activeStatus)) ? 'block' : 'none';
    });
}

function doAction(url) {
    fetch(url, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'} })
        .then(r => r.json()).then(() => location.reload());
}

function openPaymentModal(id, total, table) {
    currentTotal = total;
    document.getElementById('modalOrderId').textContent = id;
    document.getElementById('modalTableNumber').textContent = table;
    document.getElementById('modalTotalAmount').textContent = '₹' + total.toFixed(2);
    document.getElementById('paymentForm').action = `/manager/cook/${id}/payment`;
    document.getElementById('paymentMode').value = '';
    document.getElementById('cashSection').style.display = 'none';
    document.getElementById('changeSection').style.display = 'none';
    document.getElementById('submitPaymentBtn').disabled = true;
    document.querySelectorAll('.payment-mode-btn').forEach(b => b.classList.remove('selected'));
    document.getElementById('paymentModal').style.display = 'flex';
}

function closePaymentModal() { document.getElementById('paymentModal').style.display = 'none'; }

function selectMode(mode) {
    document.querySelectorAll('.payment-mode-btn').forEach(b => b.classList.remove('selected'));
    document.querySelector(`[data-mode="${mode}"]`).classList.add('selected');
    document.getElementById('paymentMode').value = mode;
    document.getElementById('cashSection').style.display = mode === 'cash' ? 'block' : 'none';
    document.getElementById('changeSection').style.display = 'none';
    document.getElementById('submitPaymentBtn').disabled = mode === 'cash';
}

function calcChange() {
    const cash = parseFloat(document.getElementById('cashReceived').value);
    if (!cash || cash < currentTotal) { alert('Cash must be at least ₹' + currentTotal.toFixed(2)); return; }
    document.getElementById('changeAmount').textContent = '₹' + (cash - currentTotal).toFixed(2);
    document.getElementById('changeSection').style.display = 'block';
    document.getElementById('submitPaymentBtn').disabled = false;
}

document.getElementById('paymentForm').addEventListener('submit', function(e) {
    if (!document.getElementById('paymentMode').value) { e.preventDefault(); alert('Select a payment method'); }
});

const BILL_URLS = {
    @foreach($orders->where('status','paid') as $order)
    {{ $order->id }}: "{{ URL::signedRoute('bill.show', ['orderId' => $order->id]) }}",
    @endforeach
};

function showQr(id) {
    const url = BILL_URLS[id]; if (!url) return;
    document.getElementById('billLink').textContent = url;
    document.getElementById('billLink').href = url;
    document.getElementById('openBillBtn').href = url;
    const c = document.getElementById('qrContainer'); c.innerHTML = '';
    new QRCode(c, { text:url, width:200, height:200, colorDark:'#111827', colorLight:'#ffffff', correctLevel:QRCode.CorrectLevel.M });
    document.getElementById('qrModal').style.display = 'flex';
}
function closeQr() { document.getElementById('qrModal').style.display = 'none'; }

(function() {
    function tick() {
        var now = Math.floor(Date.now() / 1000);
        document.querySelectorAll('[data-created-at]').forEach(function(card) {
            var el = card.querySelector('[data-timer]'); if (!el) return;
            var elapsed = now - parseInt(card.dataset.createdAt, 10); if (elapsed < 0) elapsed = 0;
            el.textContent = '⏱ ' + Math.floor(elapsed / 60) + 'm';
            el.className = 'order-timer-admin ' + (elapsed >= 1200 ? 'timer-late' : elapsed >= 600 ? 'timer-warn' : 'timer-ok');
        });
    }
    document.addEventListener('DOMContentLoaded', function() { tick(); setInterval(tick, 60000); applyFilters(); });
})();
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endsection
