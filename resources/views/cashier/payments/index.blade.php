@extends('layouts.cashier')

@section('title', 'Pending Payments')
@php use Illuminate\Support\Facades\URL; @endphp

@section('content')
<div class="space-y-3">
<div class="flex justify-between items-center">
    <h2 class="text-xl font-bold">Pending Payments (<span id="pendingCount">{{ $orders->count() }}</span>)</h2>
    <div style="display:flex;gap:8px;align-items:center;">
        <button id="instantToggle" onclick="toggleInstantMode()"
            style="display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;border:2px solid #d1d5db;background:#fff;color:#6b7280;transition:all 0.2s;">
            <i class="fas fa-bolt"></i> <span id="instantLabel">Instant Mode</span>
        </button>
        <button onclick="location.reload()" class="flex items-center gap-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold">
            🔄 Refresh
        </button>
    </div>
</div>
<div id="instantBanner" style="display:none;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:10px 14px;font-size:13px;color:#5b21b6;font-weight:600;margin-top:8px;">
    <i class="fas fa-bolt me-1"></i> Instant Mode ON — new orders appear here immediately on creation.
</div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @forelse($orders as $order)
        <div class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-red-500"
             data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}" data-is-parcel="{{ $order->is_parcel ? '1' : '0' }}">
            <div class="p-4">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="font-bold text-lg">Order #{{ $order->id }}</h3>
                        <div class="flex items-center gap-2 mt-1">
                            @if($order->is_parcel)
                                <span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">📦 Parcel</span>
                            @else
                                <span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">T{{ $order->table?->table_number }}</span>
                                @if($order->table?->category)
                                    <span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;letter-spacing:0.02em;">{{ $order->table->category->name }}</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-xs text-gray-400" style='margin-top: 3px;'>{{ $order->created_at->format('h:i A') }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800"
                        data-order-status-badge>
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    @foreach($order->orderItems as $item)
                        <div class="py-2 border-b" data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold {{ $item->status === 'cancelled' ? 'line-through text-gray-400' : '' }}">{{ $item->menuItem?->name ?? '[Deleted Item]' }}</span>
                                        @if($item->status === 'cancelled')
                                            <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-{{ $item->status === 'cancelled' ? '400' : '600' }}">Qty: {{ $item->quantity }}</div>
                                    @if($item->notes)
                                        <div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">
                                            → {{ $item->notes }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($item->status === 'cancelled')
                                        <div class="text-gray-400 line-through text-sm">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                    @else
                                        <div class="font-bold">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($order->customer_notes)
                <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <h4 class="font-semibold mb-1 text-sm text-yellow-800">Customer Request:</h4>
                    <p class="text-sm text-gray-700 italic">{{ $order->customer_notes }}</p>
                </div>
                @endif

                <div class="pt-3 border-t">
    @php
        $gst = $branchGst;
        $grandTotal = $order->total_amount;
        if ($gst['enabled'] && $gst['mode'] === 'excluded') {
            $cgstAmt    = round($order->total_amount * $gst['cgst_pct'] / 100, 2);
            $sgstAmt    = round($order->total_amount * $gst['sgst_pct'] / 100, 2);
            $grandTotal = $order->total_amount + $cgstAmt + $sgstAmt;
        } elseif ($gst['enabled'] && $gst['mode'] === 'included') {
            $base    = round($order->total_amount * 100 / (100 + $gst['total_pct']), 2);
            $cgstAmt = round($base * $gst['cgst_pct'] / 100, 2);
            $sgstAmt = round($base * $gst['sgst_pct'] / 100, 2);
        }
    @endphp
    @if($gst['enabled'])
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px;">
        @if($gst['mode'] === 'excluded')
        <div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>₹{{ number_format($order->total_amount, 2) }}</span></div>
        @else
        <div style="display:flex;justify-content:space-between;"><span>Subtotal (excl. GST)</span><span>₹{{ number_format($base, 2) }}</span></div>
        @endif
        <div style="display:flex;justify-content:space-between;color:#6b7280;"><span>CGST ({{ $gst['cgst_pct'] }}%)</span><span>₹{{ number_format($cgstAmt, 2) }}</span></div>
        <div style="display:flex;justify-content:space-between;color:#6b7280;"><span>SGST ({{ $gst['sgst_pct'] }}%)</span><span>₹{{ number_format($sgstAmt, 2) }}</span></div>
        <div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid #bbf7d0;margin-top:6px;padding-top:6px;"><span>Grand Total</span><span>₹{{ number_format($grandTotal, 2) }}</span></div>
        <div style="font-size:11px;color:#6b7280;margin-top:2px;">GST {{ $gst['mode'] === 'included' ? 'included in price' : 'added on bill' }}</div>
    </div>
    @endif
    <div class="font-bold text-xl text-green-600 mb-4" data-order-total data-grand-total="{{ $grandTotal }}">Total: ₹{{ number_format($grandTotal, 2) }}</div>

                    <form action="{{ route('cashier.payments.process', $order) }}" method="POST" id="paymentForm{{ $order->id }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="grand_total" value="{{ $grandTotal }}">

                        <div class="mb-4">
                            <label class="block text-sm font-semibold mb-2">Payment Method</label>
                            <div class="grid gap-2" style="grid-template-columns: {{ $branchUpiId ? '1fr 1fr' : '1fr' }};">
                                <button type="button" onclick="selectPaymentMode({{ $order->id }}, 'cash')"
                                    class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500"
                                    data-order="{{ $order->id }}" data-mode="cash">💵 Cash</button>
                                @if($branchUpiId)
                                <button type="button" onclick="selectPaymentMode({{ $order->id }}, 'upi', {{ $grandTotal }}, '{{ $branchUpiId }}')"
                                    class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500"
                                    data-order="{{ $order->id }}" data-mode="upi">📱 UPI</button>
                                @endif
                            </div>
                            <input type="hidden" name="payment_mode" id="paymentMode{{ $order->id }}" required>
                        </div>

                        <div id="cashSection{{ $order->id }}" class="mb-4" style="display:none;">
                            <label class="block text-sm font-semibold mb-2">Cash Received</label>
                            <div class="flex gap-2">
                                <input type="number" step="0.01" min="0" id="cashReceived{{ $order->id }}"
                                    class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 text-lg" placeholder="Enter amount">
                                <button type="button" onclick="calculateChange({{ $order->id }}, {{ $grandTotal }})"
                                    class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">OK</button>
                            </div>
                        </div>

                        <div id="changeSection{{ $order->id }}" class="mb-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4" style="display:none;">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-1">Change to Return</p>
                                <p class="text-3xl font-bold text-green-600" id="changeAmount{{ $order->id }}">₹0.00</p>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn{{ $order->id }}"
                            class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg"
                            style="display:none;" disabled>Complete Payment</button>
                    </form>
                </div>
            </div>
        </div>


    @empty
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-4xl mb-2">✓</div>
            <p class="text-gray-600">All payments cleared!</p>
        </div>
    @endforelse
</div>

{{-- Bill QR Modal (shown after payment) --}}
<div id="qrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" style="display:none !important;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 mx-4 w-full max-w-sm text-center">
        <div class="text-green-500 text-5xl mb-2">✅</div>
        <h2 class="text-xl font-bold mb-1">Payment Complete!</h2>
        <p class="text-gray-500 text-sm mb-4">Customer can scan this QR to download their bill</p>
        <div class="bg-gray-50 rounded-xl p-4 mb-4 flex justify-center">
            <div id="qrCodeContainer"></div>
        </div>
        <p class="text-xs text-gray-400 mb-1">Or share this link:</p>
        <a id="billLink" href="#" target="_blank" class="text-blue-600 text-sm underline break-all block mb-4"></a>
        <div class="flex gap-2">
            <button onclick="closeQrModal()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg font-semibold">Close</button>
            <a id="openBillBtn" href="#" target="_blank" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold text-center">Open Bill</a>
        </div>
    </div>
</div>

{{-- UPI QR Modal (shown before payment confirmation) --}}
<div id="upiQrModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50" style="display:none;">
    <div class="bg-white rounded-2xl shadow-2xl p-6 mx-4 w-full max-w-sm text-center">
        <h2 class="text-xl font-bold mb-1">📱 UPI Payment</h2>
        <p class="text-gray-500 text-sm mb-1">Ask customer to scan with Google Pay / PhonePe</p>
        <div class="text-2xl font-bold text-green-600 mb-3" id="upiAmountDisplay"></div>
        <div class="bg-gray-50 rounded-xl p-4 mb-3 flex justify-center">
            <div id="upiQrContainer"></div>
        </div>
        <p class="text-xs text-gray-400 mb-4" id="upiIdDisplay"></p>
        <div class="flex gap-2">
            <button onclick="closeUpiQrModal()" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold">Cancel</button>
            <button onclick="confirmUpiPayment()" class="flex-1 bg-green-600 text-white py-2 rounded-lg font-semibold">✓ Payment Received</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const BILL_URLS = {
    @foreach($orders as $order)
    {{ $order->id }}: "{{ URL::signedRoute('bill.show', ['orderId' => $order->id]) }}",
    @endforeach
};

@if(session('bill_url') && request('paid_order'))
BILL_URLS[{{ request('paid_order') }}] = "{{ session('bill_url') }}";
@endif

// track which order is pending UPI confirmation
var upiPendingOrderId = null;

function selectPaymentMode(orderId, mode, amount, upiId) {
    document.querySelectorAll(`[data-order="${orderId}"]`).forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-300');
    });
    event.target.classList.remove('border-gray-300');
    event.target.classList.add('border-blue-500', 'bg-blue-50');

    document.getElementById(`paymentMode${orderId}`).value = mode;
    const cashSection   = document.getElementById(`cashSection${orderId}`);
    const changeSection = document.getElementById(`changeSection${orderId}`);
    const submitBtn     = document.getElementById(`submitBtn${orderId}`);

    if (mode === 'cash') {
        cashSection.style.display   = 'block';
        changeSection.style.display = 'none';
        submitBtn.style.display     = 'none';
        submitBtn.disabled          = true;
    } else if (mode === 'upi') {
        cashSection.style.display   = 'none';
        changeSection.style.display = 'none';
        submitBtn.style.display     = 'none';
        submitBtn.disabled          = true;
        showUpiQr(orderId, amount, upiId);
    }
}

