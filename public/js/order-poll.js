/**
 * order-poll.js — Universal 5-second order polling for all panels.
 *
 * Each panel blade must define window.ORDER_POLL before including this script:
 *   window.ORDER_POLL = { panel: 'waiter' | 'cook' | 'cashier' | 'admin' };
 */

(function () {
    'use strict';

    const domSnapshot = {};
    let pendingReload  = false;
    let currentUserId  = null; // resolved from first API response

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

    // ── Order label (Table T3 or 📦 Parcel) ──────────────────────────────────
    function orderLabel(order) {
        return order.is_parcel ? '📦 Parcel' : `Table ${order.table_number}`;
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
        Object.values(STATUS_LABELS).forEach(s => {
            s.cls.split(' ').forEach(c => c && el.classList.remove(c));
        });
        info.cls.split(' ').forEach(c => c && el.classList.add(c));
    }

    // ── Ownership check ───────────────────────────────────────────────────────
    // waiter & cashier only receive toasts for orders THEY created.
    // cook & admin see all orders — no filter.
    function isMyOrder(order) {
        if (panel === 'cook' || panel === 'admin') return true;
        if (currentUserId === null) return true; // not resolved yet — allow
        return order.created_by_id === currentUserId;
    }

    // ── Panel-specific messages ───────────────────────────────────────────────
    const panel = (window.ORDER_POLL || {}).panel || 'unknown';

    function orderMsg(order, oldStatus, newStatus) {
        const label = orderLabel(order);
        const id    = order.id;
        const msgs = {
            waiter: {
                ready:     `🔔 Order #${id} (${label}) is READY to serve!`,
                cancelled: `❌ Order #${id} (${label}) was cancelled.`,
                preparing: `🍳 Order #${id} (${label}) is now being prepared.`,
                served:    `✅ Order #${id} (${label}) marked as served.`,
                paid:      `💰 Order #${id} (${label}) has been paid.`,
            },
            cook: {
                pending:   `🆕 New order #${id} arrived — ${label}!`,
                cancelled: `❌ Order #${id} (${label}) was cancelled.`,
            },
            cashier: {
                served:    `💳 Order #${id} (${label}) is ready for payment!`,
                ready:     `💳 Order #${id} (${label}) is ready for billing!`,
                cancelled: `❌ Order #${id} (${label}) was cancelled.`,
                paid:      `✅ Order #${id} (${label}) payment done.`,
            },
            cashier_parcels: {
                preparing: `🍳 Parcel #${id} is now being prepared.`,
                ready:     `✅ Parcel #${id} is READY — go to billing!`,
                cancelled: `❌ Parcel #${id} was cancelled.`,
            },
            admin: {
                pending:   `🆕 New order #${id} — ${label}.`,
                ready:     `✅ Order #${id} (${label}) is ready.`,
                served:    `🍽️ Order #${id} (${label}) served.`,
                paid:      `💰 Order #${id} (${label}) paid.`,
                cancelled: `❌ Order #${id} (${label}) cancelled.`,
            },
        };
        const panelMsgs = msgs[panel] || {};
        return panelMsgs[newStatus] || `Order #${id} status → ${newStatus}`;
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

    // ── Cashier: update pending count in header ───────────────────────────────
    function updatePendingCount() {
        const el = document.getElementById('pendingCount');
        if (!el) return;
        el.textContent = document.querySelectorAll('[data-order-id]').length;
    }

    // ── Cashier: build and inject a new parcel order card ────────────────────
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

        // Parcel orders always show parcel badge; table orders show T{number} + category
        const typeBadge = order.is_parcel
            ? `<span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">📦 Parcel</span>`
            : `<span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">T${order.table_number}</span>`;

        const customerNotesHtml = order.customer_notes ? `
        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
            <h4 class="font-semibold mb-1 text-sm text-yellow-800">Customer Request:</h4>
            <p class="text-sm text-gray-700 italic">${order.customer_notes}</p>
        </div>` : '';

        const csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        const totalFmt  = parseFloat(order.total_amount).toFixed(2);

        const div = document.createElement('div');
        div.className = 'bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-red-500';
        div.setAttribute('data-order-id',    order.id);
        div.setAttribute('data-order-status', order.status);
        div.setAttribute('data-is-parcel',    order.is_parcel ? '1' : '0');
        // Animate in
        div.style.cssText = 'opacity:0;transform:translateY(-8px);transition:opacity .4s,transform .4s;';
        div.innerHTML = `
        <div class="p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="font-bold text-lg">Order #${order.id}</h3>
                    <div class="flex items-center gap-2 mt-1">${typeBadge}</div>
                    <p class="text-xs text-gray-400" style="margin-top:3px;">${order.created_at}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800"
                      data-order-status-badge>Ready</span>
            </div>
            <div class="space-y-2 mb-4">${itemsHtml}</div>
            ${customerNotesHtml}
            <div class="pt-3 border-t">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-bold text-xl text-green-600" data-order-total>Total: ₹${totalFmt}</div>
                    <button type="button"
                            onclick="cashierAddItems(${order.id},'${order.is_parcel ? 'Parcel' : 'T' + order.table_number}')"
                            class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                        Add Items
                    </button>
                </div>
                <form action="/cashier/payments/${order.id}/process" method="POST" id="paymentForm${order.id}">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="PATCH">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2">Payment Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="selectPaymentMode(${order.id},'cash')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="${order.id}" data-mode="cash">💵 Cash</button>
                            <button type="button" onclick="selectPaymentMode(${order.id},'upi')"  class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="${order.id}" data-mode="upi">📱 UPI</button>
                            <button type="button" onclick="selectPaymentMode(${order.id},'card')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="${order.id}" data-mode="card">💳 Card</button>
                        </div>
                        <input type="hidden" name="payment_mode" id="paymentMode${order.id}" required>
                    </div>
                    <div id="cashSection${order.id}" class="mb-4" style="display:none">
                        <label class="block text-sm font-semibold mb-2">Cash Received</label>
                        <div class="flex gap-2">
                            <input type="number" step="0.01" min="0" id="cashReceived${order.id}" class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 text-lg" placeholder="Enter amount">
                            <button type="button" onclick="calculateChange(${order.id},${totalFmt})" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">OK</button>
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

        const addItemsBtn = card.querySelector('[data-add-items-btn]');
        if (addItemsBtn) {
            addItemsBtn.style.display = ['paid', 'cancelled'].includes(order.status) ? 'none' : '';
        }

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

            const editForm = document.getElementById(`cedit-${item.id}`) ||
                             document.getElementById(`edit-${item.id}`) ||
                             document.getElementById(`aedit-${item.id}`);
            if (editForm) {
                editForm.classList.add('hidden');
                editForm.style.display = 'none';
            }

            const actions = row.querySelector('[data-item-actions]');
            if (!actions) return;

            if (item.status === 'cancelled') {
                actions.innerHTML = '<span style="font-size:12px;color:#ef4444;padding:0 4px;">Cancelled</span>';
                const nameEl = row.querySelector('[data-item-name]');
                if (nameEl) nameEl.style.textDecoration = 'line-through';
            } else if (item.status === 'prepared') {
                actions.innerHTML = '<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:6px;font-size:13px;font-weight:600;">✓ Done</span>';
                const nameEl = row.querySelector('[data-item-name]');
                if (nameEl) nameEl.style.textDecoration = 'line-through';
            }
        });
    }

    // ── Busy detection ────────────────────────────────────────────────────────
    function isBusy() {
        const modals = ['addItemsModal', 'cashierAddItemsModal'];
        if (modals.some(id => { const m = document.getElementById(id); return m && !m.classList.contains('hidden'); })) return true;

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
                const retry = setInterval(() => {
                    if (!isBusy()) {
                        clearInterval(retry);
                        location.reload();
                    }
                }, 5000);
            }
        }, delayMs);
    }

    // ── Cashier: is this order billable? ─────────────────────────────────────
    function isCashierBillable(order) {
        if (order.is_parcel) return order.status === 'ready';
        return ['served', 'checkout'].includes(order.status);
    }

    // ── Poll ──────────────────────────────────────────────────────────────────
    function poll() {
        fetch(`/api/order-updates?panel=${panel}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;

            // Resolve current user ID once from the first response
            if (currentUserId === null && data.current_user_id != null) {
                currentUserId = data.current_user_id;
            }

            data.orders.forEach(order => {
                const oid  = String(order.id);
                const snap = domSnapshot[oid];

                if (!snap) {
                    // New order not yet in DOM
                    domSnapshot[oid] = {
                        status: order.status,
                        items: Object.fromEntries(order.items.map(i => [String(i.id), i.status]))
                    };

                    if (panel === 'cashier' && isCashierBillable(order)) {
                        const container = document.querySelector('.space-y-3');
                        if (container && !document.querySelector(`[data-order-id="${order.id}"]`)) {
                            const empty = container.querySelector('.text-center.py-12, .bg-white.rounded-lg.shadow.p-8');
                            if (empty) empty.remove();
                            const card = buildCashierCard(order);
                            container.appendChild(card);
                            requestAnimationFrame(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            });
                            updatePendingCount();
                            // Only toast if this cashier created the order
                            if (isMyOrder(order)) toast(orderMsg(order, null, order.status), 'success');
                        }
                    } else if ((panel === 'cook' || panel === 'admin') && order.status === 'pending') {
                        toast(orderMsg(order, null, 'pending'), 'warning');
                        safeReload(1500);
                    }                    return;
                }

                // Order status changed
                if (snap.status !== order.status) {
                    const msg = isMyOrder(order) ? orderMsg(order, snap.status, order.status) : null;
                    if (msg) toast(msg, toastType(order.status));
                    snap.status = order.status;
                    updateOrderCard(order);

                    // cashier_parcels: update border colour + inject billing button when ready
                    if (panel === 'cashier_parcels') {
                        const card = document.querySelector(`[data-order-id="${order.id}"]`);
                        if (card) {
                            card.classList.remove('border-yellow-500', 'border-orange-500', 'border-green-500');
                            if (order.status === 'ready')     card.classList.add('border-green-500');
                            else if (order.status === 'preparing') card.classList.add('border-orange-500');
                            else card.classList.add('border-yellow-500');

                            if (order.status === 'ready' && !card.querySelector('.go-billing-btn')) {
                                const btn = document.createElement('div');
                                btn.className = 'mt-3 go-billing-btn';
                                btn.innerHTML = `<a href="/cashier/payments" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold text-sm">💳 Go to Billing</a>`;
                                card.querySelector('.p-4').appendChild(btn);
                            }

                            if (['paid', 'cancelled'].includes(order.status)) {
                                card.style.transition = 'opacity .3s, transform .3s';
                                card.style.opacity = '0';
                                card.style.transform = 'translateY(-6px)';
                                setTimeout(() => card.remove(), 350);
                            }
                        }
                        return;
                    }

                    if (panel === 'cashier' && ['paid', 'cancelled'].includes(order.status)) {
                        const card = document.querySelector(`[data-order-id="${order.id}"]`);
                        if (card) {
                            card.style.transition = 'opacity .3s, transform .3s';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(-6px)';
                            setTimeout(() => {
                                card.remove();
                                updatePendingCount();
                                const container = document.querySelector('.space-y-3');
                                if (container && !container.querySelector('[data-order-id]')) {
                                    const empty = document.createElement('div');
                                    empty.className = 'bg-white rounded-lg shadow p-8 text-center';
                                    empty.innerHTML = '<div class="text-4xl mb-2">✓</div><p class="text-gray-600">All payments cleared!</p>';
                                    container.appendChild(empty);
                                }
                            }, 350);
                        }
                        return;
                    }

                    if (panel === 'waiter' && ['paid', 'cancelled'].includes(order.status)) {
                        const card = document.querySelector(`[data-order-id="${order.id}"]`);
                        if (card) card.remove();
                        const container = document.querySelector('.space-y-3');
                        if (container && !container.querySelector('[data-order-id]')) {
                            const empty = document.createElement('div');
                            empty.className = 'text-center py-12 text-gray-500';
                            empty.innerHTML = '<p class="text-lg">No orders today</p><a href="/waiter/orders/create" class="text-blue-500 hover:underline mt-2 inline-block text-sm">Create your first order</a>';
                            container.appendChild(empty);
                        }
                        return;
                    }

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
                        snap.items[iid] = item.status;
                        if ((panel === 'cook' || panel === 'admin') && !pendingReload) {
                            toast(`🆕 New item "${item.name}" added to Order #${order.id}!`, 'warning');
                            safeReload(1500);
                        }
                        return;
                    }
                    if (prevItemStatus !== item.status) {
                        // Item toasts: waiter/cashier only for their own orders
                        if (isMyOrder(order)) {
                            const msg = itemMsg(order.id, item.name, item.status);
                            if (msg) toast(msg, toastType(item.status));
                        }
                        snap.items[iid] = item.status;
                        updateOrderCard(order);
                    }
                });
            });
        })
        .catch(() => {});
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        buildSnapshot();
        setInterval(poll, 5000);
    });

    const style = document.createElement('style');
    style.textContent = `@keyframes fadeInDown{from{opacity:0;transform:translateX(-50%) translateY(-10px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}`;
    document.head.appendChild(style);
})();
