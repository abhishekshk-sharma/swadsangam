@extends('layouts.manager')
@section('title', 'Reports')
@section('content')

@php
    $gstStats      = $gstStats ?? ['enabled' => false];
    $paymentTotals = $paymentTotals ?? ['cash' => 0, 'upi' => 0, 'card' => 0];
    $filterLabel   = match(request('filter_type')) {
        'month'  => 'Month: ' . (request('month') ? \Carbon\Carbon::parse(request('month').'-01')->format('F Y') : ''),
        'year'   => 'Year: ' . request('year'),
        'custom' => request('date_from') . ' to ' . request('date_to'),
        default  => ''
    };
    $activeMode = request('payment_mode', 'all');
@endphp

{{-- Filter Card --}}
<div class="content-card" style="margin-bottom:20px;">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:12px 12px 0 0;">
        <strong style="font-size:14px;color:#374151;"><i class="fas fa-filter" style="margin-right:8px;color:#3b82f6;"></i>Filter Orders</strong>
    </div>
    <div style="padding:20px;">
        <form method="GET" action="{{ route('manager.reports.index') }}" id="filterForm">

            {{-- Period tabs --}}
            <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
                @foreach(['month'=>'Monthly','year'=>'Yearly','custom'=>'Custom Range'] as $val=>$label)
                <button type="button" onclick="setFilterType('{{ $val }}')" id="ftab-{{ $val }}"
                    style="padding:8px 20px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:2px solid {{ request('filter_type')===$val ? '#2563eb' : '#d1d5db' }};background:{{ request('filter_type')===$val ? '#eff6ff' : '#fff' }};color:{{ request('filter_type')===$val ? '#2563eb' : '#6b7280' }};transition:all 0.15s;">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <input type="hidden" name="filter_type" id="filterType" value="{{ request('filter_type') }}">

            <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">

                {{-- Monthly --}}
                <div style="display:{{ request('filter_type')==='month' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:160px;" id="panel-month">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Select Month</label>
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                           style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                </div>

                {{-- Yearly --}}
                <div style="display:{{ request('filter_type')==='year' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;" id="panel-year">
                    <label style="font-size:12px;font-weight:600;color:#374151;">Select Year</label>
                    <select name="year" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                        @for($y = now()->year; $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Custom Range --}}
                <div style="display:{{ request('filter_type')==='custom' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;" id="panel-custom-from">
                    <label style="font-size:12px;font-weight:600;color:#374151;">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                </div>
                <div style="display:{{ request('filter_type')==='custom' ? 'flex' : 'none' }};flex-direction:column;gap:6px;min-width:140px;" id="panel-custom-to">
                    <label style="font-size:12px;font-weight:600;color:#374151;">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;background:#fff;color:#374151;">
                </div>

                    <div style="display:flex;gap:8px;align-items:center;">
                    <button type="submit" style="padding:9px 22px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">Apply</button>
                    <a href="{{ route('manager.reports.index') }}" style="padding:9px 22px;background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">Clear</a>
                </div>
            </div>

            {{-- Payment Mode Filter --}}
            <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e5e7eb;">
                <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:10px;">Payment Mode</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach(['all' => 'All Modes', 'cash' => '💵 Cash', 'upi' => '📱 UPI', 'card' => '💳 Card'] as $modeVal => $modeLabel)
                    <button type="button" onclick="setPaymentMode('{{ $modeVal }}')" id="pmode-{{ $modeVal }}"
                        style="padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:2px solid {{ $activeMode === $modeVal ? '#2563eb' : '#d1d5db' }};background:{{ $activeMode === $modeVal ? '#eff6ff' : '#fff' }};color:{{ $activeMode === $modeVal ? '#2563eb' : '#6b7280' }};transition:all 0.15s;">
                        {{ $modeLabel }}
                    </button>
                    @endforeach
                </div>
                <input type="hidden" name="payment_mode" id="paymentMode" value="{{ $activeMode }}">
                <div style="font-size:11px;color:#9ca3af;margin-top:8px;">Select a mode then click Apply to filter.</div>
            </div>
        </form>
    </div>
</div>

@if(request()->filled('filter_type'))