function showUpiQr(orderId, amount, upiId) {
    upiPendingOrderId = orderId;

    // Build UPI deep-link URI — readable by Google Pay, PhonePe, Paytm etc.
    const upiUri = `upi://pay?pa=${encodeURIComponent(upiId)}&am=${parseFloat(amount).toFixed(2)}&cu=INR`;

    document.getElementById('upiAmountDisplay').textContent = '₹' + parseFloat(amount).toFixed(2);
    document.getElementById('upiIdDisplay').textContent = 'UPI ID: ' + upiId;

    const container = document.getElementById('upiQrContainer');
    container.innerHTML = '';
    new QRCode(container, {
        text: upiUri,
        width: 220,
        height: 220,
        colorDark: '#111827',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
    });

    document.getElementById('upiQrModal').style.display = 'flex';
}

function closeUpiQrModal() {
    document.getElementById('upiQrModal').style.display = 'none';
    // reset the UPI button selection
    if (upiPendingOrderId) {
        document.querySelectorAll(`[data-order="${upiPendingOrderId}"]`).forEach(btn => {
            btn.classList.remove('border-blue-500', 'bg-blue-50');
            btn.classList.add('border-gray-300');
        });
        document.getElementById(`paymentMode${upiPendingOrderId}`).value = '';
        upiPendingOrderId = null;
    }
}

