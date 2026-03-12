@extends('layouts.admin')

@section('title', 'Kitchen Panel')

@section('content')
<style>
    .kitchen-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e3e6e8;
        padding-bottom: 0;
    }
    .kitchen-tab {
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        color: #666;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s ease;
    }
    .kitchen-tab:hover {
        color: #ff9900;
        border-bottom-color: #ff9900;
    }
    .kitchen-tab.active {
        color: #ff9900;
        border-bottom-color: #ff9900;
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
        color: #ff9900;
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
        border-color: #ff9900;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255,153,0,0.1);
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
        color: #ff9900;
    }
    .countdown-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="section-title"><i class="fas fa-fire-burner me-2"></i>Kitchen Panel</h1>
    <div class="d-flex gap-2">
        <span class="badge-custom badge-pending">{{ $orders->where('status', 'pending')->count() }} Pending</span>
        <span class="badge-custom badge-processing">{{ $orders->where('status', 'preparing')->count() }} Preparing</span>
        <span class="badge-custom badge-completed">{{ $orders->where('status', 'ready')->count() }} Ready</span>
    </div>
</div>

<div class="kitchen-tabs">
    <a href="#" class="kitchen-tab active" onclick="filterOrders('all'); return false;">
        <i class="fas fa-th me-2"></i>All Orders
    </a>
    <a href="#" class="kitchen-tab" onclick="filterOrders('pending'); return false;">
        <i class="fas fa-clock me-2"></i>Pending
    </a>
    <a href="#" class="kitchen-tab" onclick="filterOrders('preparing'); return false;">
        <i class="fas fa-fire me-2"></i>Preparing
    </a>
    <a href="#" class="kitchen-tab" onclick="filterOrders('ready'); return false;">
        <i class="fas fa-fire me-2"></i>Ready to Serve
    </a>
    <a href="#" class="kitchen-tab" onclick="filterOrders('served'); return false;">
        <i class="fas fa-check me-2"></i>Served
    </a>
    <a href="#" class="kitchen-tab" onclick="filterOrders('paid'); return false;">
        <i class="fas fa-check me-2"></i>Paid
    </a>
</div>

<div id="orders-container" class="row g-4">
    @forelse($orders as $order)
    <div class="col-md-6 col-lg-4 order-item" data-order-id="{{ $order->id }}" data-status="{{ $order->status }}">
        <div class="order-card {{ $order->status }}">
            <div class="order-header">
                <div>
                    <div class="order-table">Table {{ $order->table->table_number }}</div>
                    <div class="order-id">Order #{{ $order->id }}</div>
                    <div class="order-time">
                        <i class="fas fa-clock me-1"></i>{{ $order->created_at->diffForHumans() }}
                    </div>
                </div>
                <span class="status-badge-kitchen status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            
            <div class="items-list">
                <div class="items-title">
                    <i class="fas fa-utensils me-2"></i>Order Items
                </div>
                @foreach($order->items as $item)
                <div class="item-row">
                    <span class="item-name">{{ $item->menuItem->name }}</span>
                    <span class="item-qty">x{{ $item->quantity }}</span>
                </div>
                @endforeach
            </div>
            
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
            @elseif($order->status === 'served')
            <div class="text-center py-3">
                <i class="fas fa-utensils" style="font-size: 48px; color: #9333ea;"></i>
                <div class="mt-2" style="color: #6b21a8; font-weight: 600;">Served to Customer</div>
            </div>
            @else
            <div class="text-center py-3">
                <i class="fas fa-check-circle" style="font-size: 48px; color: #6b7280;"></i>
                <div class="mt-2" style="color: #4b5563; font-weight: 600;">Payment Completed</div>
            </div>
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

<script>
function filterOrders(status) {
    // Update active tab
    document.querySelectorAll('.kitchen-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.closest('.kitchen-tab').classList.add('active');
    
    // Filter orders
    document.querySelectorAll('.order-item').forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function startPreparing(orderId) {
    fetch(`/admin/cook/${orderId}/start`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(() => location.reload());
}

function markReady(orderId) {
    fetch(`/admin/cook/${orderId}/ready`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(() => location.reload());
}

function markServed(orderId) {
    fetch(`/admin/cook/${orderId}/served`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(() => location.reload());
}
</script>
@endsection
