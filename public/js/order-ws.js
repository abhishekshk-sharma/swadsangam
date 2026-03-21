/**
 * order-ws.js — Real-time order updates via Laravel Reverb WebSocket.
 *
 * Each panel blade must define window.ORDER_WS before including this script:
 *   window.ORDER_WS = { panel: 'waiter'|'cook'|'cashier'|'admin', tenantId: N };
 */
(function () {
    'use strict';

    const cfg       = window.ORDER_WS || {};
    const panel     = cfg.panel    || 'unknown';
    const tenantId  = cfg.tenantId || 0;

    // ── Toast ─────────────────────────────────────────────────────────────────
    function toast(msg, type) {
        const colors = { info:'#3b82f6', success:'#16a34a', warning:'#d97706', danger:'#dc2626' };
        const el = document.createElement('div');
        el.style.cssText = `
            position:fixed;top:16px;left:50%;transform:translateX(-50%);
            background:${colors[type]||colors.info};color:#fff;
            padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;
            box-shadow:0 4px 12px rgba(0,0,0,.25);z-index:9999;
            animation:wsToastIn .3s ease;white-space:nowrap;pointer-events:none;
        `;
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function orderLabel(order) {
        return `Table ${order.table_number}`;
    }

    function toastType(status) {
        if (['ready','served','paid','prepared'].includes(status)) return 'success';
        if (status === 'cancelled') return 'danger';
        if (status === 'pending')   return 'warning';
        return 'info';
    }

    function orderMsg(order, newStatus) {
        const label = orderLabel(order);
        const id    = order.id;
        const msgs = {
            waiter:  { ready:`🔔 Order #${id} (${label}) is READY!`, cancelled:`❌ Order #${id} cancelled.`, preparing:`🍳 Order #${id} is being prepared.`, served:`✅ Order #${id} served.`, paid:`💰 Order #${id} paid.` },
            cook:    { pending:`🆕 New order #${id} — ${label}!`, cancelled:`❌ Order #${id} cancelled.` },
            cashier: { served:`💳 Order #${id} (${label}) ready for payment!`, checkout:`💳 Order #${id} (${label}) ready for payment!`, cancelled:`❌ Order #${id} cancelled.`, paid:`✅ Order #${id} payment done.` },
            admin:   { pending:`🆕 New order #${id} — ${label}.`, ready:`✅ Order #${id} ready.`, served:`🍽️ Order #${id} served.`, paid:`💰 Order #${id} paid.`, cancelled:`❌ Order #${id} cancelled.` },
        };
        return (msgs[panel]||{})[newStatus] || `Order #${id} → ${newStatus}`;
    }

    // ── DOM snapshot ──────────────────────────────────────────────────────────
    const snap = {};
    function buildSnapshot() {
        document.querySelectorAll('[data-order-id]').forEach(card => {
            const oid = card.dataset.orderId;
            snap[oid] = { status: card.dataset.orderStatus || '', items: {} };
            card.querySelectorAll('[data-item-id]').forEach(row => {
                snap[oid].items[row.dataset.itemId] = row.dataset.itemStatus || '';
            });
        });
    }

    // ── Busy guard ────────────────────────────────────────────────────────────
    function isBusy() {
        const modals = ['addItemsModal','cashierAddItemsModal'];
        if (modals.some(id => { const m = document.getElementById(id); return m && !m.classList.contains('hidden'); })) return true;
        return !!document.querySelector('[id^="cedit-"]:not(.hidden),[id^="edit-"]:not(.hidden),[id^="aedit-"][style*="display: block"],[id^="aedit-"][style*="display:block"]');
    }

    let pendingReload = false;
    function safeReload(ms) {
        if (pendingReload) return;
        pendingReload = true;
        setTimeout(() => {
            if (!isBusy()) { location.reload(); return; }
            const t = setInterval(() => { if (!isBusy()) { clearInterval(t); location.reload(); } }, 5000);
        }, ms);
    }

    // ── Status badge ──────────────────────────────────────────────────────────
    const STATUS_CLS = {
        pending:   'bg-yellow-100 text-yellow-800',
        preparing: 'bg-blue-100 text-blue-800',
        ready:     'bg-green-100 text-green-800',
        served:    'bg-purple-100 text-purple-800',
        paid:      'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-800',
    };
    function applyBadge(el, status) {
        Object.values(STATUS_CLS).forEach(c => c.split(' ').forEach(x => x && el.classList.remove(x)));
        (STATUS_CLS[status]||'').split(' ').forEach(x => x && el.classList.add(x));
        el.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }

    // ── Waiter action buttons live update ────────────────────────────────────
    function updateWaiterActions(card, order) {
        const status   = order.status;
        const terminal = ['paid', 'cancelled', 'checkout'];
        const actionsEl = card.querySelector('.waiter-order-actions');

        if (actionsEl) {
            const addBtn = actionsEl.querySelector('[data-add-items-btn]');
            if (addBtn) addBtn.style.display = terminal.includes(status) ? 'none' : '';

            let serveBtn = actionsEl.querySelector('[data-serve-btn]');
            if (status === 'ready') {
                if (!serveBtn) {
                    serveBtn = document.createElement('button');
                    serveBtn.type = 'button';
                    serveBtn.setAttribute('data-serve-btn', '');
                    serveBtn.title = 'Mark as Served';
                    serveBtn.className = 'waiter-action-btn waiter-btn-serve';
                    serveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                    serveBtn.onclick = function () { markServed(order.id); };
                    actionsEl.appendChild(serveBtn);
                }
            } else {
                if (serveBtn) serveBtn.remove();
            }
        }

        // Checkout section
        let checkoutEl = card.querySelector('.checkout-section');
        if (status === 'served') {
            if (!checkoutEl) {
                checkoutEl = document.createElement('div');
                checkoutEl.className = 'checkout-section';
                checkoutEl.innerHTML = `<button type="button" onclick="checkoutOrder(${order.id})" class="checkout-btn"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Checkout Table</button><p class="checkout-hint">Customer is done. Free the table now — cashier will collect payment separately.</p>`;
                const borderDiv = card.querySelector('.border-t');
                if (borderDiv) borderDiv.appendChild(checkoutEl);
            }
        } else {
            if (checkoutEl) checkoutEl.remove();
        }
    }

    // ── Update card DOM ───────────────────────────────────────────────────────
    function updateCard(order) {
        const card = document.querySelector(`[data-order-id="${order.id}"]`);
        if (!card) return;
        card.dataset.orderStatus = order.status;
        const badge = card.querySelector('[data-order-status-badge]');
        if (badge) applyBadge(badge, order.status);
        const totalEl = card.querySelector('[data-order-total]');
        if (totalEl) totalEl.textContent = '₹' + order.total_amount.toFixed(2);
        if (panel === 'waiter') updateWaiterActions(card, order);
        order.items.forEach(item => {
            const row = card.querySelector(`[data-item-id="${item.id}"]`);
            if (!row || row.dataset.itemStatus === item.status) return;
            row.dataset.itemStatus = item.status;
            const actions = row.querySelector('[data-item-actions]');
            if (!actions) return;
            if (item.status === 'cancelled') {
                actions.innerHTML = '<span style="font-size:12px;color:#ef4444;padding:0 4px;">Cancelled</span>';
                const n = row.querySelector('[data-item-name]'); if (n) n.style.textDecoration = 'line-through';
            } else if (item.status === 'prepared') {
                actions.innerHTML = '<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:6px;font-size:13px;font-weight:600;">✓ Done</span>';
                const n = row.querySelector('[data-item-name]'); if (n) n.style.textDecoration = 'line-through';
            }
        });
    }

    // ── Handle incoming event ─────────────────────────────────────────────────
    function handleEvent(data) {
        const order     = data.order;
        const oid       = String(order.id);
        const newStatus = order.status;
        const oldStatus = order.old_status;
        console.log('[order-ws] event received', { panel, oid, newStatus, oldStatus, snap: JSON.parse(JSON.stringify(snap)) });

        if (!snap[oid]) {
            // Brand new order not yet in DOM
            snap[oid] = { status: newStatus, items: {} };
            order.items.forEach(i => { snap[oid].items[String(i.id)] = i.status; });

            if ((panel === 'cook' || panel === 'admin') && newStatus === 'pending') {
                toast(orderMsg(order, 'pending'), 'warning');
                safeReload(1500);
            }
            if (panel === 'cashier' && ['served','checkout'].includes(newStatus)) {
                toast(orderMsg(order, newStatus), 'success');
                safeReload(1500);
            }
            return;
        }

        const prevStatus = snap[oid].status;
        if (prevStatus !== newStatus) {
            snap[oid].status = newStatus;
            toast(orderMsg(order, newStatus), toastType(newStatus));
            updateCard(order);

            // Remove card when paid/cancelled on cashier or waiter
            if (['paid','cancelled'].includes(newStatus) && (panel === 'cashier' || panel === 'waiter')) {
                const card = document.querySelector(`[data-order-id="${order.id}"]`);
                if (card) {
                    card.style.transition = 'opacity .3s,transform .3s';
                    card.style.opacity = '0'; card.style.transform = 'translateY(-6px)';
                    setTimeout(() => card.remove(), 350);
                }
            }

            // Cook/admin: reload for new items or status changes needing full re-render
            if ((panel === 'cook' || panel === 'admin') && ['pending','preparing','ready'].includes(newStatus)) {
                safeReload(1500);
            }
        }

        // Item changes
        order.items.forEach(item => {
            const iid = String(item.id);
            const prev = snap[oid].items[iid];
            if (prev === undefined) {
                snap[oid].items[iid] = item.status;
                if (panel === 'cook' || panel === 'admin') {
                    toast(`🆕 New item "${item.name}" added to Order #${order.id}!`, 'warning');
                    safeReload(1500);
                }
                return;
            }
            if (prev !== item.status) {
                snap[oid].items[iid] = item.status;
                if (item.status === 'prepared')  toast(`✓ "${item.name}" in Order #${order.id} prepared.`, 'success');
                if (item.status === 'cancelled') toast(`❌ "${item.name}" in Order #${order.id} cancelled.`, 'danger');
                updateCard(order);
            }
        });
    }

    // ── Bootstrap Echo ────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        buildSnapshot();

        if (!tenantId) { console.warn('[order-ws] tenantId not set'); return; }
        if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
            console.warn('[order-ws] Pusher/Echo not loaded'); return;
        }

        window.Echo = new Echo({
            broadcaster:  'reverb',
            key:          window.ORDER_WS.reverbKey,
            wsHost:       window.ORDER_WS.reverbHost,
            wsPort:       window.ORDER_WS.reverbPort,
            wssPort:      window.ORDER_WS.reverbPort,
            forceTLS:     window.ORDER_WS.reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo.channel(`orders.${tenantId}`)
            .listen('.OrderStatusUpdated', handleEvent);

        console.log('[order-ws] subscribed to orders.' + tenantId + ' as panel=' + panel);
    });

    // Toast animation
    const s = document.createElement('style');
    s.textContent = '@keyframes wsToastIn{from{opacity:0;transform:translateX(-50%) translateY(-10px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}';
    document.head.appendChild(s);
})();
