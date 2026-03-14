/**
 * order-poll.js — Universal 5-second order polling for all panels.
 *
 * Each panel blade must define window.ORDER_POLL before including this script:
 *   window.ORDER_POLL = { panel: 'waiter' | 'cook' | 'cashier' | 'admin' };
 */

(function () {
    'use strict';

    // Snapshot of what the DOM currently knows about each order/item
    const domSnapshot = {};
    let pendingReload = false;

    // ── Toast ────────────────────────────────────────────────────────────────
    function toast(msg, type) {
        const colors = { info: '#3b82f6', success: '#16a34a', warning: '#d97706', danger: '#dc2626' };
        const el = document.createElement('div');
        el.style.cssText = `
            position:fixed;top:16px;left:50%;transform:translateX(-50%);
            background:${colors[type] || colors.info};color:#fff;
            padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;
            box-shadow:0 4px 12px rgba(0,0,0,.25);z-index:9999;
            animation:fadeInDown .3s ease;white-space:nowrap;
        `;
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    // ── Build initial snapshot from current DOM ───────────────────────────────
    function buildSnapshot() {
        document.querySelectorAll('[data-order-id]').forEach(card => {
            const oid = card.dataset.orderId;
            domSnapshot[oid] = {
                status: card.dataset.orderStatus || '',
                items: {}
            };
            card.querySelectorAll('[data-item-id]').forEach(row => {
                domSnapshot[oid].items[row.dataset.itemId] = row.dataset.itemStatus || '';
            });
        });
    }

    // ── Status label helpers ──────────────────────────────────────────────────
    const STATUS_LABELS = {
        pending:   { text: 'Pending',   cls: 'bg-yellow-100 text-yellow-800' },
        preparing: { text: 'Preparing', cls: 'bg-blue-100 text-blue-800' },
        ready:     { text: 'Ready',     cls: 'bg-green-100 text-green-800' },
        served:    { text: 'Served',    cls: 'bg-purple-100 text-purple-800' },
        paid:      { text: 'Paid',      cls: 'bg-gray-100 text-gray-700' },
        cancelled: { text: 'Cancelled', cls: 'bg-red-100 text-red-800' },
    };

    function applyStatusBadge(el, status) {
        const info = STATUS_LABELS[status] || { text: status, cls: '' };
        el.textContent = info.text;
        // Remove all known colour classes then apply new ones
        Object.values(STATUS_LABELS).forEach(s => {
            s.cls.split(' ').forEach(c => c && el.classList.remove(c));
        });
        info.cls.split(' ').forEach(c => c && el.classList.add(c));
    }

    // ── Panel-specific messages ───────────────────────────────────────────────
    const panel = (window.ORDER_POLL || {}).panel || 'unknown';

    function orderMsg(orderId, tableNum, oldStatus, newStatus) {
        const msgs = {
            waiter: {
                ready:     `🔔 Order #${orderId} (Table ${tableNum}) is READY to serve!`,
                cancelled: `❌ Order #${orderId} (Table ${tableNum}) was cancelled.`,
                preparing: `🍳 Order #${orderId} (Table ${tableNum}) is now being prepared.`,
                served:    `✅ Order #${orderId} (Table ${tableNum}) marked as served.`,
                paid:      `💰 Order #${orderId} (Table ${tableNum}) has been paid.`,
            },
            cook: {
                pending:   `🆕 New order #${orderId} arrived at Table ${tableNum}!`,
                cancelled: `❌ Order #${orderId} (Table ${tableNum}) was cancelled.`,
            },
            cashier: {
                served:    `💳 Order #${orderId} (Table ${tableNum}) is ready for payment!`,
                cancelled: `❌ Order #${orderId} (Table ${tableNum}) was cancelled.`,
                paid:      `✅ Order #${orderId} (Table ${tableNum}) payment done.`,
            },
            admin: {
                pending:   `🆕 New order #${orderId} at Table ${tableNum}.`,
                ready:     `✅ Order #${orderId} (Table ${tableNum}) is ready.`,
                served:    `🍽️ Order #${orderId} (Table ${tableNum}) served.`,
                paid:      `💰 Order #${orderId} (Table ${tableNum}) paid.`,
                cancelled: `❌ Order #${orderId} (Table ${tableNum}) cancelled.`,
            },
        };
        const panelMsgs = msgs[panel] || {};
        return panelMsgs[newStatus] || `Order #${orderId} status → ${newStatus}`;
    }

    function itemMsg(orderId, itemName, newStatus) {
        if (newStatus === 'prepared')  return `✓ "${itemName}" in Order #${orderId} is prepared.`;
        if (newStatus === 'cancelled') return `❌ "${itemName}" in Order #${orderId} was cancelled.`;
        return null;
    }

    function toastType(status) {
        if (['ready', 'served', 'paid', 'prepared'].includes(status)) return 'success';
        if (status === 'cancelled') return 'danger';
        if (status === 'pending')   return 'warning';
        return 'info';
    }

    // ── Cashier: build and inject a new order card ───────────────────────────
    function buildCashierCard(order) {
        const itemsHtml = order.items.map(item => {
            const cancelled = item.status === 'cancelled';
            const priceHtml = cancelled
                ? `<div class="text-gray-400 line-through text-sm">₹${(item.price * item.quantity).toFixed(2)}</div>`
                : `<div class="font-bold">₹${(item.price * item.quantity).toFixed(2)}</div>`;
            const notesHtml = item.notes
                ? `<div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ ${item.notes}</div>`
                : '';
            return `
            <div class="py-2 border-b" data-item-id="${item.id}" data-item-status="${item.status}">
                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold ${cancelled ? 'line-through text-gray-400' : ''}">${item.name}</span>
                            ${cancelled ? '<span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>' : ''}
                        </div>
                        <div class="text-sm text-gray-${cancelled ? '400' : '600'}">Qty: ${item.quantity}</div>
                        ${notesHtml}
                    </div>
                    <div class="text-right">${priceHtml}</div>
                </div>
            </div>`;
        }).join('');

        const customerNotesHtml = order.customer_notes ? `
        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
            <h4 class="font-semibold mb-1 text-sm text-yellow-800">Customer Request:</h4>
            <p class="text-sm text-gray-700 italic">${order.customer_notes}</p>
        </div>` : '';

        const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        const div = document.createElement('div');
        div.className = 'bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-red-500';
        div.setAttribute('data-order-id', order.id);
        div.setAttribute('data-order-status', order.status);
        div.innerHTML = `
        <div class="p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-lg">Order #${order.id}</h3>
                    <p class="text-sm text-gray-500">Table ${order.table_number} • ${order.created_at}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                    ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                </span>
            </div>
            <div class="space-y-2 mb-4">${itemsHtml}</div>
            ${customerNotesHtml}
            <div class="pt-3 border-t">
                <div class="font-bold text-xl text-green-600 mb-4" data-order-total>Total: ₹${order.total_amount.toFixed(2)}</div>
                <form action="/cashier/payments/${order.id}/process" method="POST" id="paymentForm${order.id}">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">Payment Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="selectPaymentMode(${order.id}, 'cash')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="${order.id}" data-mode="cash">💵 Cash</button>
                            <button type="button" onclick="selectPaymentMode(${order.id}, 'upi')"  class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="${order.id}" data-mode="upi">📱 UPI</button>
                            <button type="button" onclick="selectPaymentMode(${order.id}, 'card')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="${order.id}" data-mode="card">💳 Card</button>
                        </div>
                        <input type="hidden" name="payment_mode" id="paymentMode${order.id}" required>
                    </div>
                    <div id="cashSection${order.id}" class="mb-4" style="display:none">
                        <label class="block text-sm font-semibold mb-2">Cash Received</label>
                        <div class="flex gap-2">
                            <input type="number" step="0.01" min="0" id="cashReceived${order.id}" class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 text-lg" placeholder="Enter amount">
                            <button type="button" onclick="calculateChange(${order.id}, ${order.total_amount})" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">OK</button>
                        </div>
                    </div>
                    <div id="changeSection${order.id}" class="mb-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4" style="display:none">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-1">Change to Return</p>
                            <p class="text-3xl font-bold text-green-600" id="changeAmount${order.id}">₹0.00</p>
                        </div>
                    </div>
                    <button type="submit" id="submitBtn${order.id}" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg" style="display:none" disabled>Complete Payment</button>
                </form>
            </div>
        </div>`;
        return div;
    }

    // ── DOM update helpers ────────────────────────────────────────────────────
    function updateOrderCard(order) {
        const card = document.querySelector(`[data-order-id="${order.id}"]`);
        if (!card) return;

        card.dataset.orderStatus = order.status;

        const badge = card.querySelector('[data-order-status-badge]');
        if (badge) applyStatusBadge(badge, order.status);

        const totalEl = card.querySelector('[data-order-total]');
        if (totalEl) totalEl.textContent = '₹' + order.total_amount.toFixed(2);

        // Show/hide Add Items button based on order status (waiter panel)
        const addItemsBtn = card.querySelector('[data-add-items-btn]');
        if (addItemsBtn) {
            addItemsBtn.style.display = ['paid', 'cancelled'].includes(order.status) ? 'none' : '';
        }

        // Waiter panel: show/hide Serve button and inject it if needed
        if (panel === 'waiter') {
            const orderActions = card.querySelector('.order-actions');
            if (orderActions) {
                let serveBtn = card.querySelector('[data-serve-btn]');
                if (order.status === 'ready') {
                    if (!serveBtn) {
                        serveBtn = document.createElement('button');
                        serveBtn.setAttribute('data-serve-btn', '');
                        serveBtn.className = 'flex-1 bg-green-500 text-white px-4 py-2 rounded text-sm font-semibold';
                        serveBtn.textContent = 'Mark as Served';
                        serveBtn.onclick = () => markServed(order.id);
                        orderActions.appendChild(serveBtn);
                    }
                    serveBtn.style.display = '';
                } else if (serveBtn) {
                    serveBtn.style.display = 'none';
                }
            }
        }

        order.items.forEach(item => {
            const row = card.querySelector(`[data-item-id="${item.id}"]`);
            if (!row) return;

            const prevStatus = row.dataset.itemStatus;
            if (prevStatus === item.status) return;
            row.dataset.itemStatus = item.status;

            // Hide edit form if open
            const editForm = document.getElementById(`cedit-${item.id}`) ||
                             document.getElementById(`edit-${item.id}`) ||
                             document.getElementById(`aedit-${item.id}`);
            if (editForm) {
                editForm.classList.add('hidden');
                editForm.style.display = 'none';
            }

            // Update action buttons container
            const actions = row.querySelector('[data-item-actions]');
            if (!actions) return;

            if (item.status === 'cancelled') {
                actions.innerHTML = '<span style="font-size:12px;color:#ef4444;padding:0 4px;">Cancelled</span>';
                // Strike through item name
                const nameEl = row.querySelector('[data-item-name]');
                if (nameEl) nameEl.style.textDecoration = 'line-through';
            } else if (item.status === 'prepared') {
                actions.innerHTML = '<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:6px;font-size:13px;font-weight:600;">✓ Done</span>';
                const nameEl = row.querySelector('[data-item-name]');
                if (nameEl) nameEl.style.textDecoration = 'line-through';
            }
        });
    }

    // ── Busy detection ─────────────────────────────────────────────────────
    function isBusy() {
        // Cashier: payment modal is open
        // const paymentModal = document.getElementById('paymentModal');
        // if (paymentModal && paymentModal.style.display === 'block') return true;

        // Waiter: add-items modal is open
        const addItemsModal = document.getElementById('addItemsModal');
        if (addItemsModal && !addItemsModal.classList.contains('hidden')) return true;

        // Cook/Admin/Waiter: any inline edit form is visible
        const openEdit = document.querySelector(
            '[id^="cedit-"]:not(.hidden), [id^="edit-"]:not(.hidden), [id^="aedit-"][style*="display: block"], [id^="aedit-"][style*="display:block"]'
        );
        if (openEdit) return true;

        return false;
    }

    function safeReload(delayMs) {
        if (pendingReload) return;
        pendingReload = true;
        setTimeout(() => {
            if (!isBusy()) {
                location.reload();
            } else {
                // Poll every 5s until user is free, then reload
                const retry = setInterval(() => {
                    if (!isBusy()) {
                        clearInterval(retry);
                        location.reload();
                    }
                }, 5000);
            }
        }, delayMs);
    }

    // ── Poll ──────────────────────────────────────────────────────────────────
    function poll() {
        fetch(`/api/order-updates?panel=${panel}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;

            data.orders.forEach(order => {
                const oid = String(order.id);
                const snap = domSnapshot[oid];

                if (!snap) {
                    // Seed full snapshot immediately so next poll won't re-trigger
                    domSnapshot[oid] = {
                        status: order.status,
                        items: Object.fromEntries(order.items.map(i => [String(i.id), i.status]))
                    };
                    if (panel === 'cashier' && order.status === 'served') {
                        // Inject new order card into cashier page
                        const container = document.querySelector('.space-y-3');
                        if (container && !document.querySelector(`[data-order-id="${order.id}"]`)) {
                            // Remove empty state if present
                            const empty = container.querySelector('.text-center');
                            if (empty) empty.remove();
                            container.appendChild(buildCashierCard(order));
                            toast(orderMsg(order.id, order.table_number, null, 'served'), 'success');
                        }
                    } else if ((panel === 'cook' || panel === 'admin') && order.status === 'pending') {
                        toast(orderMsg(order.id, order.table_number, null, 'pending'), 'warning');
                        safeReload(1500);
                    }
                    return;
                }

                // Order status changed
                if (snap.status !== order.status) {
                    const msg = orderMsg(order.id, order.table_number, snap.status, order.status);
                    if (msg) toast(msg, toastType(order.status));
                    snap.status = order.status;
                    updateOrderCard(order);

                    if (panel === 'cashier' && ['paid', 'cancelled'].includes(order.status)) {
                        // Remove the card from cashier page
                        const card = document.querySelector(`[data-order-id="${order.id}"]`);
                        if (card) card.remove();
                        // Show empty state if no cards left
                        const container = document.querySelector('.space-y-3');
                        if (container && !container.querySelector('[data-order-id]')) {
                            const empty = document.createElement('div');
                            empty.className = 'bg-white rounded-lg shadow p-8 text-center';
                            empty.innerHTML = '<div class="text-4xl mb-2">✓</div><p class="text-gray-600">All payments cleared!</p>';
                            container.appendChild(empty);
                        }
                        return;
                    }

                    if (panel === 'waiter' && ['paid', 'cancelled'].includes(order.status)) {
                        // Remove paid/cancelled order card from waiter page
                        const card = document.querySelector(`[data-order-id="${order.id}"]`);
                        if (card) card.remove();
                        // Show empty state if no cards left
                        const container = document.querySelector('.space-y-3');
                        if (container && !container.querySelector('[data-order-id]')) {
                            const empty = document.createElement('div');
                            empty.className = 'text-center py-12 text-gray-500';
                            empty.innerHTML = '<p class="text-lg">No orders today</p><a href="/waiter/orders/create" class="text-blue-500 hover:underline mt-2 inline-block text-sm">Create your first order</a>';
                            container.appendChild(empty);
                        }
                        return;
                    }

                    // Only reload on paid/cancelled for non-cashier, non-waiter panels
                    if (['paid', 'cancelled'].includes(order.status) && panel !== 'cook' && panel !== 'cashier') {
                        safeReload(2000);
                        return;
                    }
                }

                // Item status changes
                order.items.forEach(item => {
                    const iid = String(item.id);
                    const prevItemStatus = snap.items[iid];
                    if (prevItemStatus === undefined) {
                        // New item added to existing order — seed it immediately
                        snap.items[iid] = item.status;
                        // Only cook/admin need a reload to render the new item row
                        if ((panel === 'cook' || panel === 'admin') && !pendingReload) {
                            toast(`🆕 New item "${item.name}" added to Order #${order.id}!`, 'warning');
                            safeReload(1500);
                        }
                        return;
                    }
                    if (prevItemStatus !== item.status) {
                        const msg = itemMsg(order.id, item.name, item.status);
                        if (msg) toast(msg, toastType(item.status));
                        snap.items[iid] = item.status;
                        updateOrderCard(order);
                    }
                });
            });
        })
        .catch(() => {}); // silently ignore network errors
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        buildSnapshot();
        setInterval(poll, 5000);
    });

    // Inject keyframe CSS once
    const style = document.createElement('style');
    style.textContent = `@keyframes fadeInDown{from{opacity:0;transform:translateX(-50%) translateY(-10px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}`;
    document.head.appendChild(style);
})();
