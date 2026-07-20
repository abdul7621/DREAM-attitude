(function () {
    'use strict';

    // Inject Search CSS styles dynamically
    const styleId = 'sf-smart-search-styles';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.innerHTML = `
            .sf-search-wrapper {
                position: relative;
                display: flex;
                align-items: center;
                width: 100%;
            }
            .sf-search-wrapper .sf-header-search-btn {
                position: absolute;
                right: 4px;
                top: 50%;
                transform: translateY(-50%);
                z-index: 10;
            }
            .sf-search-dropdown {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #ffffff;
                border: 1px solid rgba(201, 168, 76, 0.25);
                border-radius: 16px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
                z-index: 10000;
                margin-top: 8px;
                display: none;
                overflow: hidden;
                font-family: 'DM Sans', 'Inter', sans-serif;
                text-align: left;
                max-height: 480px;
                overflow-y: auto;
            }
            .sf-search-dropdown.open {
                display: block;
            }
            .sf-search-section-title {
                padding: 12px 16px 6px;
                font-size: 11px;
                font-weight: 700;
                color: #9ca3af;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: 1px solid #f3f4f6;
            }
            .sf-search-tags-container {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                padding: 12px 16px;
            }
            .sf-search-tag {
                background: #f3f4f6;
                padding: 6px 14px;
                border-radius: 20px;
                font-size: 12px;
                color: #374151;
                text-decoration: none;
                font-weight: 500;
                transition: all 0.2s;
                cursor: pointer;
                border: none;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .sf-search-tag:hover {
                background: rgba(201, 168, 76, 0.15);
                color: #a08030;
            }
            .sf-search-tag-remove {
                font-size: 10px;
                color: #9ca3af;
                margin-left: 2px;
            }
            .sf-search-tag-remove:hover {
                color: #ef4444;
            }
            .sf-search-item {
                display: flex;
                align-items: center;
                padding: 10px 16px;
                color: #374151;
                text-decoration: none;
                font-size: 13px;
                transition: background 0.15s;
                cursor: pointer;
                border-bottom: 1px solid #f9fafb;
            }
            .sf-search-item:hover {
                background-color: #faf5e8;
            }
            .sf-search-item-img {
                width: 44px;
                height: 44px;
                object-fit: cover;
                border-radius: 8px;
                margin-right: 12px;
                border: 1px solid #f3f4f6;
                background: #f9fafb;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .sf-search-item-info {
                flex: 1;
                min-width: 0;
            }
            .sf-search-item-title {
                font-weight: 600;
                color: #111827;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                margin-bottom: 2px;
                font-size: 13px;
            }
            .sf-search-item-meta {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
            }
            .sf-search-item-price {
                font-weight: 700;
                color: #111827;
            }
            .sf-search-item-compare {
                text-decoration: line-through;
                color: #9ca3af;
                font-size: 11px;
            }
            .sf-search-item-discount {
                background: #fee2e2;
                color: #ef4444;
                font-size: 9px;
                font-weight: 700;
                padding: 1px 5px;
                border-radius: 4px;
            }
            .sf-search-item-add {
                background: #111827;
                color: #ffffff;
                border: none;
                font-size: 11px;
                font-weight: 700;
                padding: 6px 16px;
                border-radius: 20px;
                cursor: pointer;
                margin-left: auto;
                transition: all 0.2s;
                flex-shrink: 0;
            }
            .sf-search-item-add:hover {
                background: #c9a84c;
                color: #000000;
            }
            .sf-search-item-add.out-of-stock {
                background: #f3f4f6;
                color: #9ca3af;
                cursor: not-allowed;
            }
            .sf-search-no-results {
                padding: 24px 16px;
                text-align: center;
                color: #6b7280;
                font-size: 13px;
            }
        `;
        document.head.appendChild(style);
    }

    // Config & state variables
    const maxHistoryCount = 5;
    const trendingSearches = ["Perfume", "Attar", "Oud", "Musk", "Luxury Fragrance"];
    const popularCategories = [
        { name: "Perfumes", url: "/search?q=Perfume" },
        { name: "Attars", url: "/search?q=Attar" },
        { name: "Oud Collection", url: "/search?q=Oud" },
        { name: "Luxury Fragrances", url: "/search?q=Fragrance" }
    ];

    function getHistory() {
        try {
            return JSON.parse(localStorage.getItem('sf_recent_searches')) || [];
        } catch (e) {
            return [];
        }
    }

    function saveHistory(query) {
        if (!query || query.trim() === '') return;
        query = query.trim();
        let history = getHistory();
        history = history.filter(item => item.toLowerCase() !== query.toLowerCase());
        history.unshift(query);
        if (history.length > maxHistoryCount) {
            history.pop();
        }
        try {
            localStorage.setItem('sf_recent_searches', JSON.stringify(history));
        } catch (e) {}
    }

    function removeHistoryItem(e, item) {
        e.preventDefault();
        e.stopPropagation();
        let history = getHistory();
        history = history.filter(q => q.toLowerCase() !== item.toLowerCase());
        try {
            localStorage.setItem('sf_recent_searches', JSON.stringify(history));
        } catch (e) {}
        const activeInput = document.activeElement;
        if (activeInput && activeInput.parentElement) {
            const dropdown = activeInput.parentElement.querySelector('.sf-search-dropdown');
            if (dropdown) renderDefaultContent(dropdown);
        }
    }

    // Add item to cart helper
    window.addSearchProductToCart = function (e, productId) {
        e.preventDefault();
        e.stopPropagation();
        const btn = e.target;
        if (btn.classList.contains('out-of-stock') || btn.disabled) return;

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('/cart/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                product_id: productId,
                qty: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' || data.success) {
                if (window.Store) {
                    Store.emit('toast', { type: 'success', message: data.message || 'Added to cart' });
                    Store.emit('cart:updated', { count: data.cart_count || data.total_items });
                }
            } else {
                if (window.Store) Store.emit('toast', { type: 'error', message: data.message || 'Error adding to cart' });
            }
        })
        .catch(err => {
            console.error('AJAX add-to-cart error', err);
            if (window.Store) Store.emit('toast', { type: 'error', message: 'Added to cart!' });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    };

    function renderDefaultContent(dropdown) {
        if (!dropdown) return;
        dropdown.innerHTML = '';

        const history = getHistory();
        let html = '';

        // 1. Recent Searches
        if (history.length > 0) {
            html += `<div class="sf-search-section-title">Recent Searches</div>`;
            html += `<div class="sf-search-tags-container">`;
            history.forEach(item => {
                html += `<div class="sf-search-tag" onclick="window.location.href='/search?q=${encodeURIComponent(item)}'">
                    ${item}
                    <span class="sf-search-tag-remove" onclick="window.addSearchProductToCartRemoveHistory(event, '${item.replace(/'/g, "\\'")}')"><i class="bi bi-x"></i></span>
                </div>`;
            });
            html += `</div>`;
        }

        // 2. Trending Searches
        html += `<div class="sf-search-section-title">Trending Searches</div>`;
        html += `<div class="sf-search-tags-container">`;
        trendingSearches.forEach(item => {
            html += `<a href="/search?q=${encodeURIComponent(item)}" class="sf-search-tag">
                <i class="bi bi-graph-up" style="font-size: 10px;"></i> ${item}
            </a>`;
        });
        html += `</div>`;

        // 3. Popular Categories
        html += `<div class="sf-search-section-title">Popular Categories</div>`;
        html += `<div class="sf-search-tags-container">`;
        popularCategories.forEach(cat => {
            html += `<a href="${cat.url}" class="sf-search-tag">${cat.name}</a>`;
        });
        html += `</div>`;

        dropdown.innerHTML = html;
    }

    window.addSearchProductToCartRemoveHistory = removeHistoryItem;

    function initSearchInput(input) {
        if (!input) return;

        let wrapper = input.parentElement;
        if (!wrapper.classList.contains('sf-search-wrapper')) {
            wrapper = document.createElement('div');
            wrapper.className = 'sf-search-wrapper';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            const form = wrapper.closest('form');
            if (form) {
                const btn = form.querySelector('button[type="submit"]');
                if (btn) wrapper.appendChild(btn);
            }
        }

        let dropdown = wrapper.querySelector('.sf-search-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'sf-search-dropdown';
            wrapper.appendChild(dropdown);
        }

        input.setAttribute('autocomplete', 'off');

        let debounceTimeout;

        input.addEventListener('focus', function () {
            const val = this.value.trim();
            if (val === '') {
                renderDefaultContent(dropdown);
            }
            dropdown.classList.add('open');
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                saveHistory(input.value);
            });
        }

        input.addEventListener('input', function () {
            const val = this.value.trim();
            clearTimeout(debounceTimeout);

            if (val === '') {
                renderDefaultContent(dropdown);
                return;
            }

            dropdown.innerHTML = `<div class="sf-search-no-results"><i class="bi bi-arrow-repeat spin" style="display:inline-block;animation:spin 1s linear infinite;margin-right:6px;"></i> Searching...</div>`;

            debounceTimeout = setTimeout(() => {
                fetch('/api/search/suggest?q=' + encodeURIComponent(val), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    const items = data.items || [];

                    if (items.length === 0) {
                        dropdown.innerHTML = `<div class="sf-search-no-results">No products found for "${val}"</div>`;
                        return;
                    }

                    const categories = items.filter(i => i.type === 'category');
                    const products = items.filter(i => i.type === 'product');

                    let html = '';

                    // 1. Categories
                    if (categories.length > 0) {
                        html += `<div class="sf-search-section-title">Categories</div>`;
                        categories.forEach(cat => {
                            html += `<a href="${cat.url}" class="sf-search-item">
                                <div class="sf-search-item-img"><i class="bi bi-grid"></i></div>
                                <div class="sf-search-item-info">
                                    <div class="sf-search-item-title">${cat.title}</div>
                                </div>
                            </a>`;
                        });
                    }

                    // 2. Products
                    if (products.length > 0) {
                        html += `<div class="sf-search-section-title">Products</div>`;
                        products.forEach(p => {
                            html += `<a href="${p.url}" class="sf-search-item" onclick="saveHistory('${p.title.replace(/'/g, "\\'")}')">
                                ${p.image ? `<img src="${p.image}" class="sf-search-item-img" alt="${p.title}">` : '<div class="sf-search-item-img"><i class="bi bi-bag"></i></div>'}
                                <div class="sf-search-item-info">
                                    <div class="sf-search-item-title">${p.title}</div>
                                    <div class="sf-search-item-meta">
                                        <span class="sf-search-item-price">${p.price}</span>
                                        ${p.compare_price ? `<span class="sf-search-item-compare">${p.compare_price}</span>` : ''}
                                        ${p.discount > 0 ? `<span class="sf-search-item-discount">${p.discount}% OFF</span>` : ''}
                                    </div>
                                </div>
                                ${p.in_stock ? `
                                    <button class="sf-search-item-add" onclick="window.addSearchProductToCart(event, ${p.id})">Add</button>
                                ` : `
                                    <button class="sf-search-item-add out-of-stock" disabled>Sold Out</button>
                                `}
                            </a>`;
                        });
                    }

                    dropdown.innerHTML = html;
                })
                .catch(err => {
                    console.error('Error fetching suggestions', err);
                });
            }, 200);
        });

        // Hide when clicking outside
        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }

    function initAll() {
        const inputs = document.querySelectorAll('input[type="search"], input[name="q"], .search-input, .sf-header-search-input, #site-search-input');
        inputs.forEach(initSearchInput);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
