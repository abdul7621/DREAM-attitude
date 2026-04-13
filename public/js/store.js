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
        
        // Update UI
        const badge = document.querySelector('.sf-cart-badge') || document.querySelector('.cart-badge');
        if (badge) {
            badge.innerText = data.count > 0 ? data.count : '';
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
        // Future: fbq('track', ...), gtag('event', ...), etc.
    });

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

            // Create object fallback for spread operator compatibility
            let analyticsFallback = {};
            
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
                        if (data.analytics) {
                            // Non-spread object assign
                            analyticsFallback = Object.assign({ event: 'add_to_cart' }, data.analytics);
                            Store.emit('analytics', analyticsFallback);
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
