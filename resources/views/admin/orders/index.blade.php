@extends('layouts.admin')
@php use Illuminate\Support\Facades\URL; @endphp

@section('title', 'Waiter Panel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4" style="flex-wrap:wrap;gap:12px;">
    <div>
        <h1 class="section-title"><i class="fas fa-concierge-bell me-2"></i>Waiter Panel</h1>
        <p style="font-size:13px;color:var(--gray-500);">{{ now()->format('l, F j, Y') }}</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        {{-- Branch filter --}}
        @if($branches->count() > 0)
        <form method="GET" action="{{ route('admin.orders.index') }}" style="display:flex;align-items:center;gap:8px;">
            <select name="branch_id" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;min-width:160px;">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </form>
        @endif
        <a href="{{ route('admin.orders.create', $branchId ? ['branch_id' => $branchId] : []) }}"
           style="display:flex;align-items:center;gap:8px;background:#2563eb;color:#fff;padding:9px 18px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;">
            <i class="fas fa-plus"></i> New Order
        </a>
    </div>
</div>

{{-- Section Tabs --}}
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid #e5e7eb;">
    <button onclick="switchOrderSection('orders')" id="oSectionTab-orders"
        style="padding:11px 28px;font-size:14px;font-weight:700;border:none;background:none;cursor:pointer;border-bottom:3px solid #2563eb;color:#2563eb;margin-bottom:-2px;transition:all 0.2s;">
        <i class="fas fa-concierge-bell me-2"></i>Orders
    </button>
    <button onclick="switchOrderSection('payments')" id="oSectionTab-payments"
        style="padding:11px 28px;font-size:14px;font-weight:700;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#6b7280;margin-bottom:-2px;transition:all 0.2s;">
        <i class="fas fa-rupee-sign me-2"></i>Payments
        <span id="oPaymentBadge" style="background:#dc2626;color:#fff;border-radius:20px;padding:1px 8px;font-size:12px;margin-left:4px;{{ $paymentOrders->count() === 0 ? 'display:none' : '' }}">{{ $paymentOrders->count() }}</span>
    </button>
</div>

<div id="oSection-orders">
<div style="display:flex;flex-direction:column;gap:12px;">
    @forelse($orders as $order)
    <div class="content-card" style="padding:0;border-left:4px solid
        {{ $order->status === 'pending' ? '#f59e0b' : ($order->status === 'preparing' ? '#3b82f6' : ($order->status === 'ready' ? '#22c55e' : ($order->status === 'served' ? '#8b5cf6' : '#6b7280'))) }}"
        data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}">
        <div style="padding:16px;">
            {{-- Header --}}
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <div>
                    <div style="font-weight:700;font-size:16px;">Order #{{ $order->id }}</div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                        @if($order->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;"><i class="fas fa-box"></i> Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;">T{{ $order->table?->table_number }}</span>
                            @if($order->table?->category)
                                <span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;">{{ $order->table->category->name }}</span>
                            @endif
                        @endif
                        @if($order->branch)
                            <span style="background:#f3f4f6;color:#6b7280;font-size:11px;padding:2px 8px;border-radius:6px;">{{ $order->branch->name }}</span>
                        @endif
                    </div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:3px;">{{ $order->created_at->format('h:i A') }}</div>
                </div>
                <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;
                    {{ $order->status === 'pending'   ? 'background:#fef9c3;color:#a16207;' : '' }}
                    {{ $order->status === 'preparing' ? 'background:#dbeafe;color:#1d4ed8;' : '' }}
                    {{ $order->status === 'ready'     ? 'background:#dcfce7;color:#15803d;' : '' }}
                    {{ $order->status === 'served'    ? 'background:#ede9fe;color:#6d28d9;' : '' }}
                    {{ $order->status === 'cancelled' ? 'background:#fee2e2;color:#b91c1c;' : '' }}"
                    data-order-status-badge>{{ ucfirst($order->status) }}</span>
            </div>

            {{-- Items --}}
            <div style="margin-bottom:12px;">
                <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Items</div>
                @foreach($order->items as $item)
                <div style="font-size:13px;padding:4px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start;"
                     data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                    <div style="flex:1;">
                        <span class="{{ $item->status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}" data-item-name>
                            {{ $item->quantity }}&times; {{ $item->menuItem->name ?? '[Deleted Item]' }}
                        </span>
                        @if($item->status === 'cancelled')
                            <span style="font-size:11px;color:#dc2626;margin-left:4px;">(cancelled)</span>
                        @endif
                        @if($item->notes)
                            <div style="font-size:11px;color:#ea580c;font-style:italic;margin-top:2px;">&rarr; {{ $item->notes }}</div>
                        @endif
                    </div>
                    @if(!in_array($order->status, ['paid','cancelled']) && $item->status !== 'cancelled')
                    <div style="display:flex;gap:6px;align-items:center;" data-item-actions>
                        <button onclick="toggleAdminOrderEdit('aoedit-{{ $item->id }}')"
                            style="font-size:11px;padding:2px 8px;border:1px solid #3b82f6;background:#eff6ff;color:#1d4ed8;border-radius:4px;cursor:pointer;">Edit</button>
                        <form action="{{ route('admin.orders.items.cancel', $item->id) }}" method="POST" style="margin:0;">
                            @csrf @method('PATCH')
                            <button style="font-size:11px;padding:2px 8px;border:1px solid #dc2626;background:#fef2f2;color:#dc2626;border-radius:4px;cursor:pointer;">Cancel</button>
                        </form>
                    </div>
                    @endif
                </div>
                @if(!in_array($order->status, ['paid','cancelled']) && $item->status !== 'cancelled')
                <div id="aoedit-{{ $item->id }}" style="display:none;background:#f8fafc;border-radius:6px;padding:8px;margin-top:4px;">
                    <form action="{{ route('admin.orders.items.update', $item->id) }}" method="POST" style="display:flex;flex-direction:column;gap:6px;">
                        @csrf @method('PATCH')
                        <div style="display:flex;align-items:center;gap:8px;">
                            <label style="font-size:12px;color:#6b7280;min-width:36px;">Qty:</label>
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                   style="width:70px;border:1px solid #d1d5db;border-radius:4px;padding:4px 8px;font-size:13px;">
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <label style="font-size:12px;color:#6b7280;min-width:36px;">Note:</label>
                            <input type="text" name="notes" value="{{ $item->notes }}"
                                   style="flex:1;border:1px solid #d1d5db;border-radius:4px;padding:4px 8px;font-size:13px;" placeholder="Special request...">
                        </div>
                        <button style="align-self:flex-end;background:#2563eb;color:#fff;border:none;padding:4px 14px;border-radius:4px;font-size:12px;cursor:pointer;">Save</button>
                    </form>
                </div>
                @endif
                @endforeach
            </div>

            @if($order->customer_notes)
            <div style="background:#fffbeb;border-left:3px solid #f59e0b;padding:8px 12px;border-radius:4px;margin-bottom:12px;font-size:12px;color:#92400e;font-style:italic;">
                {{ $order->customer_notes }}
            </div>
            @endif

            {{-- Footer --}}
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid #f3f4f6;">
                <div>
                    <span style="font-weight:700;font-size:15px;" data-order-total>₹{{ number_format($order->total_amount, 2) }}</span>
                    @if($order->user)
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;">&#128100; {{ $order->user->name }}</div>
                    @endif
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    @if(!in_array($order->status, ['paid','cancelled']))
                    <button onclick="openAdminAssign({{ $order->id }}, {{ $order->branch_id ?? 'null' }}, '#{{ $order->id }} - {{ $order->is_parcel ? 'Parcel' : ''.$order->table?->table_number }}')" 
                        
                        style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                        <i class="fas fa-user-check"></i> Assign
                    </button>
                    <button onclick="openAdminAddItems({{ $order->id }}, '{{ $order->is_parcel ? 'Parcel' : $order->table?->table_number }}')"
                        style="background:#2563eb;color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                        + Add Items
                    </button>
                    @endif
                    @if($order->status === 'pending')
                    <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" style="margin:0;"
                          onsubmit="return confirm('Cancel order #{{ $order->id }}?')">
                        @csrf @method('PATCH')
                        <button style="background:#fee2e2;color:#dc2626;border:none;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
                            Cancel Order
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="content-card" style="text-align:center;padding:48px;">
        <div style="font-size:48px;margin-bottom:12px;"><i class="fas fa-utensils"></i></div>
        <p style="color:#6b7280;font-size:15px;">No active orders today</p>
        <a href="{{ route('admin.orders.create', $branchId ? ['branch_id' => $branchId] : []) }}"
           style="display:inline-block;margin-top:12px;background:#2563eb;color:#fff;padding:9px 20px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;">
            + Create First Order
        </a>
    </div>
    @endforelse