function confirmUpiPayment() {
    document.getElementById('upiQrModal').style.display = 'none';
    if (!upiPendingOrderId) return;
    const submitBtn = document.getElementById(`submitBtn${upiPendingOrderId}`);
    submitBtn.style.display = 'block';
    submitBtn.disabled      = false;
    // auto-submit
    submitBtn.click();
}

function calculateChange(orderId, totalAmount) {
    const cashReceived = parseFloat(document.getElementById(`cashReceived${orderId}`).value);
    if (!cashReceived || cashReceived < totalAmount) {
        alert(`Cash received must be at least ₹${totalAmount.toFixed(2)}`);
        return;
    }
    const change = cashReceived - totalAmount;
    document.getElementById(`changeAmount${orderId}`).textContent = `₹${change.toFixed(2)}`;
    document.getElementById(`changeSection${orderId}`).style.display = 'block';
    document.getElementById(`submitBtn${orderId}`).style.display     = 'block';
    document.getElementById(`submitBtn${orderId}`).disabled          = false;
}

function showQrModal(orderId, billUrl) {
    billUrl = billUrl || BILL_URLS[orderId] || `{{ url('/bill') }}/${orderId}`;
    document.getElementById('billLink').textContent = billUrl;
    document.getElementById('billLink').href        = billUrl;
    document.getElementById('openBillBtn').href     = billUrl;
    const container = document.getElementById('qrCodeContainer');
    container.innerHTML = '';
    new QRCode(container, {
        text: billUrl, width: 200, height: 200,
        colorDark: '#111827', colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
    });
    const modal = document.getElementById('qrModal');
    modal.style.removeProperty('display');
    modal.style.display = 'flex';
}

