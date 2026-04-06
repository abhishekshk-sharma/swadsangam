@extends('layouts.admin')
@php use Illuminate\Support\Facades\URL; @endphp

@section('title', 'Kitchen Panel')

@section('content')
<style>
    .kitchen-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e3e6e8;
        padding-bottom: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .kitchen-tabs::-webkit-scrollbar { display: none; }
    .kitchen-tab {
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #666;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .kitchen-tab:hover {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }
    .kitchen-tab.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
    }
    .order-card {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #e3e6e8;
        transition: all 0.2s ease;
    }
    .order-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .order-card.pending {
        border-left: 4px solid #ff9900;
    }
    .order-card.cooking {
        border-left: 4px solid #4facfe;
    }
    .order-card.ready {
        border-left: 4px solid #43e97b;
    }
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f0f0;
    }
    .order-table {
        font-size: 18px;
        font-weight: 700;
        color: #232f3e;
    }
    .order-id {
        font-size: 13px;
        color: #666;
        margin-top: 4px;
    }
    .order-time {
        font-size: 12px;
        color: #999;
    }
    .status-badge-kitchen {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-cooking {
        background: #cfe2ff;
        color: #084298;
    }
    .status-ready {
        background: #d1e7dd;
        color: #0f5132;
    }
    .status-served {
        background: #e0d7ff;
        color: #4c1d95;
    }
    .status-checkout {
        background: #d1fae5;
        color: #065f46;
    }
    .items-list {
        margin-bottom: 16px;
    }
    .items-title {
        font-size: 13px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .item-row {
        padding: 8px 0;
        border-bottom: 1px solid #f9f9f9;
        display: flex;
        justify-content: space-between;
    }
    .item-name {
        font-size: 14px;
        color: #232f3e;
    }
    .item-qty {
        font-size: 14px;
        font-weight: 700;
        color: #3b82f6;
    }
    .prep-input {
        width: 80px;
        border: 1px solid #d5d9d9;
        border-radius: 4px;
        padding: 8px 12px;
        font-size: 14px;
        text-align: center;
    }
    .prep-input:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
    .countdown-display {
        background: #fff3e0;
        padding: 12px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 12px;
    }
    .countdown-time {
        font-size: 24px;
        font-weight: 700;
        color: #3b82f6;
    }
    .countdown-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-fire-burner me-2"></i>Kitchen Panel</h1>
</div>

{{-- Filters --}}
<div class="content-card mb-4">
    <div class="card-body" style="padding:14px 20px;">
        <form method="GET" action="{{ route('admin.cook.index') }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            @if($branches->count() > 0)
            <div style="display:flex;flex-direction:column;gap:5px;">
                <label style="font-size:12px;font-weight:600;color:var(--gray-600);"><i class="fas fa-store me-1"></i>Branch</label>
                <select name="branch_id" onchange="this.form.submit()" style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;min-width:160px;">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div style="display:flex;flex-direction:column;gap:5px;">
                <label style="font-size:12px;font-weight:600;color:var(--gray-600);"><i class="fas fa-calendar me-1"></i>From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                    style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;">
            </div>
            <div style="display:flex;flex-direction:column;gap:5px;">
                <label style="font-size:12px;font-weight:600;color:var(--gray-600);"><i class="fas fa-calendar me-1"></i>To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}"
                    style="padding:7px 12px;border:1px solid var(--gray-300);border-radius:8px;font-size:13px;background:#fff;">
            </div>
            <button type="submit" style="padding:7px 16px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;align-self:flex-end;">
                <i class="fas fa-search me-1"></i>Filter
            </button>
            @if($selectedBranch || $dateFrom !== today()->toDateString() || $dateTo !== today()->toDateString())
            <a href="{{ route('admin.cook.index') }}" style="padding:7px 14px;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;align-self:flex-end;">
                <i class="fas fa-times me-1"></i>Clear
            </a>
            @endif
        </form>
        @if($dateFrom !== $dateTo)
        <div style="margin-top:8px;font-size:12px;color:#6b7280;">
            <i class="fas fa-info-circle me-1"></i>Showing orders from <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong>
        </div>
        @endif
    </div>
</div>

{{-- Stats Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr) repeat(2,1fr);gap:12px;margin-bottom:20px;">
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb;border-left:4px solid #6b7280;text-align:center;">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">Total</div>
        <div style="font-size:26px;font-weight:700;color:#111827;">{{ $stats['total'] }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb;border-left:4px solid #f59e0b;text-align:center;cursor:pointer;" onclick="quickFilter('pending')">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">Pending</div>
        <div style="font-size:26px;font-weight:700;color:#b45309;">{{ $stats['pending'] }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb;border-left:4px solid #3b82f6;text-align:center;cursor:pointer;" onclick="quickFilter('preparing')">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">Preparing</div>
        <div style="font-size:26px;font-weight:700;color:#1d4ed8;">{{ $stats['preparing'] }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb;border-left:4px solid #22c55e;text-align:center;cursor:pointer;" onclick="quickFilter('ready')">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">Ready</div>
        <div style="font-size:26px;font-weight:700;color:#15803d;">{{ $stats['ready'] }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb;border-left:4px solid #16a34a;text-align:center;cursor:pointer;" onclick="quickFilter('paid')">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">Paid</div>
        <div style="font-size:26px;font-weight:700;color:#15803d;">{{ $stats['paid'] }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:2px;">₹{{ number_format($stats['revenue'], 0) }}</div>
    </div>
    <div style="background:#fff;border-radius:10px;padding:14px 16px;border:1px solid #e5e7eb;border-left:4px solid #dc2626;text-align:center;cursor:pointer;" onclick="quickFilter('cancelled')">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:4px;">Cancelled</div>
        <div style="font-size:26px;font-weight:700;color:#dc2626;">{{ $stats['cancelled'] }}</div>
    </div>
</div>

{{-- Master type tabs --}}
<div style="display:flex;gap:10px;margin-bottom:12px;">
    <button onclick="switchType('table')" id="masterTab-table"
        style="display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;border:2px solid #1e3a5f;background:#1e3a5f;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s;">
        🍽️ Table Orders
        <span id="masterCount-table" style="background:rgba(255,255,255,0.25);border-radius:20px;padding:1px 8px;font-size:12px;">{{ $orders->where('is_parcel', false)->count() }}</span>
    </button>
    <button onclick="switchType('parcel')" id="masterTab-parcel"
        style="display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;border:2px solid #d1d5db;background:#fff;color:#6b7280;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s;">
        📦 Parcel Orders
        <span id="masterCount-parcel" style="background:#f3f4f6;border-radius:20px;padding:1px 8px;font-size:12px;color:#374151;">{{ $orders->where('is_parcel', true)->count() }}</span>
    </button>
</div>

{{-- Status sub-tabs --}}
<div class="kitchen-tabs">
    <a href="#" class="kitchen-tab" data-status="all" onclick="filterOrders('all'); return false;"><i class="fas fa-th me-2"></i>All</a>
    <a href="#" class="kitchen-tab" data-status="pending" onclick="filterOrders('pending'); return false;"><i class="fas fa-clock me-2"></i>Pending</a>
    <a href="#" class="kitchen-tab" data-status="preparing" onclick="filterOrders('preparing'); return false;"><i class="fas fa-fire me-2"></i>Preparing</a>
    <a href="#" class="kitchen-tab" data-status="ready" onclick="filterOrders('ready'); return false;"><i class="fas fa-check-circle me-2"></i>Ready</a>
    <a href="#" class="kitchen-tab" data-status="served" onclick="filterOrders('served'); return false;"><i class="fas fa-check me-2"></i>Served</a>
    <a href="#" class="kitchen-tab" data-status="checkout" onclick="filterOrders('checkout'); return false;"><i class="fas fa-sign-out-alt me-2"></i>Checkout</a>
    <a href="#" class="kitchen-tab" data-status="paid" onclick="filterOrders('paid'); return false;"><i class="fas fa-rupee-sign me-2"></i>Paid</a>
    <a href="#" class="kitchen-tab" data-status="cancelled" onclick="filterOrders('cancelled'); return false;"><i class="fas fa-ban me-2"></i>Cancelled</a>
</div>

<div id="orders-container" class="row g-4">
    @forelse($orders as $order)
    <div class="col-md-6 col-lg-4 order-item" data-order-id="{{ $order->id }}" data-order-status="{{ $order->status }}" data-status="{{ $order->status }}" data-type="{{ $order->is_parcel ? 'parcel' : 'table' }}" data-created-at="{{ $order->created_at->timestamp }}">
        <div class="order-card {{ $order->status }}">
            <div class="order-header">
                <div>
                    <div class="order-table">Order #{{ $order->daily_number ?? $order->id }}</div>
                    <div class="mt-1 d-flex align-items-center gap-2">
                        @if($order->is_parcel)
                            <span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">📦 Parcel</span>
                        @else
                            <span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">T{{ $order->table->table_number }}</span>
                            @if($order->table->category)
                                <span style="background:#e0e7ff;color:#3730a3;font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;letter-spacing:0.02em;">{{ $order->table->category->name }}</span>
                            @endif
                        @endif
                    </div>
                    <div class="order-time">
                        <i class="fas fa-clock me-1"></i>{{ $order->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    @if(in_array($order->status, ['paid', 'cancelled']))
                        @php $dur = (int) $order->created_at->diffInMinutes($order->updated_at); @endphp
                        <span class="order-timer-admin {{ $order->status === 'paid' ? 'timer-ok' : 'timer-late' }}">⏱ {{ $dur }}m</span>
                    @else
                        <span class="order-timer-admin" data-timer></span>
                    @endif
                    <span class="status-badge-kitchen status-{{ $order->status }}" data-order-status-badge>
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
            
            <div class="items-list">
                <div class="items-title">
                    <i class="fas fa-utensils me-2"></i>Order Items
                </div>
                @foreach($order->items as $item)
                <div class="item-row" data-item-id="{{ $item->id }}" data-item-status="{{ $item->status }}">
                    <div class="flex-grow-1">
                        <span class="item-name {{ $item->status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}" data-item-name>{{ $item->menuItem->name }}</span>
                        @if($item->status === 'cancelled')
                            <span style="font-size:11px;color:#dc3545;"> (cancelled)</span>
                        @endif
                        @if($item->notes)
                            <div style="font-size: 11px; color: #3b82f6; font-style: italic; margin-top: 2px;">
                                → {{ $item->notes }}
                            </div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-1" data-item-actions>
                        <span class="item-qty">x{{ $item->quantity }}</span>
                        @if(!in_array($order->status, ['paid','cancelled']) && $item->status !== 'cancelled')
                            <button onclick="toggleAdminEdit('aedit-{{ $item->id }}')" 
                                    style="font-size:11px;padding:2px 6px;border:1px solid #3b82f6;background:#eff6ff;color:#1d4ed8;border-radius:4px;cursor:pointer;">Edit</button>
                            <form action="{{ route('admin.cook.orderItems.cancel', $item->id) }}" method="POST" class="mb-0">
                                @csrf @method('PATCH')
                                <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;">Cancel</button>
                            </form>
                        @endif
                    </div>
                </div>
                @if(!in_array($order->status, ['paid','cancelled']) && $item->status !== 'cancelled')
                <div id="aedit-{{ $item->id }}" style="display:none;background:#f8fafc;border-radius:6px;padding:8px;margin-top:4px;">
                    <form action="{{ route('admin.cook.orderItems.update', $item->id) }}" method="POST" class="d-flex flex-column gap-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                        <input type="hidden" name="date_to" value="{{ $dateTo }}">
                        <div class="d-flex align-items-center gap-2">
                            <label style="font-size:12px;color:#666;min-width:40px;">Qty:</label>
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                   style="width:70px;border:1px solid #d5d9d9;border-radius:4px;padding:4px 8px;font-size:13px;">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label style="font-size:12px;color:#666;min-width:40px;">Note:</label>
                            <input type="text" name="notes" value="{{ $item->notes }}"
                                   style="flex:1;border:1px solid #d5d9d9;border-radius:4px;padding:4px 8px;font-size:13px;" placeholder="Special request...">
                        </div>
                        <button style="align-self:flex-end;background:#2563eb;color:white;border:none;padding:4px 12px;border-radius:4px;font-size:12px;cursor:pointer;">Save</button>
                    </form>
                </div>
                @endif
                @endforeach
            </div>
            
            @if($order->customer_notes)
            <div class="mb-3" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 4px;">
                <div style="font-size: 12px; font-weight: 600; color: #856404; text-transform: uppercase; margin-bottom: 4px;">
                    <i class="fas fa-info-circle me-1"></i>Customer Request:
                </div>
                <div style="font-size: 13px; color: #664d03; font-style: italic;">{{ $order->customer_notes }}</div>
            </div>
            @endif
            
            @if($order->status === 'pending')
            <button onclick="startPreparing({{ $order->id }})" class="btn-success w-100">
                <i class="fas fa-play me-1"></i>Prepare
            </button>
            @elseif($order->status === 'preparing')
            <button onclick="markReady({{ $order->id }})" class="btn-success w-100">
                <i class="fas fa-check me-1"></i>Mark as Ready
            </button>
            @elseif($order->status === 'ready')
            <button onclick="markServed({{ $order->id }})" class="btn-success w-100">
                <i class="fas fa-utensils me-1"></i>Mark as Served
            </button>
            @elseif(in_array($order->status, ['served', 'checkout']))
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
            <div class="mb-3">
                @if($gst['enabled'])
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:10px;font-size:13px;">
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
                <div style="font-size:18px;font-weight:700;color:#16a34a;text-align:center;margin-bottom:12px;">
                    Total: ₹{{ number_format($grandTotal, 2) }}
                </div>
                <button onclick="openPaymentModal({{ $order->id }}, {{ $grandTotal }}, '{{ $order->is_parcel ? 'Parcel' : $order->table?->table_number }}')" class="btn-primary w-100">
                    <i class="fas fa-money-bill me-1"></i>Take Payment
                </button>
            </div>
            @elseif($order->status === 'cancelled')
            <div class="text-center py-2">
                <span style="color:#dc3545;font-weight:600;"><i class="fas fa-times-circle me-1"></i>Order Cancelled</span>
            </div>
            @else
            <div class="text-center py-3">
                <i class="fas fa-check-circle" style="font-size: 48px; color: #16a34a;"></i>
                <div class="mt-2" style="color: #15803d; font-weight: 600;">Payment Completed</div>
                <div style="font-size: 14px; color: #666; margin-top: 4px;">₹{{ number_format($order->total_amount, 2) }} - {{ ucfirst($order->payment_mode ?? 'cash') }}</div>
                <button onclick="showAdminQr({{ $order->id }})" class="mt-3 w-100" style="background:#f0fdf4;border:2px solid #86efac;color:#15803d;border-radius:8px;padding:10px;font-size:14px;font-weight:600;cursor:pointer;">
                    <i class="fas fa-qrcode me-2"></i>Show Bill QR
                </button>
            </div>
            @endif
            @if(!in_array($order->status, ['paid','cancelled']))
            <form action="{{ route('admin.cook.orders.cancel', $order->id) }}" method="POST" class="mt-2"
                  onsubmit="return confirm('Cancel entire order #{{ $order->id }}?')">
                @csrf @method('PATCH')
                <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                <button class="w-100" style="background:#fee2e2;color:#dc2626;border:none;padding:8px;border-radius:6px;font-size:13px;font-weight:600;">
                    <i class="fas fa-ban me-1"></i>Cancel Order
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="empty-state">
            <i class="fas fa-fire-burner"></i>
            <p>No active orders in kitchen</p>
        </div>
    </div>
    @endforelse
</div>


<div id="paymentModal" class="modal fade" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2a4f7c 100%); color: white; border-radius: 12px 12px 0 0;">
                <div>
                    <h5 class="modal-title" style="margin: 0; font-weight: 700;">Process Payment</h5>
                    <p style="margin: 4px 0 0 0; font-size: 13px; opacity: 0.9;">Order #<span id="modalOrderId"></span> - <span id="modalTableNumber"></span></p>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="closePaymentModal()"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <form id="paymentForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="branch_id" value="{{ $selectedBranch }}">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    <input type="hidden" name="grand_total" id="modalGrandTotal">

                    <div class="mb-4" style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 8px; padding: 16px; text-align: center;">
                        <div style="font-size: 14px; color: #166534; font-weight: 600; margin-bottom: 4px;">Total Amount</div>
                        <div id="modalTotalAmount" style="font-size: 32px; font-weight: 700; color: #15803d;">₹0.00</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 600; color: #232f3e;">Payment Method</label>
                        <div id="paymentMethodGrid" class="row g-2"></div>
                        <input type="hidden" name="payment_mode" id="paymentMode" required>
                    </div>

                    <div id="cashSection" style="display: none;">
                        <label class="form-label" style="font-weight: 600; color: #232f3e;">Cash Received</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" style="background: #f7f8f9; border: 1px solid #d5d9d9;">₹</span>
                            <input type="number" step="0.01" min="0" id="cashReceived" class="form-control" placeholder="Enter amount" style="border: 1px solid #d5d9d9; padding: 12px;">
                            <button type="button" onclick="calculateChange()" class="btn-primary" style="padding: 12px 24px;">Calculate</button>
                        </div>
                    </div>

                    <div id="changeSection" style="display: none; background: #fef3c7; border: 2px solid #fbbf24; border-radius: 8px; padding: 16px; text-align: center; margin-bottom: 16px;">
                        <div style="font-size: 13px; color: #92400e; font-weight: 600; margin-bottom: 4px;">Change to Return</div>
                        <div id="changeAmount" style="font-size: 28px; font-weight: 700; color: #b45309;">₹0.00</div>
                    </div>

                    <button type="submit" id="submitPaymentBtn" class="btn-success w-100" style="padding: 14px; font-size: 16px; font-weight: 600;" disabled>
                        <i class="fas fa-check-circle me-2"></i>Complete Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="modalBackdrop" class="modal-backdrop fade" style="display: none;"></div>

<!-- Bill QR Modal -->
<div id="adminQrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;width:100%;max-width:360px;margin:auto;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
        <div style="color:#16a34a;font-size:48px;margin-bottom:8px;">✅</div>
        <h5 style="font-weight:700;margin-bottom:4px;">Bill QR Code</h5>
        <p style="font-size:13px;color:#666;margin-bottom:16px;">Customer can scan to view their bill</p>
        <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:16px;">
            <div id="adminQrContainer"></div>
        </div>
        <a id="adminBillLink" href="#" target="_blank" style="font-size:13px;color:#2563eb;word-break:break-all;display:block;margin-bottom:16px;"></a>
        <div style="display:flex;gap:8px;">
            <button onclick="closeAdminQr()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Close</button>
            <a id="adminOpenBillBtn" href="#" target="_blank" style="flex:1;background:#2563eb;color:#fff;border-radius:8px;padding:10px;font-weight:600;text-decoration:none;display:inline-block;">Open Bill</a>
        </div>
    </div>
</div>

<!-- UPI QR Modal -->
<div id="upiQrModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:10000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:340px;margin:auto;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.25);">
        <h5 style="font-weight:700;margin-bottom:4px;">📱 UPI Payment</h5>
        <p style="font-size:13px;color:#6b7280;margin-bottom:8px;">Ask customer to scan with Google Pay / PhonePe</p>
        <div style="font-size:22px;font-weight:700;color:#16a34a;margin-bottom:12px;" id="upiAmountDisplay"></div>
        <div style="background:#f9fafb;border-radius:12px;padding:16px;display:flex;justify-content:center;margin-bottom:12px;">
            <div id="upiQrContainer"></div>
        </div>
        <p style="font-size:12px;color:#9ca3af;margin-bottom:16px;" id="upiIdDisplay"></p>
        <div style="display:flex;gap:8px;">
            <button onclick="closeUpiModal()" style="flex:1;background:#f3f4f6;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">Cancel</button>
            <button onclick="confirmUpiPayment()" style="flex:1;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:10px;font-weight:600;cursor:pointer;">✓ Payment Received</button>
        </div>
    </div>
</div>

<style>
.payment-mode-btn:hover {
    border-color: #3b82f6 !important;
    background: #eff6ff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59,130,246,0.2);
}
.payment-mode-btn.selected {
    border-color: #3b82f6 !important;
    background: #eff6ff !important;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
}
.order-timer-admin {
    font-size: 11px;
    font-weight: 700;
    font-family: monospace;
    letter-spacing: 0.04em;
    padding: 2px 8px;
    border-radius: 20px;
}
.order-timer-admin.timer-ok   { background:#dcfce7; color:#15803d; }
.order-timer-admin.timer-warn { background:#fef9c3; color:#a16207; }
.order-timer-admin.timer-late { background:#fee2e2; color:#b91c1c; animation:adminTimerPulse 1s ease-in-out infinite; }
@keyframes adminTimerPulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>

<script>
var STORAGE_KEY = 'adminCookTab_{{ $dateFrom }}_{{ $dateTo }}_{{ $selectedBranch ?? 0 }}';
var activeType   = localStorage.getItem(STORAGE_KEY + '_type')   || 'table';
var activeStatus = localStorage.getItem(STORAGE_KEY + '_status') || 'all';
var BRANCH_UPI_ID = '{{ $branchUpiId }}';
var BRANCH_GST    = @json($branchGst);
var upiPendingOrderId = null;
var currentTotalAmount = 0;
var currentOrderId = null;

function saveState() {
    localStorage.setItem(STORAGE_KEY + '_type',   activeType);
    localStorage.setItem(STORAGE_KEY + '_status', activeStatus);
}

function switchType(type) {
    activeType = type;
    saveState();
    updateMasterTabs();
    applyFilters();
}

function filterOrders(status) {
    activeStatus = status;
    saveState();
    updateStatusTabs();
    applyFilters();
}

function quickFilter(status) {
    activeStatus = status;
    saveState();
    updateStatusTabs();
    document.querySelectorAll('.order-item').forEach(function(item) {
        item.style.display = (activeStatus === 'all' || item.dataset.status === activeStatus) ? 'block' : 'none';
    });
}

function updateMasterTabs() {
    var tableBtn  = document.getElementById('masterTab-table');
    var parcelBtn = document.getElementById('masterTab-parcel');
    if (activeType === 'table') {
        tableBtn.style.background  = '#1e3a5f'; tableBtn.style.color  = '#fff'; tableBtn.style.borderColor  = '#1e3a5f';
        parcelBtn.style.background = '#fff';    parcelBtn.style.color = '#6b7280'; parcelBtn.style.borderColor = '#d1d5db';
    } else {
        parcelBtn.style.background = '#ea580c'; parcelBtn.style.color = '#fff'; parcelBtn.style.borderColor = '#ea580c';
        tableBtn.style.background  = '#fff';    tableBtn.style.color  = '#6b7280'; tableBtn.style.borderColor  = '#d1d5db';
    }
}

function updateStatusTabs() {
    document.querySelectorAll('.kitchen-tab').forEach(function(tab) {
        tab.classList.toggle('active', tab.dataset.status === activeStatus);
    });
}

function applyFilters() {
    document.querySelectorAll('.order-item').forEach(function(item) {
        var typeMatch   = item.dataset.type === activeType;
        var statusMatch = activeStatus === 'all' || item.dataset.status === activeStatus;
        item.style.display = (typeMatch && statusMatch) ? 'block' : 'none';
    });
}

function toggleAdminEdit(id) {
    var el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function startPreparing(orderId) {
    saveState();
    fetch('/admin/cook/' + orderId + '/start', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(function(r) { return r.json(); }).then(function() { reloadWithFilters(); });
}

function markReady(orderId) {
    saveState();
    fetch('/admin/cook/' + orderId + '/ready', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(function(r) { return r.json(); }).then(function() { reloadWithFilters(); });
}

function markServed(orderId) {
    saveState();
    fetch('/admin/cook/' + orderId + '/served', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    }).then(function(r) { return r.json(); }).then(function() { reloadWithFilters(); });
}

function reloadWithFilters() {
    var params = new URLSearchParams(window.location.search);
    if (!params.has('date_from')) params.set('date_from', '{{ $dateFrom }}');
    if (!params.has('date_to'))   params.set('date_to',   '{{ $dateTo }}');
    @if($selectedBranch)
    if (!params.has('branch_id')) params.set('branch_id', '{{ $selectedBranch }}');
    @endif
    window.location.href = '/admin/cook?' + params.toString();
}

// ── Payment Modal ─────────────────────────────────────────────────────────
function openPaymentModal(orderId, grandTotal, tableLabel) {
    currentOrderId     = orderId;
    currentTotalAmount = grandTotal;
    document.getElementById('modalOrderId').textContent     = orderId;
    document.getElementById('modalTableNumber').textContent = tableLabel;
    document.getElementById('modalTotalAmount').textContent = '₹' + parseFloat(grandTotal).toFixed(2);
    document.getElementById('modalGrandTotal').value        = grandTotal;
    document.getElementById('paymentForm').action           = '/admin/cook/' + orderId + '/payment';
    document.getElementById('paymentMode').value            = '';
    document.getElementById('cashReceived').value           = '';
    document.getElementById('cashSection').style.display    = 'none';
    document.getElementById('changeSection').style.display  = 'none';
    document.getElementById('submitPaymentBtn').disabled    = true;

    // Build payment method buttons based on branch UPI
    var grid = document.getElementById('paymentMethodGrid');
    grid.innerHTML = '';
    var methods = BRANCH_UPI_ID
        ? [['cash','💵','Cash'],['upi','📱','UPI']]
        : [['cash','💵','Cash']];
    var colClass = BRANCH_UPI_ID ? 'col-6' : 'col-12';
    methods.forEach(function(m) {
        var div = document.createElement('div');
        div.className = colClass;
        div.innerHTML = '<button type="button" onclick="selectPaymentMode(\''+m[0]+'\')" class="payment-mode-btn w-100" data-mode="'+m[0]+'" style="padding:16px;border:2px solid #d5d9d9;border-radius:8px;background:#fff;cursor:pointer;transition:all .2s;">'
            + '<div style="font-size:32px;margin-bottom:8px;">'+m[1]+'</div>'
            + '<div style="font-size:13px;font-weight:600;">'+m[2]+'</div></button>';
        grid.appendChild(div);
    });

    document.getElementById('paymentModal').style.display  = 'block';
    document.getElementById('paymentModal').classList.add('show');
    document.getElementById('modalBackdrop').style.display = 'block';
    document.getElementById('modalBackdrop').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display  = 'none';
    document.getElementById('paymentModal').classList.remove('show');
    document.getElementById('modalBackdrop').style.display = 'none';
    document.getElementById('modalBackdrop').classList.remove('show');
    document.body.style.overflow = 'auto';
}

function selectPaymentMode(mode) {
    document.querySelectorAll('.payment-mode-btn').forEach(function(b) { b.classList.remove('selected'); });
    var btn = document.querySelector('[data-mode="'+mode+'"]');
    if (btn) btn.classList.add('selected');
    document.getElementById('paymentMode').value = mode;
    document.getElementById('cashSection').style.display   = mode === 'cash' ? 'block' : 'none';
    document.getElementById('changeSection').style.display = 'none';
    if (mode === 'upi') {
        document.getElementById('submitPaymentBtn').disabled = true;
        showUpiQr(currentOrderId, currentTotalAmount, BRANCH_UPI_ID);
    } else {
        document.getElementById('submitPaymentBtn').disabled = mode === 'cash';
    }
}

function showUpiQr(orderId, amount, upiId) {
    upiPendingOrderId = orderId;
    var upiUri = 'upi://pay?pa=' + encodeURIComponent(upiId) + '&am=' + parseFloat(amount).toFixed(2) + '&cu=INR';
    document.getElementById('upiAmountDisplay').textContent = '₹' + parseFloat(amount).toFixed(2);
    document.getElementById('upiIdDisplay').textContent     = 'UPI ID: ' + upiId;
    var container = document.getElementById('upiQrContainer');
    container.innerHTML = '';
    new QRCode(container, { text: upiUri, width: 200, height: 200, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
    document.getElementById('upiQrModal').style.display = 'flex';
}

function closeUpiModal() {
    document.getElementById('upiQrModal').style.display = 'none';
    // reset UPI button
    document.querySelectorAll('.payment-mode-btn').forEach(function(b) { b.classList.remove('selected'); });
    document.getElementById('paymentMode').value = '';
    upiPendingOrderId = null;
}

function confirmUpiPayment() {
    document.getElementById('upiQrModal').style.display  = 'none';
    document.getElementById('submitPaymentBtn').disabled = false;
    document.getElementById('submitPaymentBtn').click();
}

function calculateChange() {
    var cash = parseFloat(document.getElementById('cashReceived').value);
    if (!cash || cash < currentTotalAmount) {
        alert('Cash received must be at least ₹' + parseFloat(currentTotalAmount).toFixed(2));
        return;
    }
    document.getElementById('changeAmount').textContent    = '₹' + (cash - currentTotalAmount).toFixed(2);
    document.getElementById('changeSection').style.display = 'block';
    document.getElementById('submitPaymentBtn').disabled   = false;
}

document.addEventListener('DOMContentLoaded', function() {
    updateMasterTabs();
    updateStatusTabs();
    applyFilters();

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!document.getElementById('paymentMode').value) {
            alert('Please select a payment method');
            return;
        }
        var submitBtn = document.getElementById('submitPaymentBtn');
        submitBtn.disabled    = true;
        submitBtn.textContent = 'Processing…';

        fetch(this.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(this),
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closePaymentModal();
                showAdminQr(res.order_id, res.bill_url);
                // Update the order card status without full reload
                var card = document.querySelector('[data-order-id="'+res.order_id+'"]');
                if (card) {
                    card.dataset.status = 'paid';
                    card.querySelector('[data-order-status-badge]') && (card.querySelector('[data-order-status-badge]').textContent = 'Paid');
                }
                saveState();
                reloadWithFilters();
            } else {
                submitBtn.disabled    = false;
                submitBtn.innerHTML   = '<i class="fas fa-check-circle me-2"></i>Complete Payment';
                alert(res.message || 'Payment failed. Please try again.');
            }
        })
        .catch(function() {
            submitBtn.disabled    = false;
            submitBtn.innerHTML   = '<i class="fas fa-check-circle me-2"></i>Complete Payment';
            alert('Network error. Please try again.');
        });
    });
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const ADMIN_BILL_URLS = {
    @foreach($orders->where('status','paid') as $order)
    {{ $order->id }}: "{{ URL::signedRoute('bill.show', ['orderId' => $order->id]) }}",
    @endforeach
};
function showAdminQr(orderId, billUrl) {
    var url = billUrl || ADMIN_BILL_URLS[orderId];
    if (!url) return;
    document.getElementById('adminBillLink').textContent = url;
    document.getElementById('adminBillLink').href        = url;
    document.getElementById('adminOpenBillBtn').href     = url;
    var container = document.getElementById('adminQrContainer');
    container.innerHTML = '';
    new QRCode(container, { text: url, width: 200, height: 200, colorDark: '#111827', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
    document.getElementById('adminQrModal').style.display = 'flex';
}
function closeAdminQr() {
    document.getElementById('adminQrModal').style.display = 'none';
}
</script>


<script>
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
            if (elapsed >= 1200)     el.classList.add('timer-late');
            else if (elapsed >= 600) el.classList.add('timer-warn');
            else                     el.classList.add('timer-ok');
        });
    }
    document.addEventListener('DOMContentLoaded', function() { tick(); setInterval(tick, 60000); });
})();
</script>


@endsection