</div>
</div>{{-- end oSection-orders --}}

{{-- PAYMENTS SECTION --}}
<div id="oSection-payments" style="display:none;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 style="font-size:17px;font-weight:700;color:var(--gray-800);">Pending Payments (<span id="oPaymentCount">{{ $paymentOrders->count() }}</span>)</h2>
        <button onclick="location.reload()" style="display:flex;align-items:center;gap:6px;background:var(--gray-100);border:1px solid var(--gray-300);color:var(--gray-700);padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            <i class="fas fa-rotate-right"></i> Refresh
        </button>
    </div>

    <div id="oPaymentOrdersList" style="display:flex;flex-direction:column;gap:16px;">
    @forelse($paymentOrders as $order)
        <div class="content-card" style="border-left:4px solid #dc2626;padding:0;"
             data-payment-order-id="{{ $order->id }}"
             data-payment-status="{{ $order->status }}"
             data-is-parcel="{{ $order->is_parcel ? '1' : '0' }}">
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
                    <span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;
                        {{ $order->status === 'served'   ? 'background:#ede9fe;color:#6d28d9;' : '' }}
                        {{ $order->status === 'checkout' ? 'background:#d1fae5;color:#065f46;' : '' }}
                        {{ $order->status === 'ready'    ? 'background:#dcfce7;color:#15803d;' : '' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div style="margin-bottom:14px;">
                    @foreach($order->orderItems as $item)
                    <div style="padding:7px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <span style="font-size:13px;font-weight:500;{{ $item->status === 'cancelled' ? 'text-decoration:line-through;color:#9ca3af;' : '' }}">{{ $item->menuItem?->name ?? '[Deleted]' }}</span>
                            @if($item->status === 'cancelled')
                                <span style="font-size:11px;background:#fee2e2;color:#dc2626;padding:1px 6px;border-radius:4px;margin-left:4px;">Cancelled</span>
                            @endif
                            @if($item->notes)
                                <div style="font-size:11px;color:#d97706;font-style:italic;margin-top:2px;">&rarr; {{ $item->notes }}</div>
                            @endif
                            <div style="font-size:12px;color:#6b7280;">Qty: {{ $item->quantity }}</div>
                        </div>
                        <div style="font-weight:700;{{ $item->status === 'cancelled' ? 'color:#9ca3af;text-decoration:line-through;' : '' }}">
                            ₹{{ number_format($item->price * $item->quantity, 2) }}
                        </div>
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
        // If no branch filter, compute GST from the order's own branch
        if (!$branchId && $order->branch) {
            $orderBranch = $order->branch;
            $orderSlab   = $orderBranch->gstSlab;
            $orderMode   = $orderBranch->gst_mode;
            if ($orderSlab && $orderMode) {
                $gst = [
                    'enabled'   => true,
                    'mode'      => $orderMode,
                    'cgst_pct'  => (float) $orderSlab->cgst_rate,
                    'sgst_pct'  => (float) $orderSlab->sgst_rate,
                    'total_pct' => (float) ($orderSlab->cgst_rate + $orderSlab->sgst_rate),
                ];
            } else {
                $gst = ['enabled' => false];
            }
        }
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
    <div style="font-size:20px;font-weight:700;color:#16a34a;margin-bottom:14px;" data-order-total data-grand-total="{{ $grandTotal }}">Total: ₹{{ number_format($grandTotal, 2) }}</div>
    <form action="{{ route('admin.orders.payment', $order->id) }}" method="POST" id="oPayForm{{ $order->id }}">
        @csrf @method('PATCH')
        <input type="hidden" name="grand_total" value="{{ $grandTotal }}">
        <div style="margin-bottom:12px;">
            <div style="font-size:13px;font-weight:600;margin-bottom:8px;">Payment Method</div>
            <div style="display:grid;grid-template-columns:{{ $orderBranchUpiId ? 'repeat(2,1fr)' : '1fr' }};gap:8px;">
                <button type="button" onclick="oSelectMode({{ $order->id }},'cash')" class="o-pay-mode-btn" data-order="{{ $order->id }}" data-mode="cash"
                    style="padding:13px 6px;border:2px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;transition:all 0.2s;">&#128181; Cash</button>
                @if($orderBranchUpiId)
                <button type="button" onclick="oSelectMode({{ $order->id }},'upi',{{ $grandTotal }},'{{ $orderBranchUpiId }}')" class="o-pay-mode-btn" data-order="{{ $order->id }}" data-mode="upi"
                    style="padding:13px 6px;border:2px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;transition:all 0.2s;"><i class="fas fa-mobile-alt"></i> UPI</button>
                @endif
            </div>
            <input type="hidden" name="payment_mode" id="oPayMode{{ $order->id }}">
        </div>
        <div id="oCashSec{{ $order->id }}" style="display:none;margin-bottom:12px;">
            <div style="font-size:13px;font-weight:600;margin-bottom:6px;">Cash Received</div>
            <div style="display:flex;gap:8px;">
                <input type="number" step="0.01" min="0" id="oCashAmt{{ $order->id }}"
                    style="flex:1;border:2px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:15px;" placeholder="Enter amount">
                <button type="button" onclick="oCalcChange({{ $order->id }},{{ $grandTotal }})"
                    style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-weight:600;cursor:pointer;">OK</button>
            </div>
        </div>
        <div id="oChangeSec{{ $order->id }}" style="display:none;background:#fffbeb;border:2px solid #fbbf24;border-radius:8px;padding:12px;text-align:center;margin-bottom:12px;">
            <div style="font-size:12px;color:#92400e;font-weight:600;margin-bottom:4px;">Change to Return</div>
            <div id="oChangeAmt{{ $order->id }}" style="font-size:24px;font-weight:700;color:#b45309;">₹0.00</div>
        </div>
        <button type="submit" id="oSubmitBtn{{ $order->id }}"
            style="display:none;width:100%;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;"
            disabled>Complete Payment</button>
    </form>
</div>

            </div>
        </div>
    @empty
        <div style="background:#fff;border-radius:12px;padding:48px;text-align:center;border:1px solid #e5e7eb;">
            <div style="font-size:40px;margin-bottom:8px;">&#10003;</div>
            <p style="color:#6b7280;">No pending payments</p>
        </div>
    @endforelse
    </div>

    {{-- QR Modal --}}
    <div id="oPayQrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9998;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:360px;margin:auto;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
            <div style="color:#16a34a;font-size:48px;margin-bottom:8px;">&#10003;</div>
            <h5 style="font-weight:700;margin-bottom:4px;">Payment Complete!</h5>
            <p style="font-size:13px;color:#666;margin-bottom:16px;">Customer can scan to view their bill</p>
            <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:16px;">
                <div id="oPayQrContainer"></div>
            </div>
            <a id="oPayBillLink" href="#" target="_blank" style="font-size:13px;color:#2563eb;word-break:break-all;display:block;margin-bottom:16px;"></a>
            <div style="display:flex;gap:8px;">
                <button onclick="closeOPayQr()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Close</button>
                <a id="oPayOpenBillBtn" href="#" target="_blank" style="flex:1;background:#2563eb;color:#fff;border-radius:8px;padding:10px;font-weight:600;text-decoration:none;display:inline-block;">Open Bill</a>
            </div>
        </div>
    </div>
    {{-- UPI QR Modal --}}
    <div id="oUpiQrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:360px;margin:auto;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
            <h5 style="font-weight:700;margin-bottom:4px;"><i class="fas fa-mobile-alt"></i> UPI Payment</h5>
            <p style="font-size:13px;color:#666;margin-bottom:6px;">Ask customer to scan with Google Pay / PhonePe</p>
            <div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px;" id="oUpiAmountDisplay"></div>
            <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:12px;">
                <div id="oUpiQrContainer"></div>
            </div>
            <p style="font-size:12px;color:#9ca3af;margin-bottom:16px;" id="oUpiIdDisplay"></p>
            <div style="display:flex;gap:8px;">
                <button onclick="oCloseUpiQr()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Cancel</button>
                <button onclick="oConfirmUpi()" style="flex:1;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">&#10003; Payment Received</button>
            </div>
        </div>
    </div>
</div>{{-- end oSection-payments --}}
<div id="adminAddItemsModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;">
    <div style="position:fixed;inset:0;display:flex;align-items:flex-end;">
        <div style="background:#fff;width:100%;border-radius:24px 24px 0 0;max-height:90vh;display:flex;flex-direction:column;">
            <div style="padding:16px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
                <div>
                    <div style="font-weight:700;font-size:18px;">Add Items</div>
                    <div style="font-size:13px;color:#6b7280;">Order #<span id="aoModalOrderId"></span> - <span id="aoModalTable"></span></div>
                </div>
                <button onclick="closeAdminAddItems()" style="font-size:24px;background:none;border:none;cursor:pointer;color:#6b7280;">&times;</button>
            </div>
            <div style="padding:12px 16px;border-bottom:1px solid #e5e7eb;flex-shrink:0;">
                <input type="text" id="aoItemSearch" placeholder="Search menu items..."
                       style="width:100%;padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;margin-bottom:10px;"
                       onkeyup="aoFilterItems()">
                <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;">
                    <button onclick="aoFilterCat('all',this)" class="ao-cat-btn" style="padding:6px 16px;border-radius:20px;background:#2563eb;color:#fff;border:none;font-size:13px;white-space:nowrap;cursor:pointer;">All</button>
                    @php $aoCats = $menuItems->pluck('category.name')->filter()->unique(); @endphp
                    @foreach($aoCats as $cat)
                        <button onclick="aoFilterCat('{{ $cat }}',this)" class="ao-cat-btn" style="padding:6px 16px;border-radius:20px;background:#f3f4f6;color:#374151;border:none;font-size:13px;white-space:nowrap;cursor:pointer;">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>
            <div style="flex:1;overflow-y:auto;padding:16px;min-height:0;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" id="aoMenuGrid">
                    @foreach($menuItems as $mi)
                    <div class="ao-menu-item" data-category="{{ $mi->category?->name }}" data-name="{{ strtolower($mi->name) }}"
                         style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                        @if($mi->image)
                            <img src="{{ asset($mi->image) }}" style="width:100%;height:100px;object-fit:cover;">
                        @else
                            <div style="width:100%;height:100px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);display:flex;align-items:center;justify-content:center;font-size:32px;"><i class="fas fa-utensils"></i></div>
                        @endif
                        <div style="padding:10px;">
                            <div style="font-weight:600;font-size:13px;margin-bottom:4px;">{{ $mi->name }}</div>
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-weight:700;color:#2563eb;">₹{{ $mi->price }}</span>
                                <button onclick="aoAddItem({{ $mi->id }},'{{ addslashes($mi->name) }}',{{ $mi->price }})"
                                    style="background:#2563eb;color:#fff;border:none;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;">+ Add</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div style="padding:16px;border-top:1px solid #e5e7eb;background:#f9fafb;flex-shrink:0;">
                <div id="aoSelectedList" style="margin-bottom:12px;max-height:140px;overflow-y:auto;display:none;"></div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-weight:600;font-size:15px;">New Items Total:</span>
                    <span id="aoTotal" style="font-weight:700;font-size:20px;color:#2563eb;">₹0.00</span>
                </div>
                <button onclick="aoSubmit()" style="width:100%;background:#2563eb;color:#fff;border:none;padding:14px;border-radius:10px;font-weight:700;font-size:16px;cursor:pointer;">Add to Order</button>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAdminOrderEdit(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

let aoOrderId = null, aoItems = [];

function openAdminAddItems(orderId, table) {
    aoOrderId = orderId; aoItems = [];
    document.getElementById('aoModalOrderId').textContent = orderId;
    document.getElementById('aoModalTable').textContent = table;
    document.getElementById('aoSelectedList').style.display = 'none';
    document.getElementById('aoSelectedList').innerHTML = '';
    document.getElementById('aoTotal').textContent = '₹0.00';
    document.getElementById('aoItemSearch').value = '';
    document.querySelectorAll('.ao-menu-item').forEach(i => i.style.display = 'block');
    const btns = document.querySelectorAll('.ao-cat-btn');
    btns.forEach(b => { b.style.background='#f3f4f6'; b.style.color='#374151'; });
    if (btns[0]) { btns[0].style.background='#2563eb'; btns[0].style.color='#fff'; }
    document.getElementById('adminAddItemsModal').style.display = 'block';
}
function closeAdminAddItems() {
    if (aoItems.length && !confirm('You have selected items. Close anyway?')) return;
    document.getElementById('adminAddItemsModal').style.display = 'none';
    aoItems = [];
}
function aoAddItem(id, name, price) {
    const ex = aoItems.find(i => i.id === id);
    if (ex) ex.quantity++; else aoItems.push({id, name, price, quantity:1, notes:''});
    aoRender();
}
function aoRender() {
    const div = document.getElementById('aoSelectedList');
    if (!aoItems.length) { div.style.display='none'; div.innerHTML=''; }
    else {
        div.style.display = 'block';
        div.innerHTML = '<div style="display:flex;flex-direction:column;gap:8px;">' + aoItems.map((item,i) => `
            <div style="background:#fff;padding:10px;border-radius:8px;border:1px solid #e5e7eb;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <div><div style="font-weight:600;font-size:13px;">${item.name}</div><div style="font-size:11px;color:#6b7280;">₹${item.price} each</div></div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button onclick="aoQty(${i},-1)" style="width:28px;height:28px;border-radius:50%;background:#f3f4f6;border:none;font-weight:700;cursor:pointer;">-</button>
                        <span style="font-weight:700;min-width:20px;text-align:center;">${item.quantity}</span>
                        <button onclick="aoQty(${i},1)" style="width:28px;height:28px;border-radius:50%;background:#2563eb;color:#fff;border:none;font-weight:700;cursor:pointer;">+</button>
                        <button onclick="aoRemove(${i})" style="background:none;border:none;color:#dc2626;font-size:18px;cursor:pointer;">&times;</button>
                    </div>
                </div>
                <textarea rows="1" style="width:100%;padding:4px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:12px;"
                    placeholder="Special request..." onchange="aoItems[${i}].notes=this.value">${item.notes}</textarea>
            </div>`).join('') + '</div>';
    }
    const total = aoItems.reduce((s,i) => s + i.price * i.quantity, 0);
    document.getElementById('aoTotal').textContent = '₹' + total.toFixed(2);
}
function aoQty(i, d) { aoItems[i].quantity += d; if (aoItems[i].quantity <= 0) aoItems.splice(i,1); aoRender(); }
function aoRemove(i) { aoItems.splice(i,1); aoRender(); }
function aoFilterItems() {
    const q = document.getElementById('aoItemSearch').value.toLowerCase();
    document.querySelectorAll('.ao-menu-item').forEach(el => { el.style.display = el.dataset.name.includes(q) ? 'block' : 'none'; });
}
function aoFilterCat(cat, btn) {
    document.querySelectorAll('.ao-cat-btn').forEach(b => { b.style.background='#f3f4f6'; b.style.color='#374151'; });
    btn.style.background='#2563eb'; btn.style.color='#fff';
    document.querySelectorAll('.ao-menu-item').forEach(el => { el.style.display = (cat==='all'||el.dataset.category===cat) ? 'block' : 'none'; });
}
function aoSubmit() {
    if (!aoItems.length) { alert('Please select at least one item!'); return; }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/orders/${aoOrderId}/add-items`;
    const csrf = document.createElement('input'); csrf.type='hidden'; csrf.name='_token'; csrf.value='{{ csrf_token() }}'; form.appendChild(csrf);
    aoItems.forEach((item,i) => {
        [['menu_item_id',item.id],['quantity',item.quantity],['notes',item.notes||'']].forEach(([k,v]) => {
            const inp = document.createElement('input'); inp.type='hidden'; inp.name=`items[${i}][${k}]`; inp.value=v; form.appendChild(inp);
        });
    });
    document.body.appendChild(form); form.submit();
}
</script>
{{-- Assign Order Modal --}}
<div id="adminAssignModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:10000;align-items:flex-end;">
    <div style="position:fixed;bottom:0;left:0;right:0;background:#fff;border-radius:24px 24px 0 0;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div>
                <div style="font-weight:700;font-size:18px;">Assign Order</div>
                <div style="font-size:13px;color:#6b7280;" id="adminAssignLabel"></div>
            </div>
            <button onclick="closeAdminAssign()" style="font-size:24px;background:none;border:none;cursor:pointer;color:#6b7280;">&times;</button>
        </div>
        <form id="adminAssignForm" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Assign to Waiter</label>
                <select name="to_user_id" id="adminAssignSelect" required
                        style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:14px;background:#fff;">
                    <option value="">Select waiter...</option>
                </select>
                <div id="adminAssignNoWaiters" style="display:none;font-size:13px;color:#dc2626;margin-top:6px;">
                    No active waiters found for this branch.
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Note (optional)</label>
                <input type="text" name="note" placeholder="e.g. VIP table, handle with care..."
                       style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:14px;">
            </div>
            <button type="submit" id="adminAssignSubmit"
                    style="width:100%;background:#16a34a;color:#fff;border:none;padding:14px;border-radius:10px;font-weight:700;font-size:16px;cursor:pointer;">
                Assign Order
            </button>
        </form>
    </div>
</div>

<script>
const waitersByBranch = @json($waitersByBranch);

function openAdminAssign(orderId, branchId, label) {
    const select = document.getElementById('adminAssignSelect');
    const noWaiters = document.getElementById('adminAssignNoWaiters');
    const submitBtn = document.getElementById('adminAssignSubmit');

    document.getElementById('adminAssignLabel').textContent = label;
    document.getElementById('adminAssignForm').action = `/admin/orders/${orderId}/assign`;

    // Populate waiters for this order's branch
    select.innerHTML = '<option value="">Select waiter...</option>';
    const waiters = branchId && waitersByBranch[branchId] ? waitersByBranch[branchId] : [];

    if (waiters.length === 0) {
        noWaiters.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
    } else {
        noWaiters.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        waiters.forEach(w => {
            const opt = document.createElement('option');
            opt.value = w.id;
            opt.textContent = w.name;
            select.appendChild(opt);
        });
    }

    document.getElementById('adminAssignModal').style.display = 'flex';
}

function closeAdminAssign() {
    document.getElementById('adminAssignModal').style.display = 'none';
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
//Section switching
function switchOrderSection(s) {
    document.getElementById('oSection-orders').style.display   = s === 'orders'   ? '' : 'none';
    document.getElementById('oSection-payments').style.display = s === 'payments' ? '' : 'none';
    const oTab = document.getElementById('oSectionTab-orders');
    const pTab = document.getElementById('oSectionTab-payments');
    oTab.style.borderBottomColor = s === 'orders'   ? '#2563eb' : 'transparent';
    oTab.style.color             = s === 'orders'   ? '#2563eb' : '#6b7280';
    pTab.style.borderBottomColor = s === 'payments' ? '#2563eb' : 'transparent';
    pTab.style.color             = s === 'payments' ? '#2563eb' : '#6b7280';
}

// Payment mode selection
var oUpiPendingOrderId = null;

function oSelectMode(orderId, mode, amount, upiId) {
    document.querySelectorAll(`[data-order="${orderId}"].o-pay-mode-btn`).forEach(btn => {
        btn.style.borderColor = '#d1d5db'; btn.style.background = '#fff';
    });
    event.target.style.borderColor = '#2563eb';
    event.target.style.background  = '#eff6ff';
    document.getElementById('oPayMode' + orderId).value = mode;
    const cashSec   = document.getElementById('oCashSec'   + orderId);
    const changeSec = document.getElementById('oChangeSec' + orderId);
    const submitBtn = document.getElementById('oSubmitBtn' + orderId);
    if (mode === 'cash') {
        cashSec.style.display   = 'block';
        changeSec.style.display = 'none';
        submitBtn.style.display = 'none';
        submitBtn.disabled      = true;
    } else if (mode === 'upi') {
        cashSec.style.display   = 'none';
        changeSec.style.display = 'none';
        submitBtn.style.display = 'none';
        submitBtn.disabled      = true;
        oShowUpiQr(orderId, amount, upiId);
    }
}

function oShowUpiQr(orderId, amount, upiId) {
    oUpiPendingOrderId = orderId;
    const upiUri = `upi://pay?pa=${encodeURIComponent(upiId)}&am=${parseFloat(amount).toFixed(2)}&cu=INR`;
    document.getElementById('oUpiAmountDisplay').textContent = '₹' + parseFloat(amount).toFixed(2);
    document.getElementById('oUpiIdDisplay').textContent = 'UPI ID: ' + upiId;
    const container = document.getElementById('oUpiQrContainer');
    container.innerHTML = '';
    new QRCode(container, { text: upiUri, width: 220, height: 220, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
    document.getElementById('oUpiQrModal').style.display = 'flex';
}

function oCloseUpiQr() {
    document.getElementById('oUpiQrModal').style.display = 'none';
    if (oUpiPendingOrderId) {
        document.querySelectorAll(`[data-order="${oUpiPendingOrderId}"].o-pay-mode-btn`).forEach(btn => {
            btn.style.borderColor = '#d1d5db'; btn.style.background = '#fff';
        });
        document.getElementById('oPayMode' + oUpiPendingOrderId).value = '';
        oUpiPendingOrderId = null;
    }
}

function oConfirmUpi() {
    document.getElementById('oUpiQrModal').style.display = 'none';
    if (!oUpiPendingOrderId) return;
    const submitBtn = document.getElementById('oSubmitBtn' + oUpiPendingOrderId);
    submitBtn.style.display = 'block';
    submitBtn.disabled = false;
    submitBtn.click();
}

function oCalcChange(orderId, total) {
    const cash = parseFloat(document.getElementById('oCashAmt' + orderId).value);
    if (!cash || cash < total) { alert('Cash must be at least ₹' + total.toFixed(2)); return; }
    document.getElementById('oChangeAmt'  + orderId).textContent    = '₹' + (cash - total).toFixed(2);
    document.getElementById('oChangeSec'  + orderId).style.display  = 'block';
    document.getElementById('oSubmitBtn'  + orderId).style.display  = 'block';
    document.getElementById('oSubmitBtn'  + orderId).disabled       = false;
}

// QR modal
const O_BILL_URLS = {
    @foreach($paymentOrders as $order)
    {{ $order->id }}: "{{ URL::signedRoute('bill.show', ['orderId' => $order->id]) }}",
    @endforeach
};
function showOPayQr(orderId, billUrl) {
    document.getElementById('oPayBillLink').textContent    = billUrl;
    document.getElementById('oPayBillLink').href           = billUrl;
    document.getElementById('oPayOpenBillBtn').href        = billUrl;
    const c = document.getElementById('oPayQrContainer'); c.innerHTML = '';
    new QRCode(c, { text: billUrl, width: 200, height: 200, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
    document.getElementById('oPayQrModal').style.display = 'flex';
}
function closeOPayQr() { document.getElementById('oPayQrModal').style.display = 'none'; }

// AJAX payment submit
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="oPayForm"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const orderId  = form.id.replace('oPayForm', '');
            const submitBtn = document.getElementById('oSubmitBtn' + orderId);
            if (!document.getElementById('oPayMode' + orderId).value) { alert('Please select a payment method'); return; }
            submitBtn.disabled = true; submitBtn.textContent = 'Processing...';
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            })
            .then(r => r.json())
            .then(function (res) {
                if (res.success) {
                    O_BILL_URLS[res.order_id] = res.bill_url;
                    const card = document.querySelector(`[data-payment-order-id="${orderId}"]`);
                    if (card) {
                        card.style.transition = 'opacity .35s,transform .35s';
                        card.style.opacity = '0'; card.style.transform = 'scale(0.97)';
                        setTimeout(function () {
                            card.remove();
                            const cnt = document.getElementById('oPaymentCount');
                            if (cnt) cnt.textContent = document.querySelectorAll('[data-payment-order-id]').length;
                            const badge = document.getElementById('oPaymentBadge');
                            if (badge) { const n = document.querySelectorAll('[data-payment-order-id]').length; badge.textContent = n; badge.style.display = n === 0 ? 'none' : ''; }
                            const cont = document.getElementById('oPaymentOrdersList');
                            if (cont && !cont.querySelector('[data-payment-order-id]')) {
                                cont.innerHTML = '<div style="background:#fff;border-radius:12px;padding:48px;text-align:center;border:1px solid #e5e7eb;"><div style="font-size:40px;margin-bottom:8px;">&#10003;</div><p style="color:#6b7280;">No pending payments</p></div>';
                            }
                        }, 350);
                    }
                    showOPayQr(res.order_id, res.bill_url);
                } else {
                    submitBtn.disabled = false; submitBtn.textContent = 'Complete Payment';
                    alert(res.message || 'Payment failed. Please try again.');
                }
            })
            .catch(function () {
                submitBtn.disabled = false; submitBtn.textContent = 'Complete Payment';
                alert('Network error. Please try again.');
            });
        });
    });

});
</script>