function closeQrModal() {
    document.getElementById('qrModal').style.display = 'none';
    const url = new URL(window.location);
    url.searchParams.delete('paid_order');
    window.history.replaceState({}, '', url);
}

// ── AJAX payment submission ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const paidOrder = params.get('paid_order');
    if (paidOrder) showQrModal(paidOrder);

    document.querySelectorAll('[id^="paymentForm"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const orderId   = form.id.replace('paymentForm', '');
            const submitBtn = document.getElementById('submitBtn' + orderId);
            submitBtn.disabled    = true;
            submitBtn.textContent = 'Processing…';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) {
                    BILL_URLS[res.order_id] = res.bill_url;
                    const card = document.querySelector(`[data-order-id="${orderId}"]`);
                    if (card) {
                        card.style.transition = 'opacity .35s, transform .35s';
                        card.style.opacity    = '0';
                        card.style.transform  = 'scale(0.97)';
                        setTimeout(function () {
                            card.remove();
                            const count = document.getElementById('pendingCount');
                            if (count) count.textContent = document.querySelectorAll('[data-order-id]').length;
                            const cont = document.querySelector('.space-y-3');
                            if (cont && !cont.querySelector('[data-order-id]')) {
                                const empty = document.createElement('div');
                                empty.className = 'bg-white rounded-lg shadow p-8 text-center';
                                empty.innerHTML = '<div class="text-4xl mb-2">✓</div><p class="text-gray-600">All payments cleared!</p>';
                                cont.appendChild(empty);
                            }
                        }, 350);
                    }
                    showQrModal(res.order_id, res.bill_url);
                } else {
                    submitBtn.disabled    = false;
                    submitBtn.textContent = 'Complete Payment';
                    alert('Payment failed. Please try again.');
                }
            })
            .catch(function () {
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Complete Payment';
                alert('Network error. Please try again.');
            });
        });
    });
});

// ── Instant Mode toggle ──────────────────────────────────────────────────
var instantMode = localStorage.getItem('cashier_instant_mode') === '1';

function applyInstantUI() {
    var btn    = document.getElementById('instantToggle');
    var label  = document.getElementById('instantLabel');
    var banner = document.getElementById('instantBanner');
    if (instantMode) {
        btn.style.background    = '#7c3aed';
        btn.style.color         = '#fff';
        btn.style.borderColor   = '#7c3aed';
        label.textContent       = 'Instant: ON';
        banner.style.display    = 'block';
    } else {
        btn.style.background    = '#fff';
        btn.style.color         = '#6b7280';
        btn.style.borderColor   = '#d1d5db';
        label.textContent       = 'Instant Mode';
        banner.style.display    = 'none';
    }
}

function toggleInstantMode() {
    instantMode = !instantMode;
    localStorage.setItem('cashier_instant_mode', instantMode ? '1' : '0');
    applyInstantUI();
}

document.addEventListener('DOMContentLoaded', applyInstantUI);

