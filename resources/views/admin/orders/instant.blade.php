@extends('layouts.admin')
@php use Illuminate\Support\Facades\URL; @endphp
@section('title', 'Instant Mode')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4" style="flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="section-title" style="color:#7c3aed;">
            <i class="fas fa-bolt me-2"></i>Instant Mode
        </h1>
        <p style="font-size:13px;color:#6b7280;">Orders go straight to billing — no kitchen flow.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        @if($branches->count() > 0)
        <form method="GET" action="{{ route('admin.orders.instant') }}" style="display:flex;align-items:center;gap:8px;">
            <select name="branch_id" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;min-width:160px;">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        <a href="{{ route('admin.orders.instant.create', $branchId ? ['branch_id' => $branchId] : []) }}"
           style="display:flex;align-items:center;gap:8px;background:#7c3aed;color:#fff;padding:9px 18px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;">
            <i class="fas fa-plus"></i> New Instant Order
        </a>
        <a href="{{ route('admin.orders.index', $branchId ? ['branch_id' => $branchId] : []) }}"
           style="display:flex;align-items:center;gap:8px;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:9px 18px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Normal Mode
        </a>
    </div>
</div>

{{-- Info banner --}}
<div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:10px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:13px;color:#5b21b6;">
    <i class="fas fa-bolt" style="font-size:18px;"></i>
    <span><strong>Instant Mode:</strong> When you create an order here, it skips the kitchen flow and goes directly to the payment section for immediate billing.</span>
</div>

