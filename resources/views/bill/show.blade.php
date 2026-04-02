<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $order->daily_number ?? $order->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #e5e5e5;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
        }

        .action-bar {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
            width: 100%;
            max-width: 320px;
        }
        .action-bar button {
            flex: 1;
            padding: 9px;
            font-size: 13px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-print    { background: #333; color: #fff; }
        .btn-download { background: #555; color: #fff; }

        .receipt {
            background: #fff;
            width: 100%;
            max-width: 320px;
            padding: 18px 16px;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
        }

        .center { text-align: center; }
        .bold   { font-weight: bold; }
        .large  { font-size: 15px; }
        .small  { font-size: 11px; }

        .divider-solid  { border-top: 1px solid #000; margin: 6px 0; }
        .divider-dashed { border-top: 1px dashed #000; margin: 6px 0; }

        .row {
            display: flex;
            justify-content: space-between;
        }
        .row .left  { flex: 1; }
        .row .right { white-space: nowrap; padding-left: 8px; }

        .item-name { word-break: break-word; }
        .item-sub  { font-size: 11px; padding-left: 2px; }
        .cancelled-row { opacity: 0.5; text-decoration: line-through; }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1px 0;
        }
        .total-row.grand {
            font-size: 14px;
            font-weight: bold;
            padding-top: 4px;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .action-bar { display: none !important; }
            .receipt { max-width: 100%; box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <button class="btn-print" onclick="window.print()">🖨 Print</button>
    <button class="btn-download" onclick="window.print()">⬇ Save PDF</button>
</div>

<div class="receipt">

    {{-- Header --}}
    <div class="center bold large">{{ strtoupper($order->tenant->name ?? 'RESTAURANT') }}</div>
    @if($order->branch?->name)
        <div class="center small">{{ $order->branch->name }}</div>
    @endif
    @if($order->branch?->address)
        <div class="center small">{{ $order->branch->address }}</div>
    @endif
    @if($order->branch?->phone)
        <div class="center small">Ph: {{ $order->branch->phone }}</div>
    @endif
    @if($order->branch?->gst_number)
        <div class="center small" style="font-weight:bold;">GSTIN: {{ $order->branch->gst_number }}</div>
    @endif
    @if($order->tenant->domain ?? false)
        <div class="center small">{{ $order->tenant->domain }}</div>
    @endif
    <div class="center" style="margin-top:4px; letter-spacing:2px;">* RECEIPT *</div>

    <div class="divider-dashed"></div>

    {{-- Order meta --}}
    <div class="row"><span>Order #</span><span>{{ $order->daily_number ?? $order->id }}</span></div>
    <div class="row"><span>{{ $order->is_parcel ? 'Type' : 'Table' }}</span><span>{{ $order->is_parcel ? '📦 Parcel' : ($order->table?->table_number ?? 'N/A') }}</span></div>
    <div class="row"><span>Date</span><span>{{ $order->paid_at->format('d/m/Y') }}</span></div>
    <div class="row"><span>Time</span><span>{{ $order->paid_at->format('h:i A') }}</span></div>

    <div class="divider-dashed"></div>

    {{-- Column header --}}
    <div class="row bold small">
        <span class="left">ITEM</span>
        <span>QTY</span>
        <span class="right">AMT</span>
    </div>
    <div class="divider-solid"></div>

    {{-- Items --}}
    @foreach($order->orderItems->where('status', '!=', 'cancelled') as $item)
        <div style="margin: 3px 0;">
            <div class="row">
                <span class="left item-name">{{ $item->menuItem?->name ?? '[Deleted Item]' }}</span>
                <span style="padding: 0 8px;">{{ $item->quantity }}</span>
                <span class="right">₹{{ number_format($item->price * $item->quantity, 2) }}</span>
            </div>
            <div class="item-sub" style="color:#444;">{{ $item->quantity }} x ₹{{ number_format($item->price, 2) }}</div>
            @if($item->notes)
                <div class="item-sub" style="color:#666;">* {{ $item->notes }}</div>
            @endif
        </div>
    @endforeach

    <div class="divider-solid"></div>

    {{-- Totals --}}
    @if($gst['enabled'] ?? false)
        <div class="total-row">
            <span>Subtotal</span>
            <span>₹{{ number_format($gst['base'], 2) }}</span>
        </div>
        <div class="total-row small">
            <span>CGST ({{ $gst['cgst_pct'] }}%)</span>
            <span>₹{{ number_format($gst['cgst'], 2) }}</span>
        </div>
        <div class="total-row small">
            <span>SGST ({{ $gst['sgst_pct'] }}%)</span>
            <span>₹{{ number_format($gst['sgst'], 2) }}</span>
        </div>
        <div class="divider-solid"></div>
        <div class="total-row grand">
            <span>TOTAL</span>
            <span>₹{{ number_format($gst['grand'], 2) }}</span>
        </div>
        <div class="total-row small" style="margin-top:2px;color:#555;">
            <span>GST {{ $gst['mode'] === 'included' ? '(incl. in price)' : '(added on bill)' }}</span>
            <span>₹{{ number_format($gst['cgst'] + $gst['sgst'], 2) }}</span>
        </div>
    @else
        <div class="total-row grand">
            <span>TOTAL</span>
            <span>₹{{ number_format($order->total_amount, 2) }}</span>
        </div>
    @endif
    <div class="total-row small" style="margin-top:2px;">
        <span>Payment</span>
        <span>{{ strtoupper($order->payment_mode ?? 'CASH') }}</span>
    </div>

    @if($order->customer_notes)
        <div class="divider-dashed"></div>
        <div class="small" style="font-style:italic;">Note: {{ $order->customer_notes }}</div>
    @endif

    <div class="divider-dashed"></div>

    {{-- Bill QR Code --}}
    @php
        $billUrl = request()->fullUrl();
        $qrSvg   = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(160)->margin(1)->generate($billUrl));
    @endphp
    <div class="center" style="margin:10px 0 4px;">
        <img src="data:image/svg+xml;base64,{{ $qrSvg }}" width="160" height="160" alt="Bill QR" style="display:inline-block;">
    </div>
    <div class="center small" style="margin-bottom:4px;">Scan to view &amp; download this bill</div>

    <div class="divider-dashed"></div>

    {{-- Footer --}}
    <div class="center bold" style="margin-top:4px;">*** THANK YOU ***</div>
    <div class="center small" style="margin-top:2px;">Please visit again!</div>

</div>

</body>
</html>