{{-- Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;margin-bottom:16px;">
    <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e5e7eb;border-left:4px solid #4facfe;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Total Orders</div>
        <div style="font-size:30px;font-weight:700;color:#111827;">{{ $totalOrders }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e5e7eb;border-left:4px solid #43e97b;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:6px;">Total Revenue (Paid)</div>
        <div style="font-size:30px;font-weight:700;color:#111827;">₹{{ number_format($totalRevenue, 2) }}</div>
    </div>
</div>

{{-- Payment mode breakdown --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px;">
    <div style="background:#fff;border-radius:12px;padding:16px 20px;border:1px solid #e5e7eb;border-left:4px solid #16a34a;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <span style="font-size:18px;">💵</span>
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">Cash</span>
        </div>
        <div style="font-size:22px;font-weight:700;color:#15803d;">₹{{ number_format($paymentTotals['cash'], 2) }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px 20px;border:1px solid #e5e7eb;border-left:4px solid #7c3aed;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <span style="font-size:18px;">📱</span>
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">UPI</span>
        </div>
        <div style="font-size:22px;font-weight:700;color:#6d28d9;">₹{{ number_format($paymentTotals['upi'], 2) }}</div>
    </div>
    <div style="background:#fff;border-radius:12px;padding:16px 20px;border:1px solid #e5e7eb;border-left:4px solid #0284c7;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <span style="font-size:18px;">💳</span>
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;">Card</span>
        </div>
        <div style="font-size:22px;font-weight:700;color:#0369a1;">₹{{ number_format($paymentTotals['card'], 2) }}</div>
    </div>
</div>

@if($gstStats['enabled'] ?? false)
<div style="background:#fff;border-radius:12px;padding:16px 20px;border:1px solid #e5e7eb;border-left:4px solid #f59e0b;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">GST Collected</div>
        <div style="font-size:22px;font-weight:700;color:#b45309;">₹{{ number_format($gstStats['total'], 2) }}</div>
    </div>
    <div style="display:flex;gap:24px;">
        <div style="text-align:center;">
            <div style="font-size:11px;color:#6b7280;font-weight:600;">CGST</div>
            <div style="font-size:16px;font-weight:700;color:#92400e;">₹{{ number_format($gstStats['cgst'], 2) }}</div>
        </div>
        <div style="text-align:center;">
            <div style="font-size:11px;color:#6b7280;font-weight:600;">SGST</div>
            <div style="font-size:16px;font-weight:700;color:#92400e;">₹{{ number_format($gstStats['sgst'], 2) }}</div>
        </div>
    </div>
</div>
@endif

{{-- Orders table --}}
<div class="content-card">
    <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:15px;font-weight:600;color:#1f2937;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-table" style="color:#3b82f6;"></i> Orders Report
            @if($filterLabel)<span style="font-size:12px;font-weight:400;color:#6b7280;">— {{ $filterLabel }}</span>@endif
        </div>
        <form method="GET" action="{{ route('manager.reports.export') }}">
            <input type="hidden" name="filter_type"  value="{{ request('filter_type') }}">
            <input type="hidden" name="month"        value="{{ request('month') }}">
            <input type="hidden" name="year"         value="{{ request('year') }}">
            <input type="hidden" name="date_from"    value="{{ request('date_from') }}">
            <input type="hidden" name="date_to"      value="{{ request('date_to') }}">
            <input type="hidden" name="payment_mode" value="{{ $activeMode }}">
            <button type="submit" style="display:inline-flex;align-items:center;gap:6px;background:#059669;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                <i class="fas fa-download"></i> Export Excel
            </button>
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    @php $th = 'padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:1px solid #e5e7eb;text-align:left;white-space:nowrap;'; @endphp
                    <th style="{{ $th }}">Order ID</th>
                    <th style="{{ $th }}">Table</th>
                    <th style="{{ $th }}">Items</th>
                    <th style="{{ $th }}">Amount</th>
                    @if($gstStats['enabled'] ?? false)
                    <th style="{{ $th }}">CGST</th>
                    <th style="{{ $th }}">SGST</th>
                    <th style="{{ $th }}">Grand Total</th>
                    @endif
                    <th style="{{ $th }}">Status</th>
                    <th style="{{ $th }}">Payment</th>
                    <th style="{{ $th }}">Created By</th>
                    <th style="{{ $th }}">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php
                    $branch  = $order->branch;
                    $slab    = $branch?->gstSlab;
                    $mode    = $branch?->gst_mode;
                    $base    = (float) $order->total_amount;
                    $cgst = $sgst = 0; $grand = $base;
                    if ($slab && $mode && $order->status === 'paid') {
                        $cgstPct = (float) $slab->cgst_rate;
                        $sgstPct = (float) $slab->sgst_rate;
                        if ($mode === 'excluded') {
                            $cgst  = round($base * $cgstPct / 100, 2);
                            $sgst  = round($base * $sgstPct / 100, 2);
                            $grand = $base + $cgst + $sgst;
                        } else {
                            $totalPct = $cgstPct + $sgstPct;
                            $base     = round($base * 100 / (100 + $totalPct), 2);
                            $cgst     = round($base * $cgstPct / 100, 2);
                            $sgst     = round($base * $sgstPct / 100, 2);
                            $grand    = (float) $order->total_amount;
                        }
                    }
                    $td = 'padding:12px 14px;font-size:13px;color:#374151;border-bottom:1px solid #f3f4f6;vertical-align:top;';
                    $statusStyles = [
                        'paid'      => 'background:#dcfce7;color:#15803d;',
                        'pending'   => 'background:#fef9c3;color:#a16207;',
                        'preparing' => 'background:#dbeafe;color:#1d4ed8;',
                        'ready'     => 'background:#d1fae5;color:#065f46;',
                        'served'    => 'background:#ede9fe;color:#6d28d9;',
                        'cancelled' => 'background:#fee2e2;color:#dc2626;',
                    ];
                    $ss = $statusStyles[$order->status] ?? 'background:#f3f4f6;color:#6b7280;';
                @endphp
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="{{ $td }}font-weight:700;color:#111827;">#{{ $order->id }}</td>
                    <td style="{{ $td }}white-space:nowrap;">{{ $order->is_parcel ? '📦 Parcel' : 'T' . ($order->table?->table_number ?? '?') }}</td>
                    <td style="{{ $td }}">
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                        @foreach($order->orderItems as $item)
                            <span style="display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:5px;padding:2px 8px;font-size:12px;white-space:nowrap;">
                                {{ $item->menuItem?->name ?? '[Deleted]' }}
                                <span style="background:#3b82f6;color:#fff;border-radius:3px;padding:0 5px;font-size:11px;font-weight:700;">×{{ $item->quantity }}</span>
                            </span>
                        @endforeach
                        </div>
                    </td>
                    <td style="{{ $td }}font-weight:600;">₹{{ number_format($base, 2) }}</td>
                    @if($gstStats['enabled'] ?? false)
                    <td style="{{ $td }}color:#6b7280;">{{ $cgst ? '₹'.number_format($cgst,2) : '—' }}</td>
                    <td style="{{ $td }}color:#6b7280;">{{ $sgst ? '₹'.number_format($sgst,2) : '—' }}</td>
                    <td style="{{ $td }}font-weight:700;">₹{{ number_format($grand, 2) }}</td>
                    @endif
                    <td style="{{ $td }}">
                        <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{{ $ss }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td style="{{ $td }}">{{ $order->payment_mode ? ucfirst($order->payment_mode) : '—' }}</td>
                    <td style="{{ $td }}">{{ $order->user?->name ?? '—' }}</td>
                    <td style="{{ $td }}color:#6b7280;white-space:nowrap;">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ ($gstStats['enabled'] ?? false) ? '11' : '8' }}" style="padding:48px;text-align:center;">
                        <div style="font-size:40px;color:#d1d5db;margin-bottom:12px;"><i class="fas fa-inbox"></i></div>
                        <div style="font-size:14px;color:#6b7280;">No orders found for this period.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@else
<div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;padding:16px 20px;border-radius:8px;font-size:14px;">
    <i class="fas fa-info-circle" style="margin-right:8px;"></i> Please select a filter type above to view detailed reports.
</div>
@endif

<script>
function setFilterType(type) {
    document.getElementById('filterType').value = type;
    ['month','year','custom'].forEach(function(t) {
        var active = t === type;
        var btn = document.getElementById('ftab-' + t);
        if (btn) { btn.style.borderColor = active ? '#2563eb' : '#d1d5db'; btn.style.background = active ? '#eff6ff' : '#fff'; btn.style.color = active ? '#2563eb' : '#6b7280'; }
    });
    ['month','year'].forEach(function(t) {
        var panel = document.getElementById('panel-' + t);
        if (panel) panel.style.display = t === type ? 'flex' : 'none';
    });
    document.getElementById('panel-custom-from').style.display = type === 'custom' ? 'flex' : 'none';
    document.getElementById('panel-custom-to').style.display   = type === 'custom' ? 'flex' : 'none';
}
function setPaymentMode(mode) {
    document.getElementById('paymentMode').value = mode;
    ['all','cash','upi','card'].forEach(function(m) {
        var btn = document.getElementById('pmode-' + m);
        if (!btn) return;
        var active = m === mode;
        btn.style.borderColor = active ? '#2563eb' : '#d1d5db';
        btn.style.background  = active ? '#eff6ff' : '#fff';
        btn.style.color       = active ? '#2563eb' : '#6b7280';
    });
    // auto-submit only if a date filter is already active
    if (document.getElementById('filterType').value) {
        document.getElementById('filterForm').submit();
    }
}
document.getElementById('filterForm').addEventListener('submit', function(e) {
    var type = document.getElementById('filterType').value;
    if (!type) { e.preventDefault(); alert('Please select a filter type.'); return; }
    if (type === 'month'  && !document.querySelector('[name="month"]').value)     { e.preventDefault(); alert('Please select a month.'); return; }
    if (type === 'year'   && !document.querySelector('[name="year"]').value)      { e.preventDefault(); alert('Please select a year.'); return; }
    if (type === 'custom' && !document.querySelector('[name="date_from"]').value) { e.preventDefault(); alert('Please select a From date.'); return; }
    if (type === 'custom' && !document.querySelector('[name="date_to"]').value)   { e.preventDefault(); alert('Please select a To date.'); return; }
});
</script>
@endsection
