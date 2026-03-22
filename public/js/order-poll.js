/**
 * order-poll.js — Universal 7-second HTTP polling for all panels.
 *
 * Each layout must set before including this script:
 *   window.ORDER_POLL = { panel: 'waiter'|'cook'|'cashier'|'cashier_parcels'|'admin'|'manager' };
 */
(function () {
    'use strict';

    var panel = (window.ORDER_POLL || {}).panel || 'unknown';
    // manager uses same logic as admin
    if (panel === 'manager') panel = 'admin';

    // ── Snapshot of what is currently rendered ────────────────────────────────
    // Keyed by order id string → { status, items: { itemId: status } }
    var snap = {};
    // Track order IDs that have already triggered a reload request
    var reloadTriggered = {};
    // Timestamp when the page finished loading — used to ignore pre-existing orders
    var pageLoadTime = Date.now();

    function buildSnapshot() {
        document.querySelectorAll('[data-order-id]').forEach(function (card) {
            var oid = card.dataset.orderId;
            snap[oid] = { status: card.dataset.orderStatus || '', items: {} };
            card.querySelectorAll('[data-item-id]').forEach(function (row) {
                snap[oid].items[row.dataset.itemId] = row.dataset.itemStatus || '';
            });
        });
    }

    // ── Toast ─────────────────────────────────────────────────────────────────
    function toast(msg, type) {
        var colors = { info: '#3b82f6', success: '#16a34a', warning: '#d97706', danger: '#dc2626' };
        var el = document.createElement('div');
        el.style.cssText = 'position:fixed;top:16px;left:50%;transform:translateX(-50%);' +
            'background:' + (colors[type] || colors.info) + ';color:#fff;' +
            'padding:10px 20px;border-radius:8px;font-size:14px;font-weight:600;' +
            'box-shadow:0 4px 12px rgba(0,0,0,.25);z-index:9999;' +
            'animation:pollToastIn .3s ease;white-space:nowrap;pointer-events:none;';
        el.textContent = msg;
        document.body.appendChild(el);
        setTimeout(function () { el.remove(); }, 4000);
    }

    // ── Busy guard ────────────────────────────────────────────────────────────
    function isBusy() {
        var modals = ['addItemsModal', 'cashierAddItemsModal'];
        for (var i = 0; i < modals.length; i++) {
            var m = document.getElementById(modals[i]);
            if (m && !m.classList.contains('hidden')) return true;
        }
        if (document.querySelector('[id^="cedit-"]:not(.hidden),[id^="edit-"]:not(.hidden)')) return true;
        return false;
    }

    var pendingReload = false;
    function safeReload(ms) {
        if (pendingReload) return;
        pendingReload = true;
        setTimeout(function () {
            if (!isBusy()) { location.reload(); return; }
            var t = setInterval(function () {
                if (!isBusy()) { clearInterval(t); location.reload(); }
            }, 5000);
        }, ms || 1500);
    }

    // ── Status badge ──────────────────────────────────────────────────────────
    var STATUS_CLS = {
        pending:   'bg-yellow-100 text-yellow-800',
        preparing: 'bg-blue-100 text-blue-800',
        ready:     'bg-green-100 text-green-800',
        served:    'bg-purple-100 text-purple-800',
        paid:      'bg-gray-100 text-gray-700',
        cancelled: 'bg-red-100 text-red-800',
        checkout:  'bg-indigo-100 text-indigo-800',
    };
    function applyBadge(el, status) {
        Object.values(STATUS_CLS).forEach(function (c) {
            c.split(' ').forEach(function (x) { if (x) el.classList.remove(x); });
        });
        (STATUS_CLS[status] || '').split(' ').forEach(function (x) { if (x) el.classList.add(x); });
        el.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }

    // ── Labels & messages ─────────────────────────────────────────────────────
    function orderLabel(order) {
        return order.is_parcel ? '📦 Parcel' : 'Table ' + order.table_number;
    }
    function toastType(status) {
        if (['ready', 'served', 'paid', 'prepared'].includes(status)) return 'success';
        if (status === 'cancelled') return 'danger';
        if (['pending', 'warning'].includes(status)) return 'warning';
        return 'info';
    }
    function orderMsg(order, newStatus) {
        var label = orderLabel(order), id = order.id;
        var msgs = {
            waiter: {
                pending:   '🆕 New order #' + id + ' — ' + label + ' created.',
                preparing: '🍳 Order #' + id + ' (' + label + ') is being prepared.',
                ready:     '🔔 Order #' + id + ' (' + label + ') is READY to serve!',
                served:    '✅ Order #' + id + ' (' + label + ') marked as served.',
                checkout:  '🏁 Order #' + id + ' (' + label + ') checked out.',
                paid:      '💰 Order #' + id + ' (' + label + ') has been paid.',
                cancelled: '❌ Order #' + id + ' (' + label + ') was cancelled.',
            },
            cook: {
                pending:   '🆕 New order #' + id + ' — ' + label + '!',
                preparing: '🍳 Order #' + id + ' (' + label + ') is now preparing.',
                ready:     '✅ Order #' + id + ' (' + label + ') marked ready.',
                served:    '🍽️ Order #' + id + ' (' + label + ') has been served.',
                cancelled: '❌ Order #' + id + ' (' + label + ') was cancelled.',
            },
            cashier: {
                preparing: '🍳 Order #' + id + ' (' + label + ') is being prepared.',
                served:    '💳 Order #' + id + ' (' + label + ') ready for payment!',
                checkout:  '💳 Order #' + id + ' (' + label + ') ready for payment!',
                cancelled: '❌ Order #' + id + ' (' + label + ') was cancelled.',
                paid:      '✅ Order #' + id + ' payment done.',
            },
            cashier_parcels: {
                preparing: '🍳 Parcel #' + id + ' is now being prepared.',
                ready:     '✅ Parcel #' + id + ' is READY — go to billing!',
                paid:      '💰 Parcel #' + id + ' payment done.',
                cancelled: '❌ Parcel #' + id + ' was cancelled.',
            },
            admin: {
                pending:   '🆕 New order #' + id + ' — ' + label + '.',
                preparing: '🍳 Order #' + id + ' (' + label + ') is being prepared.',
                ready:     '✅ Order #' + id + ' (' + label + ') is ready.',
                served:    '🍽️ Order #' + id + ' (' + label + ') served.',
                checkout:  '🏁 Order #' + id + ' (' + label + ') checked out.',
                paid:      '💰 Order #' + id + ' (' + label + ') paid.',
                cancelled: '❌ Order #' + id + ' (' + label + ') cancelled.',
            },
        };
        return ((msgs[panel] || {})[newStatus]) || ('Order #' + id + ' → ' + newStatus);
    }
    function itemAddedMsg(item, orderId) {
        var msgs = {
            cook:           '🆕 New item "' + item.name + '" (×' + item.quantity + ') added to Order #' + orderId + '!',
            admin:          '🆕 New item "' + item.name + '" (×' + item.quantity + ') added to Order #' + orderId + '.',
            waiter:         '➕ "' + item.name + '" (×' + item.quantity + ') added to Order #' + orderId + '.',
            cashier:        '➕ "' + item.name + '" (×' + item.quantity + ') added to Order #' + orderId + '.',
            cashier_parcels:'➕ "' + item.name + '" (×' + item.quantity + ') added to Parcel #' + orderId + '.',
        };
        return msgs[panel] || ('➕ "' + item.name + '" added to Order #' + orderId);
    }

    // ── Update existing card DOM ──────────────────────────────────────────────
    function updateCard(order) {
        var card = document.querySelector('[data-order-id="' + order.id + '"]');
        if (!card) return;

        card.dataset.orderStatus = order.status;
        var badge = card.querySelector('[data-order-status-badge]');
        if (badge) applyBadge(badge, order.status);
        var totalEl = card.querySelector('[data-order-total]');
        if (totalEl) {
            var fmt = '\u20b9' + parseFloat(order.total_amount).toFixed(2);
            totalEl.textContent = totalEl.textContent.indexOf('Total:') !== -1 ? 'Total: ' + fmt : fmt;
        }

        if (panel === 'waiter') {
            var actionsEl = card.querySelector('.waiter-order-actions');
            if (actionsEl) {
                var addBtn = actionsEl.querySelector('[data-add-items-btn]');
                if (addBtn) addBtn.style.display = ['paid', 'cancelled', 'checkout'].includes(order.status) ? 'none' : '';
                var serveBtn = actionsEl.querySelector('[data-serve-btn]');
                if (order.status === 'ready') {
                    if (!serveBtn) {
                        serveBtn = document.createElement('button');
                        serveBtn.type = 'button';
                        serveBtn.setAttribute('data-serve-btn', '');
                        serveBtn.title = 'Mark as Served';
                        serveBtn.className = 'waiter-action-btn waiter-btn-serve';
                        serveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                        serveBtn.onclick = function () { if (typeof markServed === 'function') markServed(order.id); };
                        actionsEl.appendChild(serveBtn);
                    }
                } else if (serveBtn) {
                    serveBtn.remove();
                }
            }
            var checkoutEl = card.querySelector('.checkout-section');
            if (order.status === 'served') {
                if (!checkoutEl) {
                    checkoutEl = document.createElement('div');
                    checkoutEl.className = 'checkout-section';
                    checkoutEl.innerHTML = '<button type="button" onclick="checkoutOrder(' + order.id + ')" class="checkout-btn">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;display:inline-block;vertical-align:middle;margin-right:6px;">' +
                        '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>' +
                        'Checkout Table</button><p class="checkout-hint">Customer is done. Free the table now — cashier will collect payment separately.</p>';
                    var borderDiv = card.querySelector('.border-t');
                    if (borderDiv) borderDiv.appendChild(checkoutEl);
                }
            } else if (checkoutEl) {
                checkoutEl.remove();
            }
        }

        if (panel === 'cashier') {
            // Keep calculateChange OK button in sync with updated total
            var cashOkBtn = card.querySelector('[onclick^="calculateChange"]');
            if (cashOkBtn) cashOkBtn.setAttribute('onclick', 'calculateChange(' + order.id + ',' + order.total_amount + ')');
        }

        if (panel === 'cashier_parcels') {
            card.classList.remove('border-yellow-500', 'border-orange-500', 'border-green-500');
            if (order.status === 'ready')          card.classList.add('border-green-500');
            else if (order.status === 'preparing') card.classList.add('border-orange-500');
            else                                   card.classList.add('border-yellow-500');
            if (order.status === 'ready' && !card.querySelector('.go-billing-btn')) {
                var btn = document.createElement('div');
                btn.className = 'mt-3 go-billing-btn';
                btn.innerHTML = '<a href="/cashier/payments" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold text-sm">💳 Go to Billing</a>';
                var p4 = card.querySelector('.p-4');
                if (p4) p4.appendChild(btn);
            }
        }

        order.items.forEach(function (item) {
            var row = card.querySelector('[data-item-id="' + item.id + '"]');
            if (!row || row.dataset.itemStatus === item.status) return;
            row.dataset.itemStatus = item.status;
            var editForm = document.getElementById('cedit-' + item.id) || document.getElementById('edit-' + item.id);
            if (editForm) editForm.classList.add('hidden');
            var actions = row.querySelector('[data-item-actions]');
            if (!actions) return;
            if (item.status === 'cancelled') {
                actions.innerHTML = '<span style="font-size:12px;color:#ef4444;padding:0 4px;">Cancelled</span>';
                var n = row.querySelector('[data-item-name]'); if (n) n.style.textDecoration = 'line-through';
            } else if (item.status === 'prepared') {
                actions.innerHTML = '<span style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:6px;font-size:13px;font-weight:600;">✓ Done</span>';
                var n2 = row.querySelector('[data-item-name]'); if (n2) n2.style.textDecoration = 'line-through';
            }
        });
    }

    // ── Cashier payments: build and inject a new order card ──────────────────
    function buildCashierCard(order) {
        var itemsHtml = order.items.map(function (item) {
            var cancelled = item.status === 'cancelled';
            var priceHtml = cancelled
                ? '<div class="text-gray-400 line-through text-sm">₹' + (item.price * item.quantity).toFixed(2) + '</div>'
                : '<div class="font-bold">₹' + (item.price * item.quantity).toFixed(2) + '</div>';
            var notesHtml = item.notes ? '<div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ ' + item.notes + '</div>' : '';
            return '<div class="py-2 border-b" data-item-id="' + item.id + '" data-item-status="' + item.status + '">' +
                '<div class="flex justify-between items-center"><div class="flex-1"><div class="flex items-center gap-2">' +
                '<span class="font-semibold ' + (cancelled ? 'line-through text-gray-400' : '') + '">' + item.name + '</span>' +
                (cancelled ? '<span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded">Cancelled</span>' : '') +
                '</div><div class="text-sm text-gray-' + (cancelled ? '400' : '600') + '">Qty: ' + item.quantity + '</div>' +
                notesHtml + '</div><div class="text-right">' + priceHtml + '</div></div></div>';
        }).join('');
        var typeBadge = order.is_parcel
            ? '<span style="background:#ea580c;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">📦 Parcel</span>'
            : '<span style="background:#1e3a5f;color:#fff;font-size:13px;font-weight:800;padding:2px 10px;border-radius:6px;letter-spacing:0.03em;">T' + order.table_number + '</span>';
        var notesHtml = order.customer_notes
            ? '<div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded"><h4 class="font-semibold mb-1 text-sm text-yellow-800">Customer Request:</h4><p class="text-sm text-gray-700 italic">' + order.customer_notes + '</p></div>'
            : '';
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var total = parseFloat(order.total_amount).toFixed(2);
        var div = document.createElement('div');
        div.className = 'bg-white rounded-lg shadow-md overflow-hidden border-l-4 border-red-500';
        div.setAttribute('data-order-id', order.id);
        div.setAttribute('data-order-status', order.status);
        div.setAttribute('data-is-parcel', order.is_parcel ? '1' : '0');
        div.style.cssText = 'opacity:0;transform:translateY(-8px);transition:opacity .4s,transform .4s;';
        div.innerHTML =
            '<div class="p-4"><div class="flex justify-between items-start mb-3"><div>' +
            '<h3 class="font-bold text-lg">Order #' + order.id + '</h3>' +
            '<div class="flex items-center gap-2 mt-1">' + typeBadge + '</div>' +
            '<p class="text-xs text-gray-400" style="margin-top:3px;">' + order.created_at + '</p>' +
            '</div><span class="px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800" data-order-status-badge>' +
            order.status.charAt(0).toUpperCase() + order.status.slice(1) + '</span></div>' +
            '<div class="space-y-2 mb-4">' + itemsHtml + '</div>' + notesHtml +
            '<div class="pt-3 border-t"><div class="font-bold text-xl text-green-600 mb-4" data-order-total>Total: ₹' + total + '</div>' +
            '<form action="/cashier/payments/' + order.id + '/process" method="POST" id="paymentForm' + order.id + '">' +
            '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="PATCH">' +
            '<div class="mb-4"><label class="block text-sm font-semibold mb-2">Payment Method</label><div class="grid grid-cols-3 gap-2">' +
            '<button type="button" onclick="selectPaymentMode(' + order.id + ',\'cash\')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="' + order.id + '" data-mode="cash">💵 Cash</button>' +
            '<button type="button" onclick="selectPaymentMode(' + order.id + ',\'upi\')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="' + order.id + '" data-mode="upi">📱 UPI</button>' +
            '<button type="button" onclick="selectPaymentMode(' + order.id + ',\'card\')" class="payment-mode-btn border-2 border-gray-300 rounded-lg py-3 font-semibold hover:border-blue-500" data-order="' + order.id + '" data-mode="card">💳 Card</button>' +
            '</div><input type="hidden" name="payment_mode" id="paymentMode' + order.id + '" required></div>' +
            '<div id="cashSection' + order.id + '" class="mb-4" style="display:none"><label class="block text-sm font-semibold mb-2">Cash Received</label>' +
            '<div class="flex gap-2"><input type="number" step="0.01" min="0" id="cashReceived' + order.id + '" class="flex-1 border-2 border-gray-300 rounded-lg px-4 py-2 text-lg" placeholder="Enter amount">' +
            '<button type="button" onclick="calculateChange(' + order.id + ',' + total + ')" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">OK</button></div></div>' +
            '<div id="changeSection' + order.id + '" class="mb-4 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4" style="display:none">' +
            '<div class="text-center"><p class="text-sm text-gray-600 mb-1">Change to Return</p><p class="text-3xl font-bold text-green-600" id="changeAmount' + order.id + '">₹0.00</p></div></div>' +
            '<button type="submit" id="submitBtn' + order.id + '" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold text-lg" style="display:none" disabled>Complete Payment</button>' +
            '</form></div></div>';
        return div;
    }

    function updatePendingCount() {
        var el = document.getElementById('pendingCount');
        if (el) el.textContent = document.querySelectorAll('[data-order-id]').length;
    }

    function removeCard(orderId, afterRemove) {
        var card = document.querySelector('[data-order-id="' + orderId + '"]');
        if (!card) return;
        card.style.transition = 'opacity .3s,transform .3s';
        card.style.opacity = '0';
        card.style.transform = 'translateY(-6px)';
        setTimeout(function () {
            card.remove();
            if (typeof afterRemove === 'function') afterRemove();
        }, 350);
    }

    // ── Bind AJAX payment submit on a cashier card ──────────────────────────
    function bindCashierForm(card) {
        var form = card.querySelector('[id^="paymentForm"]');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var orderId  = form.id.replace('paymentForm', '');
            var submitBtn = document.getElementById('submitBtn' + orderId);
            submitBtn.disabled    = true;
            submitBtn.textContent = 'Processing…';
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    if (typeof BILL_URLS !== 'undefined') BILL_URLS[res.order_id] = res.bill_url;
                    var c = document.querySelector('[data-order-id="' + orderId + '"]');
                    if (c) {
                        c.style.transition = 'opacity .35s,transform .35s';
                        c.style.opacity    = '0';
                        c.style.transform  = 'scale(0.97)';
                        setTimeout(function () {
                            c.remove();
                            updatePendingCount();
                            var cont = document.querySelector('.space-y-3');
                            if (cont && !cont.querySelector('[data-order-id]')) {
                                var empty = document.createElement('div');
                                empty.className = 'bg-white rounded-lg shadow p-8 text-center';
                                empty.innerHTML = '<div class="text-4xl mb-2">✓</div><p class="text-gray-600">All payments cleared!</p>';
                                cont.appendChild(empty);
                            }
                        }, 350);
                    }
                    if (typeof showQrModal === 'function') showQrModal(res.order_id, res.bill_url);
                } else {
                    submitBtn.disabled    = false;
                    submitBtn.textContent = 'Complete Payment';
                }
            })
            .catch(function () {
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Complete Payment';
            });
        });
    }

    // ── Cook item action (mark prepared / cancel) via fetch ────────────────────
    function cookItemAction(url, extraData, row, orderId) {
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var body = new URLSearchParams(Object.assign({ _method: 'PATCH', _token: csrf }, extraData));
        fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body })
        .then(function(r) {
            if (r.ok || r.redirected) {
                // Let next poll update the row status; reload to sync fully
                safeReload(500);
            }
        })
        .catch(function() { safeReload(500); });
    }

    // ── Poll ──────────────────────────────────────────────────────────────────
    function poll() {
        fetch('/api/order-updates?panel=' + panel, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data) return;

            data.orders.forEach(function (order) {
                var oid = String(order.id);
                var s   = snap[oid];

                // ── Order not yet in snapshot ─────────────────────────────────
                if (!s) {
                    // Register in snap immediately to prevent repeat triggers
                    snap[oid] = { status: order.status, items: {} };
                    order.items.forEach(function (i) { snap[oid].items[String(i.id)] = i.status; });

                    if (panel === 'cashier') {
                        // Inject new card into cashier payments page
                        var container = document.querySelector('.space-y-3');
                        if (container && !document.querySelector('[data-order-id="' + order.id + '"]')) {
                            var empty = container.querySelector('.bg-white.rounded-lg.shadow.p-8');
                            if (empty) empty.remove();
                            var card = buildCashierCard(order);
                            container.appendChild(card);
                            requestAnimationFrame(function () { card.style.opacity = '1'; card.style.transform = 'translateY(0)'; });
                            bindCashierForm(card);
                            updatePendingCount();
                            toast(orderMsg(order, order.status), 'success');
                        }
                    } else if (panel === 'cook' || panel === 'admin') {
                        // Only reload once per new order ID
                        if (!reloadTriggered[oid] && order.status === 'pending') {
                            reloadTriggered[oid] = true;
                            toast(orderMsg(order, 'pending'), 'warning');
                            safeReload(1500);
                        }
                    }
                    // waiter: only reload for orders that arrived AFTER page load
                    // (i.e. created_at > pageLoadTime). Pre-existing orders missing
                    // from the DOM just mean the index hasn't rendered them yet —
                    // reloading for those causes an infinite loop.
                    else if (panel === 'waiter') {
                        var orderTs = order.created_at_ts ? order.created_at_ts * 1000 : 0;
                        if (!reloadTriggered[oid] && orderTs > pageLoadTime) {
                            reloadTriggered[oid] = true;
                            toast(orderMsg(order, order.status), 'info');
                            safeReload(1500);
                        } else {
                            // Pre-existing order not in DOM — just register it, no reload
                            reloadTriggered[oid] = true;
                        }
                    }
                    return;
                }

                // ── Order status changed ──────────────────────────────────────
                if (s.status !== order.status) {
                    toast(orderMsg(order, order.status), toastType(order.status));
                    s.status = order.status;
                    updateCard(order);

                    if (['paid', 'cancelled'].includes(order.status) && (panel === 'cashier' || panel === 'waiter')) {
                        removeCard(order.id, function () {
                            updatePendingCount();
                            var cont = document.querySelector('.space-y-3');
                            if (cont && !cont.querySelector('[data-order-id]')) {
                                var empty2 = document.createElement('div');
                                if (panel === 'cashier') {
                                    empty2.className = 'bg-white rounded-lg shadow p-8 text-center';
                                    empty2.innerHTML = '<div class="text-4xl mb-2">✓</div><p class="text-gray-600">All payments cleared!</p>';
                                } else {
                                    empty2.className = 'text-center py-12 text-gray-500';
                                    empty2.innerHTML = '<p class="text-lg">No orders today</p><a href="/waiter/orders/create" class="text-blue-500 hover:underline mt-2 inline-block text-sm">Create your first order</a>';
                                }
                                cont.appendChild(empty2);
                            }
                        });
                        return;
                    }

                    if (['paid', 'cancelled'].includes(order.status) && panel === 'cashier_parcels') {
                        removeCard(order.id);
                        return;
                    }

                    if ((panel === 'cook' || panel === 'admin') && !reloadTriggered['status_' + oid + '_' + order.status]) {
                        reloadTriggered['status_' + oid + '_' + order.status] = true;
                        safeReload(1500);
                    }
                }

                // ── Item status changes ───────────────────────────────────────
                order.items.forEach(function (item) {
                    var iid  = String(item.id);
                    var prev = s.items[iid];

                    if (prev === undefined) {
                        s.items[iid] = item.status;
                        if (panel === 'cook' || panel === 'admin') {
                            if (!reloadTriggered['item_' + iid]) {
                                reloadTriggered['item_' + iid] = true;
                                toast(itemAddedMsg(item, order.id), 'warning');
                                var card = document.querySelector('[data-order-id="' + order.id + '"]');
                                if (!card) {
                                    // Order not visible on current page (e.g. served order on pending view) — reload
                                    safeReload(1500);
                                } else if (!card.querySelector('[data-item-id="' + iid + '"]')) {
                                    var itemsContainer = card.querySelector('.space-y-2');
                                    if (itemsContainer) {
                                        var row = document.createElement('div');
                                        row.className = 'py-2 border-b last:border-0';
                                        row.setAttribute('data-item-id', iid);
                                        row.setAttribute('data-item-status', item.status);
                                        var notesHtml = item.notes
                                            ? '<div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ ' + item.notes + '</div>'
                                            : '';
                                        row.innerHTML =
                                            '<div class="flex justify-between items-center">' +
                                            '<div class="flex-1">' +
                                            '<div class="flex items-center gap-2">' +
                                            '<span class="font-semibold" data-item-name>' + item.name + '</span>' +
                                            '<span class="text-xs bg-red-100 text-red-700 px-1.5 py-0.5 rounded font-semibold">NEW</span>' +
                                            '</div>' +
                                            '<div class="text-sm text-gray-500">Qty: ' + item.quantity + '</div>' +
                                            notesHtml +
                                            '</div>' +
                                            '<div class="ml-3 action-btn-wrap" data-item-actions></div>' +
                                            '</div>';
                                        var actionsEl = row.querySelector('[data-item-actions]');
                                        var doneBtn = document.createElement('button');
                                        doneBtn.type = 'button';
                                        doneBtn.title = 'Mark as Prepared';
                                        doneBtn.className = 'action-btn action-btn-done';
                                        doneBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>';
                                        doneBtn.onclick = (function(id){ return function(){ cookItemAction('/cook/order-items/' + id + '/status', {status:'prepared'}, row, order.id); }; })(iid);
                                        actionsEl.appendChild(doneBtn);
                                        var cancelBtn = document.createElement('button');
                                        cancelBtn.type = 'button';
                                        cancelBtn.title = 'Cancel Item';
                                        cancelBtn.className = 'action-btn action-btn-cancel';
                                        cancelBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                                        cancelBtn.onclick = (function(id){ return function(){ cookItemAction('/cook/order-items/' + id + '/cancel', {}, row, order.id); }; })(iid);
                                        actionsEl.appendChild(cancelBtn);
                                        itemsContainer.appendChild(row);
                                        var counter = card.querySelector('.border-t span.text-sm');
                                        if (counter) {
                                            var allRows = card.querySelectorAll('[data-item-id]');
                                            var preparedCount = Array.from(allRows).filter(function(r){ return r.dataset.itemStatus === 'prepared'; }).length;
                                            counter.textContent = preparedCount + ' / ' + allRows.length + ' items prepared';
                                        }
                                        card.classList.remove('border-yellow-500','border-orange-500','border-green-500');
                                        card.classList.add('border-red-500');
                                    }
                                }
                            }
                        } else {
                            // waiter / cashier / cashier_parcels: inject item into card if visible, toast always
                            if (!reloadTriggered['item_' + iid]) {
                                reloadTriggered['item_' + iid] = true;
                                toast(itemAddedMsg(item, order.id), 'info');
                                // Inject new item row into cashier/waiter card
                                var card = document.querySelector('[data-order-id="' + order.id + '"]');
                                if (card) {
                                    var itemsContainer = card.querySelector('.space-y-2');
                                    if (itemsContainer && !card.querySelector('[data-item-id="' + iid + '"]')) {
                                        var notesHtml = item.notes
                                            ? '<div class="text-xs text-orange-600 italic mt-1 bg-orange-50 px-2 py-1 rounded">→ ' + item.notes + '</div>'
                                            : '';
                                        var lineTotal = (item.price * item.quantity).toFixed(2);
                                        var row = document.createElement('div');
                                        row.className = 'py-2 border-b';
                                        row.setAttribute('data-item-id', iid);
                                        row.setAttribute('data-item-status', item.status);
                                        row.innerHTML =
                                            '<div class="flex justify-between items-center">' +
                                            '<div class="flex-1">' +
                                            '<div class="flex items-center gap-2">' +
                                            '<span class="font-semibold">' + item.name + '</span>' +
                                            '<span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-semibold">NEW</span>' +
                                            '</div>' +
                                            '<div class="text-sm text-gray-600">Qty: ' + item.quantity + '</div>' +
                                            notesHtml +
                                            '</div>' +
                                            '<div class="text-right"><div class="font-bold">₹' + lineTotal + '</div></div>' +
                                            '</div>';
                                        itemsContainer.appendChild(row);
                                        // Update total display
                                        var totalEl = card.querySelector('[data-order-total]');
                                        if (totalEl) totalEl.textContent = 'Total: ₹' + parseFloat(order.total_amount).toFixed(2);
                                        // Update calculateChange binding with new total
                                        var cashOkBtn = card.querySelector('[onclick^="calculateChange"]');
                                        if (cashOkBtn) cashOkBtn.setAttribute('onclick', 'calculateChange(' + order.id + ',' + order.total_amount + ')');
                                    }
                                }
                            }
                        }
                        return;
                    }

                    if (prev !== item.status) {
                        s.items[iid] = item.status;
                        if (item.status === 'prepared') {
                            if (panel === 'cook' || panel === 'admin')
                                toast('✅ "' + item.name + '" ×' + item.quantity + ' in Order #' + order.id + ' is prepared.', 'success');
                            else if (panel === 'waiter')
                                toast('✅ "' + item.name + '" in Order #' + order.id + ' is ready.', 'success');
                        }
                        if (item.status === 'cancelled') {
                            toast('❌ "' + item.name + '" in Order #' + order.id + ' was cancelled.', 'danger');
                        }
                        updateCard(order);
                    }
                });
            });
        })
        .catch(function () {});
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Don't poll on create/form pages — reloading would reset the multi-step form.
        if (panel === 'waiter' && /\/orders\/create/.test(window.location.pathname)) return;
        buildSnapshot();
        setInterval(poll, 7000);
    });

    var style = document.createElement('style');
    style.textContent = '@keyframes pollToastIn{from{opacity:0;transform:translateX(-50%) translateY(-10px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}';
    document.head.appendChild(style);
})();