<script>
(function () {
    'use strict';

    var currentBranchId = {!! json_encode($branchId) !!};
    var pollUrl = '/api/order-updates?panel=admin_waiter' + (currentBranchId ? '&branch_id=' + currentBranchId : '');

    var snapOrders   = {};
    var snapPayments = {};


    function buildSnapshot() {
        document.querySelectorAll('#oSection-orders [data-order-id]').forEach(function (card) {
            var oid = card.dataset.orderId;
            snapOrders[oid] = { status: card.dataset.orderStatus || '' };
        });
        document.querySelectorAll('[data-payment-order-id]').forEach(function (card) {
            snapPayments[card.dataset.paymentOrderId] = true;
        });
    }

    function toast(msg, color) {
        var el = document.createElement('div');
        el.style.cssText = 'position:fixed;top:16px;left:50%;transform:translateX(-50%);' +
            'background:' + (color || '#2563eb') + ';color:#fff;padding:10px 22px;border-radius:8px;' +
            'font-size:14px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.25);z-index:99999;white-space:nowrap;pointer-events:none;';
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 4000);
    }

    function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

    function orderLabel(order) {
        return order.is_parcel ? 'Parcel' : 'T' + order.table_number;
    }

    function statusColor(status) {
        var map = { pending:'#f59e0b', preparing:'#3b82f6', ready:'#22c55e', served:'#8b5cf6', cancelled:'#ef4444', checkout:'#6366f1', paid:'#6b7280' };
        return map[status] || '#6b7280';
    }

    function statusBg(status) {
        var map = {
            pending:   'background:#fef9c3;color:#a16207;',
            preparing: 'background:#dbeafe;color:#1d4ed8;',
            ready:     'background:#dcfce7;color:#15803d;',
            served:    'background:#ede9fe;color:#6d28d9;',
            cancelled: 'background:#fee2e2;color:#b91c1c;',
            checkout:  'background:#d1fae5;color:#065f46;'
        };
        return map[status] || '';
    }


    function buildOrderCard(order) {
        var typeBadge = order.is_parcel
            ? '<span style="background:#ea580c;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;">Parcel</span>'
            : '<span style="background:#1e3a5f;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;">T' + order.table_number + '</span>'
              + (order.table_category ? '<span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;">' + order.table_category + '</span>' : '');

        var branchBadge = order.branch_name
            ? '<span style="background:#f3f4f6;color:#6b7280;font-size:11px;padding:2px 8px;border-radius:6px;">' + order.branch_name + '</span>'
            : '';

        var itemsHtml = order.items.map(function (item) {
            var cancelled = item.status === 'cancelled';
            return '<div style="font-size:13px;padding:4px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start;"'
                + ' data-item-id="' + item.id + '" data-item-status="' + item.status + '">'
                + '<div style="flex:1;"><span' + (cancelled ? ' style="text-decoration:line-through;color:#9ca3af;"' : '') + ' data-item-name>'
                + item.quantity + '&times; ' + item.name + '</span>'
                + (cancelled ? '<span style="font-size:11px;color:#dc2626;margin-left:4px;">(cancelled)</span>' : '')
                + (item.notes ? '<div style="font-size:11px;color:#ea580c;font-style:italic;margin-top:2px;">&rarr; ' + item.notes + '</div>' : '')
                + '</div></div>';
        }).join('');

        var notesHtml = order.customer_notes
            ? '<div style="background:#fffbeb;border-left:3px solid #f59e0b;padding:8px 12px;border-radius:4px;margin-bottom:12px;font-size:12px;color:#92400e;font-style:italic;">' + order.customer_notes + '</div>'
            : '';

        var userHtml = order.user_name
            ? '<div style="font-size:11px;color:#6b7280;margin-top:2px;">&#128100; ' + order.user_name + '</div>'
            : '';

        var div = document.createElement('div');
        div.className = 'content-card';
        div.style.cssText = 'padding:0;border-left:4px solid ' + statusColor(order.status) + ';opacity:0;transition:opacity .4s;';
        div.setAttribute('data-order-id', order.id);
        div.setAttribute('data-order-status', order.status);
        div.innerHTML =
            '<div style="padding:16px;">'
            + '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">'
            + '<div><div style="font-weight:700;font-size:16px;">Order #' + order.id + '</div>'
            + '<div style="display:flex;align-items:center;gap:8px;margin-top:4px;">' + typeBadge + branchBadge + '</div>'
            + '<div style="font-size:11px;color:#9ca3af;margin-top:3px;">' + order.created_at + '</div></div>'
            + '<span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;' + statusBg(order.status) + '" data-order-status-badge>' + ucfirst(order.status) + '</span>'
            + '</div>'
            + '<div style="margin-bottom:12px;"><div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;margin-bottom:6px;">Items</div>' + itemsHtml + '</div>'
            + notesHtml
            + '<div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid #f3f4f6;">'
            + '<div><span style="font-weight:700;font-size:15px;" data-order-total>₹' + parseFloat(order.total_amount).toFixed(2) + '</span>' + userHtml + '</div>'
            + '</div></div>';
        return div;
    }

  
    var PAGE_UPI_ID = {!! json_encode($branchUpiId) !!};

    function buildPaymentCard(order) {
        var oid   = order.id;
        var total = parseFloat(order.grand_total || order.total_amount).toFixed(2);
        var subtotal = parseFloat(order.total_amount).toFixed(2);
        var csrf  = (document.querySelector('meta[name="csrf-token"]') || {}).content
                 || (document.querySelector('[name="_token"]') || {}).value || '';

        // Use per-order UPI ID from the order's own branch
        var orderUpiId = order.upi_id || PAGE_UPI_ID;

        var gstHtml = '';
        if (order.gst_enabled) {
            var subtotalLabel = order.gst_mode === 'excluded' ? 'Subtotal' : 'Subtotal (excl. GST)';
            var subtotalVal   = order.gst_mode === 'excluded' ? subtotal : parseFloat(order.total_amount - order.cgst_amount - order.sgst_amount).toFixed(2);
            gstHtml = '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:13px;">'
                + '<div style="display:flex;justify-content:space-between;"><span>' + subtotalLabel + '</span><span>₹' + subtotalVal + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;color:#6b7280;"><span>CGST (' + order.cgst_pct + '%)</span><span>₹' + parseFloat(order.cgst_amount).toFixed(2) + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;color:#6b7280;"><span>SGST (' + order.sgst_pct + '%)</span><span>₹' + parseFloat(order.sgst_amount).toFixed(2) + '</span></div>'
                + '<div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid #bbf7d0;margin-top:6px;padding-top:6px;"><span>Grand Total</span><span>₹' + total + '</span></div>'
                + '<div style="font-size:11px;color:#6b7280;margin-top:2px;">GST ' + (order.gst_mode === 'included' ? 'included in price' : 'added on bill') + '</div>'
                + '</div>';
        }

        var typeBadge = order.is_parcel
            ? '<span style="background:#ea580c;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;">Parcel</span>'
            : '<span style="background:#1e3a5f;color:#fff;font-size:12px;font-weight:700;padding:2px 10px;border-radius:6px;">T' + order.table_number + '</span>'
              + (order.table_category ? '<span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;">' + order.table_category + '</span>' : '');

        var itemsHtml = order.items.map(function (item) {
            var cancelled = item.status === 'cancelled';
            return '<div style="padding:7px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">'
                + '<div><span style="font-size:13px;font-weight:500;' + (cancelled ? 'text-decoration:line-through;color:#9ca3af;' : '') + '">' + item.name + '</span>'
                + (cancelled ? '<span style="font-size:11px;background:#fee2e2;color:#dc2626;padding:1px 6px;border-radius:4px;margin-left:4px;">Cancelled</span>' : '')
                + (item.notes ? '<div style="font-size:11px;color:#d97706;font-style:italic;margin-top:2px;">&rarr; ' + item.notes + '</div>' : '')
                + '<div style="font-size:12px;color:#6b7280;">Qty: ' + item.quantity + '</div></div>'
                + '<div style="font-weight:700;' + (cancelled ? 'color:#9ca3af;text-decoration:line-through;' : '') + '">₹' + (item.price * item.quantity).toFixed(2) + '</div>'
                + '</div>';
        }).join('');

        var notesHtml = order.customer_notes
            ? '<div style="background:#fffbeb;border-left:4px solid #fbbf24;padding:10px 12px;border-radius:4px;margin-bottom:14px;">'
              + '<div style="font-size:12px;font-weight:600;color:#92400e;margin-bottom:2px;">Customer Request:</div>'
              + '<div style="font-size:13px;color:#78350f;font-style:italic;">' + order.customer_notes + '</div></div>'
            : '';

        var upiBtn = orderUpiId
            ? '<button type="button" onclick="oSelectMode(' + oid + ',\'upi\',' + total + ',\'' + orderUpiId + '\')" class="o-pay-mode-btn" data-order="' + oid + '" data-mode="upi" style="padding:13px 6px;border:2px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;">UPI</button>'
            : '';
        var gridCols = orderUpiId ? 'repeat(2,1fr)' : '1fr';

        var div = document.createElement('div');
        div.className = 'content-card';
        div.style.cssText = 'border-left:4px solid #dc2626;padding:0;opacity:0;transition:opacity .4s;';
        div.setAttribute('data-payment-order-id', oid);
        div.setAttribute('data-payment-status', order.status);
        div.setAttribute('data-is-parcel', order.is_parcel ? '1' : '0');

        div.innerHTML =
            '<div style="padding:20px;">'
            + '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">'
            + '<div><div style="font-size:17px;font-weight:700;">Order #' + oid + '</div>'
            + '<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">' + typeBadge + '</div>'
            + '<div style="font-size:11px;color:#9ca3af;margin-top:3px;">' + order.created_at + '</div></div>'
            + '<span style="padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;' + statusBg(order.status) + '">' + ucfirst(order.status) + '</span>'
            + '</div>'
            + '<div style="margin-bottom:14px;">' + itemsHtml + '</div>'
            + notesHtml
            + '<div style="border-top:1px solid #e5e7eb;padding-top:14px;">'
            + gstHtml
            + '<div style="font-size:20px;font-weight:700;color:#16a34a;margin-bottom:14px;" data-order-total data-grand-total="' + total + '">Total: ₹' + total + '</div>'
            + '<form action="/admin/orders/' + oid + '/payment" method="POST" id="oPayForm' + oid + '">'
            + '<input type="hidden" name="_token" value="' + csrf + '">'
            + '<input type="hidden" name="_method" value="PATCH">'
            + '<input type="hidden" name="grand_total" value="' + total + '">'
            + '<div style="margin-bottom:12px;"><div style="font-size:13px;font-weight:600;margin-bottom:8px;">Payment Method</div>'
            + '<div style="display:grid;grid-template-columns:' + gridCols + ';gap:8px;">'
            + '<button type="button" onclick="oSelectMode(' + oid + ',\'cash\')" class="o-pay-mode-btn" data-order="' + oid + '" data-mode="cash" style="padding:13px 6px;border:2px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;font-size:13px;font-weight:600;">Cash</button>'
            + upiBtn
            + '</div><input type="hidden" name="payment_mode" id="oPayMode' + oid + '"></div>'
            + '<div id="oCashSec' + oid + '" style="display:none;margin-bottom:12px;"><div style="font-size:13px;font-weight:600;margin-bottom:6px;">Cash Received</div>'
            + '<div style="display:flex;gap:8px;"><input type="number" step="0.01" min="0" id="oCashAmt' + oid + '" style="flex:1;border:2px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:15px;" placeholder="Enter amount">'
            + '<button type="button" onclick="oCalcChange(' + oid + ',' + total + ')" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-weight:600;cursor:pointer;">OK</button></div></div>'
            + '<div id="oChangeSec' + oid + '" style="display:none;background:#fffbeb;border:2px solid #fbbf24;border-radius:8px;padding:12px;text-align:center;margin-bottom:12px;">'
            + '<div style="font-size:12px;color:#92400e;font-weight:600;margin-bottom:4px;">Change to Return</div>'
            + '<div id="oChangeAmt' + oid + '" style="font-size:24px;font-weight:700;color:#b45309;">₹0.00</div></div>'
            + '<button type="submit" id="oSubmitBtn' + oid + '" style="display:none;width:100%;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;" disabled>Complete Payment</button>'
            + '</form></div></div>';

        div.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault();
            var submitBtn = document.getElementById('oSubmitBtn' + oid);
            if (!document.getElementById('oPayMode' + oid).value) { alert('Please select a payment method'); return; }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            fetch('/admin/orders/' + oid + '/payment', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(e.target),
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (typeof O_BILL_URLS !== 'undefined') O_BILL_URLS[res.order_id] = res.bill_url;
                    var card = document.querySelector('[data-payment-order-id="' + oid + '"]');
                    if (card) {
                        card.style.transition = 'opacity .35s,transform .35s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.97)';
                        setTimeout(function () {
                            card.remove();
                            delete snapPayments[String(oid)];
                            refreshPaymentBadge();
                            checkPaymentEmpty();
                        }, 350);
                    }
                    if (typeof showOPayQr === 'function') showOPayQr(res.order_id, res.bill_url);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Payment';
                    alert('Payment failed.');
                }
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Payment';
                alert('Network error.');
            });
        });

        return div;
    }


    function refreshPaymentBadge() {
        var n     = document.querySelectorAll('[data-payment-order-id]').length;
        var badge = document.getElementById('oPaymentBadge');
        var cnt   = document.getElementById('oPaymentCount');
        if (badge) { badge.textContent = n; badge.style.display = n === 0 ? 'none' : ''; }
        if (cnt)   cnt.textContent = n;
    }

    function checkPaymentEmpty() {
        var list = document.getElementById('oPaymentOrdersList');
        if (list && !list.querySelector('[data-payment-order-id]')) {
            list.innerHTML = '<div style="background:#fff;border-radius:12px;padding:48px;text-align:center;border:1px solid #e5e7eb;"><div style="font-size:40px;margin-bottom:8px;">&#10003;</div><p style="color:#6b7280;">No pending payments</p></div>';
        }
    }

    function checkOrderEmpty() {
        var list = document.querySelector('#oSection-orders > div');
        if (list && !list.querySelector('[data-order-id]')) {
            list.innerHTML = '<div class="content-card" style="text-align:center;padding:48px;"><div style="font-size:48px;margin-bottom:12px;">&#127869;</div><p style="color:#6b7280;font-size:15px;">No active orders today</p></div>';
        }
    }


    function updateOrderCard(order) {
        var card = document.querySelector('#oSection-orders [data-order-id="' + order.id + '"]');
        if (!card) return;
        card.dataset.orderStatus = order.status;
        card.style.borderLeftColor = statusColor(order.status);
        var badge = card.querySelector('[data-order-status-badge]');
        if (badge) {
            badge.textContent = ucfirst(order.status);
            badge.style.cssText = 'padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;' + statusBg(order.status);
        }
        var totalEl = card.querySelector('[data-order-total]');
        if (totalEl) {
            var grand = totalEl.dataset.grandTotal || parseFloat(order.total_amount).toFixed(2);
            totalEl.textContent = '\u20b9' + parseFloat(grand).toFixed(2);
        }
        // Update item statuses
        order.items.forEach(function (item) {
            var row = card.querySelector('[data-item-id="' + item.id + '"]');
            if (!row) return;
            if (row.dataset.itemStatus !== item.status) {
                row.dataset.itemStatus = item.status;
                var nameEl = row.querySelector('[data-item-name]');
                if (nameEl && item.status === 'cancelled') nameEl.style.textDecoration = 'line-through';
                var actions = row.querySelector('[data-item-actions]');
                if (actions && item.status === 'cancelled') actions.innerHTML = '';
            }
        });
    }

   
    function poll() {
        fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data || !data.orders) return;


            var seenOrders = {};
            data.orders.forEach(function (order) {
                var oid = String(order.id);
                seenOrders[oid] = true;

                if (!snapOrders[oid]) {
                    snapOrders[oid] = { status: order.status };
                    var list = document.querySelector('#oSection-orders > div');
                    if (list && !list.querySelector('[data-order-id="' + oid + '"]')) {
                        var empty = list.querySelector('.content-card:not([data-order-id])');
                        if (empty) empty.remove();
                        var card = buildOrderCard(order);
                        list.insertBefore(card, list.firstChild);
                        requestAnimationFrame(function () { card.style.opacity = '1'; });
                        var branchInfo = (!currentBranchId && order.branch_name) ? ' [' + order.branch_name + ']' : '';
                        toast('New order #' + order.id + ' - ' + orderLabel(order) + branchInfo, '#2563eb');

                    }
                } else if (snapOrders[oid].status !== order.status) {
                    var prev = snapOrders[oid].status;
                    snapOrders[oid].status = order.status;
                    if (['paid', 'cancelled', 'checkout'].includes(order.status)) {
                        var card = document.querySelector('#oSection-orders [data-order-id="' + oid + '"]');
                        if (card) {
                            card.style.transition = 'opacity .35s';
                            card.style.opacity = '0';
                            setTimeout(function () { card.remove(); checkOrderEmpty(); }, 350);
                        }
                        delete snapOrders[oid];
                    } else {
                        updateOrderCard(order);
                        var colors = { preparing:'#3b82f6', ready:'#16a34a', served:'#8b5cf6' };
                        var branchInfo = (!currentBranchId && order.branch_name) ? ' [' + order.branch_name + ']' : '';
toast('Order #' + order.id + ' (' + orderLabel(order) + branchInfo + ') -> ' + ucfirst(order.status), colors[order.status] || '#6b7280');


                    }
                } else {
                    updateOrderCard(order);
                }
            });

            Object.keys(snapOrders).forEach(function (oid) {
                if (!seenOrders[oid]) {
                    delete snapOrders[oid];
                    var card = document.querySelector('#oSection-orders [data-order-id="' + oid + '"]');
                    if (card) { card.style.opacity = '0'; setTimeout(function () { card.remove(); checkOrderEmpty(); }, 350); }
                }
            });


            var seenPayments = {};
            (data.payment_orders || []).forEach(function (order) {
                var oid = String(order.id);
                seenPayments[oid] = true;
                if (!snapPayments[oid]) {
                    snapPayments[oid] = true;
                    var list = document.getElementById('oPaymentOrdersList');
                    if (list && !list.querySelector('[data-payment-order-id="' + oid + '"]')) {
                        var empty = list.querySelector('div:not([data-payment-order-id])');
                        if (empty && empty.textContent.includes('No pending')) empty.remove();
                        var card = buildPaymentCard(order);
                        list.appendChild(card);
                        requestAnimationFrame(function () { card.style.opacity = '1'; });
                        refreshPaymentBadge();
                        var branchInfo = (!currentBranchId && order.branch_name) ? ' [' + order.branch_name + ']' : '';
toast('Order #' + order.id + ' (' + orderLabel(order) + branchInfo + ') ready for payment!', '#dc2626');

                    }
                }
            });

            Object.keys(snapPayments).forEach(function (oid) {
                if (!seenPayments[oid]) {
                    delete snapPayments[oid];
                    var card = document.querySelector('[data-payment-order-id="' + oid + '"]');
                    if (card) {
                        card.style.transition = 'opacity .35s';
                        card.style.opacity = '0';
                        setTimeout(function () { card.remove(); refreshPaymentBadge(); checkPaymentEmpty(); }, 350);
                    }
                }
            });
        })
        .catch(function (err) { console.error('Poll error:', err); });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            buildSnapshot();
            setInterval(poll, 7000);
            setTimeout(poll, 1000);
        });
    } else {
        buildSnapshot();
        setInterval(poll, 7000);
        setTimeout(poll, 1000);
    }

})();
</script>

@endsection