{{-- Payments section --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="font-size:17px;font-weight:700;color:var(--gray-800);">
        Pending Payments (<span id="instantPaymentCount">{{ $paymentOrders->count() }}</span>)
    </h2>
    <button onclick="location.reload()" style="display:flex;align-items:center;gap:6px;background:var(--gray-100);border:1px solid var(--gray-300);color:var(--gray-700);padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
        <i class="fas fa-rotate-right"></i> Refresh
    </button>
</div>

<div id="instantPaymentList" style="display:flex;flex-direction:column;gap:16px;">
@forelse($paymentOrders as $order)
    <div class="content-card" style="border-left:4px solid #7c3aed;padding:0;"
         data-payment-order-id="{{ $order->id }}"
         data-payment-status="{{ $order->status }}">
        <div style="padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
                <div>
                    <div style="font-size:17px;font-weight:700;">Order #{{ $order->id }}</div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                        @if($order->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;"><i class="fas fa-box"></i> Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;">T{{ $order->table?->table_number }}</span>
                            @if($order->table?->category)
                                <span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;">{{ $order->table->category->name }}</span>
                            @endif
                        @endif
                    </div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:3px;">{{ $order->created_at->format('h:i A') }}</div>
                </div>
                <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;background:#f5f3ff;color:#7c3aed;">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div style="margin-bottom:14px;">
                @foreach($order->orderItems as $item)
                <div style="padding:7px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <span style="font-size:13px;font-weight:500;{{ $item->status === 'cancelled' ? 'text-decoration:line-through;color:#9ca3af;' : '' }}">{{ $item->menuItem?->name ?? '[Deleted]' }}</span>
                        <div style="font-size:12px;color:#6b7280;">Qty: {{ $item->quantity }}</div>
                    </div>
                    <div style="font-weight:700;">₹{{ number_format($item->price * $item->quantity, 2) }}</div>
                </div>
                @endforeach
            </div>

            @if($order->customer_notes)
            <div style="background:#fffbeb;border-left:4px solid #fbbf24;padding:10px 12px;border-radius:4px;margin-bottom:14px;">
                <div style="font-size:12px;font-weight:600;color:#92400e;margin-bottom:2px;"><i class="fas fa-info-circle me-1"></i>Customer Request:</div>
                <div style="font-size:13px;color:#78350f;font-style:italic;">{{ $order->customer_notes }}</div>
            </div>
            @endif

            <div style="border-top:1px solid #e5e7eb;padding-top:14px;">
            @php
                $gst = $branchGst;
                $orderBranchUpiId = $order->branch?->upi_id ?? $branchUpiId;
                if (!$branchId && $order->branch) {
                    $ob = $order->branch; $os = $ob->gstSlab; $om = $ob->gst_mode;
                    $gst = ($os && $om) ? ['enabled'=>true,'mode'=>$om,'cgst_pct'=>(float)$os->cgst_rate,'sgst_pct'=>(float)$os->sgst_rate,'total_pct'=>(float)($os->cgst_rate+$os->sgst_rate)] : ['enabled'=>false];
                }
                $grandTotal = $order->total_amount;
                if ($gst['enabled'] && $gst['mode'] === 'excluded') {
                    $cgstAmt = round($order->total_amount * $gst['cgst_pct'] / 100, 2);
                    $sgstAmt = round($order->total_amount * $gst['sgst_pct'] / 100, 2);
                    $grandTotal = $order->total_amount + $cgstAmt + $sgstAmt;
                } elseif ($gst['enabled'] && $gst['mode'] === 'included') {
                    $base = round($order->total_amount * 100 / (100 + $gst['total_pct']), 2);
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
            </div>
            @endif
            <div style="font-size:20px;font-weight:700;color:#16a34a;margin-bottom:14px;" data-grand-total="{{ $grandTotal }}">Total: ₹{{ number_format($grandTotal, 2) }}</div>

            <form action="{{ route('admin.orders.payment', $order->id) }}" method="POST" id="iPayForm{{ $order->id }}">
                @csrf @method('PATCH')
                <input type="hidden" name="grand_total" value="{{ $grandTotal }}">
                <div style="margin-bottom:12px;">
                    <div style="font-size:13px;font-weight:600;margin-bottom:8px;">Payment Method</div>
                    <div style="display:grid;grid-template-columns:{{ $orderBranchUpiId ? 'repeat(2,1fr)' : '1fr' }};gap:8px;">
                        <button type="button" onclick="iSelectMode({{ $order->id }},'cash')" class="i-pay-mode-btn" data-order="{{ $order->id }}"
                            style="padding:13px 6px;border:2px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;">💵 Cash</button>
                        @if($orderBranchUpiId)
                        <button type="button" onclick="iSelectMode({{ $order->id }},'upi',{{ $grandTotal }},'{{ $orderBranchUpiId }}')" class="i-pay-mode-btn" data-order="{{ $order->id }}"
                            style="padding:13px 6px;border:2px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;"><i class="fas fa-mobile-alt"></i> UPI</button>
                        @endif
                    </div>
                    <input type="hidden" name="payment_mode" id="iPayMode{{ $order->id }}">
                </div>
                <div id="iCashSec{{ $order->id }}" style="display:none;margin-bottom:12px;">
                    <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Cash Received</div>
                    <div style="display:flex;gap:8px;">
                        <input type="number" step="0.01" min="0" id="iCashAmt{{ $order->id }}"
                            style="flex:1;border:2px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:15px;" placeholder="Enter amount">
                        <button type="button" onclick="iCalcChange({{ $order->id }},{{ $grandTotal }})"
                            style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-weight:600;cursor:pointer;">OK</button>
                    </div>
                </div>
                <div id="iChangeSec{{ $order->id }}" style="display:none;background:#fffbeb;border:2px solid #fbbf24;border-radius:8px;padding:12px;text-align:center;margin-bottom:12px;">
                    <div style="font-size:12px;color:#92400e;font-weight:600;margin-bottom:4px;">Change to Return</div>
                    <div id="iChangeAmt{{ $order->id }}" style="font-size:24px;font-weight:700;color:#b45309;">₹0.00</div>
                </div>
                <button type="submit" id="iSubmitBtn{{ $order->id }}"
                    style="display:none;width:100%;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;"
                    disabled>Complete Payment</button>
            </form>
            </div>
        </div>
    </div>
@empty
    <div style="background:#fff;border-radius:12px;padding:48px;text-align:center;border:1px solid #e5e7eb;">
        <div style="font-size:40px;margin-bottom:8px;">⚡</div>
        <p style="color:#6b7280;">No instant orders yet. Create one above.</p>
    </div>
@endforelse
</div>

{{-- QR Modal --}}
<div id="iQrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9998;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:360px;margin:auto;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
        <div style="color:#16a34a;font-size:48px;margin-bottom:8px;">✓</div>
        <h5 style="font-weight:700;margin-bottom:4px;">Payment Complete!</h5>
        <p style="font-size:13px;color:#666;margin-bottom:16px;">Customer can scan to view their bill</p>
        <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:16px;">
            <div id="iQrContainer"></div>
        </div>
        <a id="iQrBillLink" href="#" target="_blank" style="font-size:13px;color:#2563eb;word-break:break-all;display:block;margin-bottom:16px;"></a>
        <div style="display:flex;gap:8px;">
            <button onclick="closeIQr()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Close</button>
            <a id="iQrOpenBtn" href="#" target="_blank" style="flex:1;background:#2563eb;color:#fff;border-radius:8px;padding:10px;font-weight:600;text-decoration:none;display:inline-block;">Open Bill</a>
        </div>
    </div>
</div>

{{-- UPI QR Modal --}}
<div id="iUpiModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:360px;margin:auto;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
        <h5 style="font-weight:700;margin-bottom:4px;"><i class="fas fa-mobile-alt"></i> UPI Payment</h5>
        <p style="font-size:13px;color:#666;margin-bottom:6px;">Ask customer to scan with Google Pay / PhonePe</p>
        <div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px;" id="iUpiAmount"></div>
        <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:12px;">
            <div id="iUpiQrContainer"></div>
        </div>
        <p style="font-size:12px;color:#9ca3af;margin-bottom:16px;" id="iUpiId"></p>
        <div style="display:flex;gap:8px;">
            <button onclick="closeIUpi()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Cancel</button>
            <button onclick="confirmIUpi()" style="flex:1;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">✓ Payment Received</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
var iUpiPendingId = null;

function iSelectMode(orderId, mode, amount, upiId) {
    document.querySelectorAll('[data-order="'+orderId+'"].i-pay-mode-btn').forEach(function(b){
        b.style.borderColor='#d1d5db'; b.style.background='#fff';
    });
    event.target.style.borderColor='#7c3aed'; event.target.style.background='#f5f3ff';
    document.getElementById('iPayMode'+orderId).value = mode;
    var cashSec   = document.getElementById('iCashSec'+orderId);
    var changeSec = document.getElementById('iChangeSec'+orderId);
    var submitBtn = document.getElementById('iSubmitBtn'+orderId);
    if (mode === 'cash') {
        cashSec.style.display='block'; changeSec.style.display='none';
        submitBtn.style.display='none'; submitBtn.disabled=true;
    } else if (mode === 'upi') {
        cashSec.style.display='none'; changeSec.style.display='none';
        submitBtn.style.display='none'; submitBtn.disabled=true;
        iShowUpi(orderId, amount, upiId);
    }
}

function iShowUpi(orderId, amount, upiId) {
    iUpiPendingId = orderId;
    var uri = 'upi://pay?pa='+encodeURIComponent(upiId)+'&am='+parseFloat(amount).toFixed(2)+'&cu=INR';
    document.getElementById('iUpiAmount').textContent = '₹'+parseFloat(amount).toFixed(2);
    document.getElementById('iUpiId').textContent = 'UPI ID: '+upiId;
    var c = document.getElementById('iUpiQrContainer'); c.innerHTML='';
    new QRCode(c,{text:uri,width:220,height:220,colorDark:'#111827',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
    document.getElementById('iUpiModal').style.display='flex';
}
function closeIUpi() {
    document.getElementById('iUpiModal').style.display='none';
    if (iUpiPendingId) {
        document.querySelectorAll('[data-order="'+iUpiPendingId+'"].i-pay-mode-btn').forEach(function(b){b.style.borderColor='#d1d5db';b.style.background='#fff';});
        document.getElementById('iPayMode'+iUpiPendingId).value='';
        iUpiPendingId=null;
    }
}
function confirmIUpi() {
    document.getElementById('iUpiModal').style.display='none';
    if (!iUpiPendingId) return;
    var btn = document.getElementById('iSubmitBtn'+iUpiPendingId);
    btn.style.display='block'; btn.disabled=false; btn.click();
}
function iCalcChange(orderId, total) {
    var cash = parseFloat(document.getElementById('iCashAmt'+orderId).value);
    if (!cash || cash < total) { alert('Cash must be at least ₹'+total.toFixed(2)); return; }
    document.getElementById('iChangeAmt'+orderId).textContent = '₹'+(cash-total).toFixed(2);
    document.getElementById('iChangeSec'+orderId).style.display='block';
    document.getElementById('iSubmitBtn'+orderId).style.display='block';
    document.getElementById('iSubmitBtn'+orderId).disabled=false;
}
function showIQr(orderId, billUrl) {
    document.getElementById('iQrBillLink').textContent=billUrl;
    document.getElementById('iQrBillLink').href=billUrl;
    document.getElementById('iQrOpenBtn').href=billUrl;
    var c=document.getElementById('iQrContainer'); c.innerHTML='';
    new QRCode(c,{text:billUrl,width:200,height:200,colorDark:'#111827',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});
    document.getElementById('iQrModal').style.display='flex';
}
function closeIQr() { document.getElementById('iQrModal').style.display='none'; }

// AJAX payment submit
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[id^="iPayForm"]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var orderId = form.id.replace('iPayForm','');
            var submitBtn = document.getElementById('iSubmitBtn'+orderId);
            if (!document.getElementById('iPayMode'+orderId).value) { alert('Please select a payment method'); return; }
            submitBtn.disabled=true; submitBtn.textContent='Processing...';
            fetch(form.action, {
                method:'POST',
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
                body: new FormData(form)
            })
            .then(function(r){return r.json();})
            .then(function(res) {
                if (res.success) {
                    var card = document.querySelector('[data-payment-order-id="'+orderId+'"]');
                    if (card) {
                        card.style.transition='opacity .35s,transform .35s';
                        card.style.opacity='0'; card.style.transform='scale(0.97)';
                        setTimeout(function(){
                            card.remove();
                            var n = document.querySelectorAll('[data-payment-order-id]').length;
                            document.getElementById('instantPaymentCount').textContent = n;
                            if (!n) {
                                document.getElementById('instantPaymentList').innerHTML =
                                    '<div style="background:#fff;border-radius:12px;padding:48px;text-align:center;border:1px solid #e5e7eb;"><div style="font-size:40px;margin-bottom:8px;">⚡</div><p style="color:#6b7280;">No instant orders yet. Create one above.</p></div>';
                            }
                        }, 350);
                    }
                    showIQr(res.order_id, res.bill_url);
                } else {
                    submitBtn.disabled=false; submitBtn.textContent='Complete Payment';
                    alert(res.message||'Payment failed.');
                }
            })
            .catch(function(){ submitBtn.disabled=false; submitBtn.textContent='Complete Payment'; alert('Network error.'); });
        });
    });
});

