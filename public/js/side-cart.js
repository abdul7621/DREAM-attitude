/**
 * Side Cart — AJAX Sliding Cart Drawer
 * Feature: side_cart
 * Only loaded when feature is ON (via @feature blade directive)
 */
(function () {
    'use strict';

    const cfg = window.__sideCartConfig || { freeThreshold: 500, showBar: true, currency: 'INR' };
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // DOM refs
    const overlay = document.getElementById('sideCartOverlay');
    const drawer = document.getElementById('sideCartDrawer');
    const closeBtn = document.getElementById('sideCartClose');
    const body = document.getElementById('scBody');
    const footer = document.getElementById('scFooter');
    const countEl = document.getElementById('scCount');
    const subtotalEl = document.getElementById('scSubtotal');
    const emptyEl = document.getElementById('scEmpty');
    const shippingBar = document.getElementById('scShippingBar');
    const progressFill = document.getElementById('scProgressFill');
    const progressText = document.getElementById('scProgressText');

    if (!drawer) return;

    // ── Open / Close ──────────────────────────────────────
    function open() {
        drawer.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function close() {
        drawer.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

    // ── Render ─────────────────────────────────────────────
    function render(data) {
        const items = data.items || [];
        const count = data.count || 0;
        const subtotal = parseFloat(data.subtotal || 0);

        // Update header badge globally
        countEl.textContent = count;
        const badges = document.querySelectorAll('.sf-cart-badge, .sf-bnav-badge');
        badges.forEach(b => {
            b.textContent = count;
            b.style.display = count > 0 ? '' : 'none';
        });

        // Update Store.cart global
        if (window.Store) {
            window.Store.cart.count = count;
            window.Store.cart.total = data.subtotal;
        }
        
        // Dispatch global event for other components
        document.dispatchEvent(new CustomEvent('cart:updated', { detail: data }));

        if (items.length === 0) {
            emptyEl.style.display = 'flex';
            footer.style.display = 'none';
            // Remove item elements
            body.querySelectorAll('.sc-item').forEach(el => el.remove());
            return;
        }

        emptyEl.style.display = 'none';
        footer.style.display = '';
        subtotalEl.textContent = data.subtotal_formatted || ('₹' + subtotal.toLocaleString('en-IN', { minimumFractionDigits: 0 }));

        // Free shipping bar (Dream Attitude custom: >= 500 is free for prepaid)
        if (cfg.showBar && cfg.freeThreshold > 0) {
            shippingBar.style.display = '';
            const pct = Math.min(100, (subtotal / cfg.freeThreshold) * 100);
            progressFill.style.width = pct + '%';
            if (subtotal >= cfg.freeThreshold) {
                progressText.innerHTML = '🎉 <b>Free shipping</b> on Prepaid unlocked!';
                progressFill.style.background = '#28a745';
            } else {
                const rem = Math.ceil(cfg.freeThreshold - subtotal);
                // We don't have formatted remaining amount from API here, so we fallback to INR format or symbol
                progressText.innerHTML = 'Add <b>' + (data.currency === 'INR' ? '₹' : '') + rem.toLocaleString('en-IN') + ' ' + (data.currency !== 'INR' ? data.currency : '') + '</b> more for free prepaid shipping';
                progressFill.style.background = '';
            }
        } else {
            shippingBar.style.display = 'none';
        }

        // Build items HTML
        let html = '';
        items.forEach(item => {
            html += `<div class="sc-item" data-id="${item.item_id}">
                <div class="sc-item-img">
                    ${item.image ? `<img src="${item.image}" alt="${item.name}" loading="lazy">` : '<div class="sc-item-noimg"><i class="bi bi-image"></i></div>'}
                </div>
                <div class="sc-item-info">
                    <a href="${item.url}" class="sc-item-name">${item.name}</a>
                    ${item.variant ? `<small class="text-muted">${item.variant}</small>` : ''}
                    <div class="sc-item-price">${item.unit_price_formatted || ('₹' + parseFloat(item.unit_price).toLocaleString('en-IN'))}</div>
                    <div class="sc-item-actions">
                        <div class="sc-qty">
                            <button type="button" class="sc-qty-btn" data-action="decrease" data-id="${item.item_id}">−</button>
                            <span class="sc-qty-val">${item.qty}</span>
                            <button type="button" class="sc-qty-btn" data-action="increase" data-id="${item.item_id}">+</button>
                        </div>
                        <button type="button" class="sc-remove" data-id="${item.item_id}" title="Remove"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>
            </div>`;
        });

        // Replace only items (keep emptyEl)
        body.querySelectorAll('.sc-item').forEach(el => el.remove());
        emptyEl.insertAdjacentHTML('beforebegin', html);
    }

    // ── API Calls ─────────────────────────────────────────
    function fetchCart() {
        fetch('/cart/data', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(render)
            .catch(console.error);
    }

    function updateQty(itemId, qty) {
        fetch(`/cart/items/${itemId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ qty: qty })
        })
            .then(r => r.json())
            .then(render)
            .catch(console.error);
    }

    function removeItem(itemId) {
        fetch(`/cart/items/${itemId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
            .then(r => r.json())
            .then(render)
            .catch(console.error);
    }

    // ── Event Delegation for Qty/Remove ───────────────────
    body.addEventListener('click', function (e) {
        const qtyBtn = e.target.closest('.sc-qty-btn');
        if (qtyBtn) {
            const id = qtyBtn.dataset.id;
            const valEl = qtyBtn.closest('.sc-qty').querySelector('.sc-qty-val');
            let qty = parseInt(valEl.textContent) || 1;
            if (qtyBtn.dataset.action === 'increase') qty++;
            else qty = Math.max(0, qty - 1);
            updateQty(id, qty);
            return;
        }

        const rmBtn = e.target.closest('.sc-remove');
        if (rmBtn) {
            removeItem(rmBtn.dataset.id);
        }
    });

    // ── Listen to Global Add to Cart ──────────────────────
    if (window.Store) {
        window.Store.on('cart:added', function () {
            fetchCart();
            setTimeout(open, 150);
        });
    }

    // ── Cart icon click opens side cart instead of navigating ──
    const cartLink = document.querySelector('a[href$="/cart"]');
    if (cartLink) {
        cartLink.addEventListener('click', function (e) {
            e.preventDefault();
            fetchCart();
            open();
        });
    }

    // Initial load (populate count)
    fetchCart();

})();
