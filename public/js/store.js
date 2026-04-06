(function() {
    window.Store = window.Store || {};

    // Restore from localStorage if backend cart count is 0
    if (Store.cart && Store.cart.count === 0) {
        const saved = localStorage.getItem('sf_cart');
        if (saved) {
            try {
                Store.cart = JSON.parse(saved);
            } catch (e) {
                console.error('Failed to parse cart from localStorage', e);
            }
        }
    }

    // Sync localStorage to server truth
    if (Store.user && Store.user.loggedIn && Store.cart && Store.cart.count > 0) {
        localStorage.setItem('sf_cart', JSON.stringify(Store.cart));
    }

    // Event listeners
    Store.on('cart:updated', (data) => {
        Store.cart = data;
        localStorage.setItem('sf_cart', JSON.stringify(data));
        
        // Update UI
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            badge.innerText = data.count > 0 ? data.count : '';
        }
    });

    Store.on('variant:changed', (data) => {
        localStorage.setItem('sf_last_variant_' + data.productId, data.variantId);
    });

    // Default analytics listener
    Store.on('analytics', (data) => {
        console.debug('[Analytics]', data.event, data);
        // Future: fbq('track', ...), gtag('event', ...), etc.
    });

})();