// ── Polling for new orders ────────────────────────────────────────────────
(function () {
    var known = {};
    document.querySelectorAll('[data-order-id]').forEach(function (el) {
        known[el.dataset.orderId] = true;
    });

    var PAGE_UPI_ID = '{{ $branchUpiId }}';
    var PAGE_GST    = @json($branchGst);

    function buildPaymentCard(order) {
        var oid      = order.id;
        var subtotal = parseFloat(order.total_amount);
        var grand    = subtotal;
        var cgstAmt  = 0, sgstAmt = 0;
        var gstHtml  = '';

        if (PAGE_GST.enabled) {
            if (PAGE_GST.mode === 'excluded') {
                cgstAmt = Math.round(subtotal * PAGE_GST.cgst_pct) / 100;
                sgstAmt = Math.round(subtotal * PAGE_GST.sgst_pct) / 100;
                grand   = subtotal + cgstAmt + sgstAmt;
                gstHtml = '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px;">'
                    + '<div style="display:flex;justify-content:space-between;"><span>Subtotal</span><span>₹'+subtotal.toFixed(2)+'</span></div>'
                    + '<div style="display:flex;justify-content:space-between;color:#6b7280;"><span>CGST ('+PAGE_GST.cgst_pct+'%)</span><span>₹'+cgstAmt.toFixed(2)+'</span></div>'
                    + '<div style="display:flex;justify-content:space-between;color:#6b7280;"><span>SGST ('+PAGE_GST.sgst_pct+'%)</span><span>₹'+sgstAmt.toFixed(2)+'</span></div>'
                    + '<div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid #bbf7d0;margin-top:6px;padding-top:6px;"><span>Grand Total</span><span>₹'+grand.toFixed(2)+'</span></div>'
                    + '</div>';
            } else {
                var base = Math.round(subtotal * 100 / (100 + PAGE_GST.total_pct) * 100) / 100;
                cgstAmt  = Math.round(base * PAGE_GST.cgst_pct) / 100;
                sgstAmt  = Math.round(base * PAGE_GST.sgst_pct) / 100;
                grand    = subtotal;
                gstHtml  = '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px;">'
                    + '<div style="display:flex;justify-content:space-between;"><span>Subtotal (excl. GST)</span><span>₹'+base.toFixed(2)+'</span></div>'
                    + '<div style="display:flex;justify-content:space-between;color:#6b7280;"><span>CGST ('+PAGE_GST.cgst_pct+'%)</span><span>₹'+cgstAmt.toFixed(2)+'</span></div>'
                    + '<div style="display:flex;justify-content:space-between;color:#6b7280;"><span>SGST ('+PAGE_GST.sgst_pct+'%)</span><span>₹'+sgstAmt.toFixed(2)+'</span></div>'
                    + '<div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid #bbf7d0;margin-top:6px;padding-top:6px;"><span>Grand Total</span><span>₹'+grand.toFixed(2)+'</span></div>'
                    + '</div>';
            }
        }

        var label = order.is_parcel
            ? '<span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">📦 Parcel</span>'
            : '<span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;">T'+order.table_number+'</span>';

        var itemsHtml = order.items.map(function(item) {
            return '<div class="py-2 border-b">'
                + '<div class="flex justify-between items-center">'
                + '<div><span class="font-semibold">'+item.name+'</span>'
                + '<div class="text-sm text-gray-600">Qty: '+item.quantity+'</div></div>'
                + '<div class="font-bold">₹'+(item.price*item.quantity).toFixed(2)+'</div>'
                + '</div></div>';
        }).join('');

        var upiId  = order.upi_id || PAGE_UPI_ID;
        var upiBtn = upiId
            ? '<button type="button" onclick="selectPaymentMode('+oid+',\'upi\','+grand.toFixed(2)+',\''+upiId+'\')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold" data-order="'+oid+'" data-mode="upi">📱 UPI</button>'
            : '';
        var gridCols = upiId ? '1fr 1fr' : '1fr';

        var csrf = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('[name="_token"]')?.value || '';

        var div = document.createElement('div');
        div.className = 'bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-purple-600';
        div.style.cssText = 'opacity:0;transition:opacity .4s;';
        div.setAttribute('data-order-id', oid);
        div.setAttribute('data-order-status', order.status);
        div.innerHTML =
            '<div class="p-4">'
            + '<div class="flex justify-between items-start mb-3">'
            + '<div><h3 class="font-bold text-lg">Order #'+oid+'</h3>'
            + '<div class="flex items-center gap-2 mt-1">'+label+'</div>'
            + '<p class="text-xs text-gray-400" style="margin-top:3px;">'+order.created_at+'</p></div>'
            + '<span class="px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">'+order.status.charAt(0).toUpperCase()+order.status.slice(1)+'</span>'
            + '</div>'
            + '<div class="space-y-2 mb-4">'+itemsHtml+'</div>'
            + '<div class="pt-3 border-t">'
            + gstHtml
            + '<div class="font-bold text-xl text-green-600 mb-4" data-order-total data-grand-total="'+grand.toFixed(2)+'">Total: ₹'+grand.toFixed(2)+'</div>'
            + '<form action="{{ route('cashier.payments.process', ['order' => '__OID__']) }}".replace("__OID__",oid) method="POST" id="paymentForm'+oid+'">'
            + '<input type="hidden" name="_token" value="'+csrf+'">'
            + '<input type="hidden" name="_method" value="PATCH">'
            + '<input type="hidden" name="grand_total" value="'+grand.toFixed(2)+'">'
            + '<div class="mb-4"><label class="block text-sm font-semibold mb-2">Payment Method</label>'
            + '<div class="grid gap-2" style="grid-template-columns:'+gridCols+'">'
            + '<button type="button" onclick="selectPaymentMode('+oid+',\'cash\')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold" data-order="'+oid+'" data-mode="cash">💵 Cash</button>'
            + upiBtn
            + '</div><input type="hidden" name="payment_mode" id="paymentMode'+oid+'"></div>'
            + '<div id="cashSection'+oid+'" class="mb-4" style="display:none;"><label class="block text-sm font-semibold mb-2">Cash Received</label>'
            + '<div class="flex gap-2"><input type="number" step="0.01" min="0" id="cashReceived'+oid+'" class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 text-lg" placeholder="Enter amount">'
            + '<button type="button" onclick="calculateChange('+oid+','+grand.toFixed(2)+')" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">OK</button></div></div>'
            + '<div id="changeSection'+oid+'" class="mb-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4" style="display:none;"><div class="text-center"><p class="text-sm text-gray-600 mb-1">Change to Return</p><p class="text-3xl font-bold text-green-600" id="changeAmount'+oid+'">₹0.00</p></div></div>'
            + '<button type="submit" id="submitBtn'+oid+'" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg" style="display:none;" disabled>Complete Payment</button>'
            + '</form></div></div>';

        // Attach submit handler
        div.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = document.getElementById('submitBtn'+oid);
            if (!document.getElementById('paymentMode'+oid).value) { alert('Please select a payment method'); return; }
            submitBtn.disabled = true; submitBtn.textContent = 'Processing…';
            fetch(e.target.action, {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body: new FormData(e.target)
            })
            .then(function(r){return r.json();})
            .then(function(res) {
                if (res.success) {
                    BILL_URLS[res.order_id] = res.bill_url;
                    var card = document.querySelector('[data-order-id="'+oid+'"]');
                    if (card) {
                        card.style.transition='opacity .35s,transform .35s';
                        card.style.opacity='0'; card.style.transform='scale(0.97)';
                        setTimeout(function(){
                            card.remove();
                            delete known[String(oid)];
                            var count = document.getElementById('pendingCount');
                            if (count) count.textContent = document.querySelectorAll('[data-order-id]').length;
                            checkEmpty();
                        }, 350);
                    }
                    showQrModal(res.order_id, res.bill_url);
                } else {
                    submitBtn.disabled=false; submitBtn.textContent='Complete Payment';
                    alert(res.message||'Payment failed.');
                }
            })
            .catch(function(){ submitBtn.disabled=false; submitBtn.textContent='Complete Payment'; alert('Network error.'); });
        });

        return div;
    }

    function checkEmpty() {
        var cont = document.querySelector('.space-y-3');
        if (cont && !cont.querySelector('[data-order-id]')) {
            var empty = document.createElement('div');
            empty.className = 'bg-white rounded-lg shadow p-8 text-center';
            empty.innerHTML = '<div class="text-4xl mb-2">✓</div><p class="text-gray-600">All payments cleared!</p>';
            cont.appendChild(empty);
        }
    }

    // Persistent notification banner
    var notifQueue = [], notifVisible = false;
    function showNotif(msg, color) {
        notifQueue.push({msg, color});
        if (!notifVisible) flushNotif();
    }
    function flushNotif() {
        if (!notifQueue.length) { notifVisible = false; return; }
        notifVisible = true;
        var n = notifQueue.shift();
        try {
            var ctx = new (window.AudioContext||window.webkitAudioContext)();
            var osc = ctx.createOscillator(), gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.4, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime+0.6);
            osc.start(ctx.currentTime); osc.stop(ctx.currentTime+0.6);
        } catch(e) {}
        var banner = document.createElement('div');
        banner.style.cssText = 'position:fixed;top:0;left:0;right:0;background:'+(n.color||'#dc2626')+';color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;z-index:99999;box-shadow:0 4px 20px rgba(0,0,0,.4);animation:slideDown .3s ease;';
        banner.innerHTML = '<div style="display:flex;align-items:center;gap:12px;font-size:16px;font-weight:700;"><span style="font-size:24px;">🔔</span>'+n.msg+'</div>'
            + '<button onclick="this.parentElement.remove();" style="background:rgba(255,255,255,.25);border:none;color:#fff;border-radius:6px;padding:4px 12px;font-size:13px;font-weight:700;cursor:pointer;flex-shrink:0;">Dismiss</button>';
        var timer = setTimeout(function(){ if(banner.parentElement) banner.remove(); setTimeout(flushNotif,300); }, 12000);
        banner.querySelector('button').addEventListener('click', function(){ clearTimeout(timer); setTimeout(flushNotif,300); });
        document.body.prepend(banner);
    }
    if (!document.getElementById('cashierNotifStyle')) {
        var s = document.createElement('style'); s.id='cashierNotifStyle';
        s.textContent='@keyframes slideDown{from{transform:translateY(-100%)}to{transform:translateY(0)}}';
        document.head.appendChild(s);
    }

    function poll() {
        var panel = instantMode ? 'cashier_instant' : 'cashier';
        fetch('/api/order-updates?panel='+panel, {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.ok?r.json():null;})
        .then(function(data){
            if (!data||!data.orders) return;
            data.orders.forEach(function(order){
                var oid = String(order.id);
                if (!known[oid]) {
                    known[oid] = true;
                    var label = order.is_parcel ? '📦 Parcel' : 'Table '+order.table_number;
                    if (instantMode) {
                        // Build full payment card and inject it
                        var cont = document.querySelector('.space-y-3');
                        if (cont) {
                            var empty = cont.querySelector('.bg-white:not([data-order-id])');
                            if (empty) empty.remove();
                            var card = buildPaymentCard(order);
                            cont.insertBefore(card, cont.firstChild);
                            requestAnimationFrame(function(){ card.style.opacity='1'; });
                        }
                        var count = document.getElementById('pendingCount');
                        if (count) count.textContent = document.querySelectorAll('[data-order-id]').length;
                        showNotif('⚡ New Order #'+order.id+' — '+label+' | ₹'+parseFloat(order.total_amount).toFixed(2), '#7c3aed');
                    } else {
                        showNotif('New Order #'+order.id+' — '+label+' | ₹'+parseFloat(order.total_amount).toFixed(2), '#dc2626');
                        var count = document.getElementById('pendingCount');
                        if (count) count.textContent = Object.keys(known).length;
                    }
                }
            });
            Object.keys(known).forEach(function(oid){
                if (!data.orders.find(function(o){return String(o.id)===oid;})) delete known[oid];
            });
        })
        .catch(function(){});
    }

    setInterval(poll, 5000);
    setTimeout(poll, 2000);
})();
</script>

@endsection
