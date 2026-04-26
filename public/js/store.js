(function() {
    window.Store = window.Store || {};

    // Restore from localStorage if backend cart count is 0
    if (Store.cart && Store.cart.count === 0) {
        try {
            const saved = localStorage.getItem('sf_cart');
            if (saved) {
                try {
                    Store.cart = JSON.parse(saved);
                } catch (e) {
                    console.error('Failed to parse cart from localStorage', e);
                }
            }
        } catch(e) {}
    }

    // Sync localStorage to server truth
    if (Store.user && Store.user.loggedIn && Store.cart && Store.cart.count > 0) {
        try {
            localStorage.setItem('sf_cart', JSON.stringify(Store.cart));
        } catch(e) {}
    }

    // Event listeners
    Store.on('cart:updated', (data) => {
        Store.cart = data;
        try {
            localStorage.setItem('sf_cart', JSON.stringify(data));
        } catch(e) {}
        
        // Fix #12: Update badge — always show/hide, never create new element
        var badge = document.querySelector('.sf-cart-badge');
        if (badge) {
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = '';
            } else {
                badge.innerText = '0';
                badge.style.display = 'none';
            }
        }
    });

    Store.on('variant:changed', (data) => {
        try {
            localStorage.setItem('sf_last_variant_' + data.productId, data.variantId);
        } catch(e) {}
    });

    // Default analytics listener
    Store.on('analytics', (data) => {
        console.debug('[Analytics]', data.event, data);
    });

    // ── Decision Engine Beacon ──────────────────────────────────────────────
    Store.track = function(eventName, meta = {}) {
        try {
            if (!navigator.sendBeacon) return;
            var payload = {
                event_name: eventName,
                page_url: window.location.href,
                page_type: window.location.pathname === '/' ? 'home' : (window.location.pathname.split('/')[1] || 'page'),
                meta: meta
            };
            navigator.sendBeacon('/api/beacon/track', JSON.stringify(payload));
        } catch (e) {
            console.error('Track error', e);
        }
    };

    // Auto page_view
    if (document.readyState === 'complete') {
        setTimeout(function(){ Store.track('page_view'); }, 100);
    } else {
        window.addEventListener('load', function() { setTimeout(function(){ Store.track('page_view'); }, 100); });
    }

    // Auto scroll tracking (25%, 50%, 75%)
    var scrollMarks = { 25: false, 50: false, 75: false };
    window.addEventListener('scroll', function() {
        var pct = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        if (pct >= 25 && !scrollMarks[25]) { scrollMarks[25] = true; Store.track('scroll_25'); }
        if (pct >= 50 && !scrollMarks[50]) { scrollMarks[50] = true; Store.track('scroll_50'); }
        if (pct >= 75 && !scrollMarks[75]) { scrollMarks[75] = true; Store.track('scroll_75'); }
    }, { passive: true });

    // AJAX Add to Cart Interceptor
    document.addEventListener('submit', function(e) {
        let form = e.target;
        while(form && form.tagName !== 'FORM') {
            form = form.parentElement;
        }
        if (!form) return;

        if (form.id === 'productForm' || form.classList.contains('form-add-to-cart')) {
            // Check if it's a Buy Now redirect
            const redirectInput = form.querySelector('input[name="redirect"]');
            if (redirectInput && redirectInput.value === 'checkout') {
                return; // Let standard form submission handle Buy Now
            }

            e.preventDefault();
            
            const btn = form.querySelector('button[type="submit"]');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat spin" style="display:inline-block;animation:spin 1s linear infinite;"></i> Adding...';
                if (!document.getElementById('spin-keyframes')) {
                    const style = document.createElement('style');
                    style.id = 'spin-keyframes';
                    style.innerHTML = '@keyframes spin { 100% { transform: rotate(360deg); } }';
                    document.head.appendChild(style);
                }
            }

            const formData = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (window.Store) {
                        Store.emit('toast', { type: 'success', message: data.message || 'Added to cart' });
                        Store.emit('cart:updated', { count: data.total_items });

                        // Fix #13: Fire AddToCart pixel on AJAX add-to-cart
                        if (data.analytics) {
                            var analyticsData = Object.assign({ event: 'add_to_cart' }, data.analytics);
                            Store.emit('analytics', analyticsData);

                            // GA4 dataLayer push
                            try {
                                window.dataLayer = window.dataLayer || [];
                                dataLayer.push({ ecommerce: null });
                                dataLayer.push({
                                    event: 'add_to_cart',
                                    ecommerce: {
                                        currency: data.analytics.currency || 'INR',
                                        value: data.analytics.value || 0,
                                        items: data.analytics.items || []
                                    }
                                });
                            } catch(e) { console.error('GA4 add_to_cart error:', e); }

                            // fbq AddToCart — dedup by variant in session
                            try {
                                if (typeof fbq === 'function') {
                                    var variantId = (data.analytics.items && data.analytics.items[0]) 
                                        ? data.analytics.items[0].item_id : '';
                                    var dedupKey = 'sf_atc_' + variantId;
                                    var alreadyFired = false;
                                    try { alreadyFired = sessionStorage.getItem(dedupKey) === '1'; } catch(e) {}

                                    if (!alreadyFired) {
                                        fbq('track', 'AddToCart', {
                                            value: data.analytics.value || 0,
                                            currency: data.analytics.currency || 'INR',
                                            content_ids: [variantId],
                                            content_type: 'product'
                                        });
                                        try { sessionStorage.setItem(dedupKey, '1'); } catch(e) {}
                                    }
                                }
                            } catch(e) { console.error('fbq AddToCart error:', e); }

                            // Decision Engine AddToCart
                            try {
                                Store.track('add_to_cart', {
                                    product_id: data.analytics.items && data.analytics.items[0] ? data.analytics.items[0].item_id : null,
                                    value: data.analytics.value
                                });
                            } catch(e) {}
                        }
                    }
                } else {
                    if (window.Store) Store.emit('toast', { type: 'error', message: data.message || 'Error adding to cart' });
                }
            })
            .catch(err => {
                console.error('AJAX cart error', err);
                if (window.Store) Store.emit('toast', { type: 'error', message: 'Failed to add item to cart.' });
            })
            .finally(() => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });
        }
    });

})();