// Polling — show new instant orders as they arrive
(function() {
    var known = {};
    document.querySelectorAll('[data-payment-order-id]').forEach(function(el){ known[el.dataset.paymentOrderId]=true; });
    var branchId = {!! json_encode($branchId) !!};
    var pollUrl = '/api/order-updates?panel=admin_waiter' + (branchId ? '&branch_id='+branchId : '');

    function toast(msg) {
        var el=document.createElement('div');
        el.style.cssText='position:fixed;top:16px;left:50%;transform:translateX(-50%);background:#7c3aed;color:#fff;padding:12px 24px;border-radius:10px;font-size:15px;font-weight:700;box-shadow:0 4px 16px rgba(0,0,0,.3);z-index:99999;white-space:nowrap;pointer-events:none;';
        el.textContent=msg; document.body.appendChild(el);
        setTimeout(function(){el.remove();},5000);
    }

    function poll() {
        fetch(pollUrl,{headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(function(r){return r.ok?r.json():null;})
        .then(function(data){
            if (!data) return;
            // Show payment_orders (checkout status) that are new
            (data.payment_orders||[]).forEach(function(order){
                var oid=String(order.id);
                if (!known[oid]) {
                    known[oid]=true;
                    toast('⚡ New Order #'+order.id+' ready for payment!');
                    document.getElementById('instantPaymentCount').textContent=Object.keys(known).length;
                    // Reload to show the new card with full billing UI
                    setTimeout(function(){ location.reload(); }, 800);
                }
            });
        })
        .catch(function(){});
    }
    setInterval(poll, 5000);
    setTimeout(poll, 2000);
})();
</script>
@endsection
