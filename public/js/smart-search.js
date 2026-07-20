(function () {
    'use strict';

    // Inject Luxury Glassmorphism Search Overlay CSS styles dynamically
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
            .sf-search-modal-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(17, 24, 39, 0.65);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                z-index: 99999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 40px 16px;
                overflow-y: auto;
            }
            .sf-search-modal-backdrop.open {
                opacity: 1;
                visibility: visible;
            }
            .sf-search-modal-container {
                background: #FFFFFF;
                width: 100%;
                max-width: 960px;
                border-radius: 20px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                border: 1px solid rgba(201, 168, 76, 0.3);
                overflow: hidden;
                transform: translateY(-20px) scale(0.98);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
            }
            .sf-search-modal-backdrop.open .sf-search-modal-container {
                transform: translateY(0) scale(1);
            }
            .sf-search-modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #E5E7EB;
                display: flex;
                align-items: center;
                gap: 16px;
                background: #FAFAFA;
                position: relative;
            }
            .sf-search-modal-header i.search-icon {
                font-size: 20px;
                color: #C9A84C;
            }
            .sf-search-modal-input {
                flex: 1;
                border: none;
                background: transparent;
                font-size: 16px;
                font-weight: 500;
                color: #111827;
                outline: none;
                font-family: inherit;
            }
            .sf-search-modal-close {
                background: #E5E7EB;
                border: none;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                color: #374151;
                font-size: 18px;
                transition: all 0.2s;
            }
            .sf-search-modal-close:hover {
                background: #E31E24;
                color: #FFFFFF;
            }
            .sf-search-modal-body {
                padding: 24px;
                max-height: 70vh;
                overflow-y: auto;
            }
            .sf-search-grid-3col {
                display: grid;
                grid-template-columns: 260px 1fr;
                gap: 24px;
            }
            @media (max-width: 768px) {
                .sf-search-grid-3col {
                    grid-template-columns: 1fr;
                }
            }
            .sf-search-col-title {
                font-size: 11px;
                font-weight: 700;
                color: #9CA3AF;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 12px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .sf-search-pills-wrap {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 20px;
            }
            .sf-search-pill {
                background: #F3F4F6;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 500;
                color: #374151;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.2s;
                cursor: pointer;
            }
            .sf-search-pill:hover {
                background: rgba(201, 168, 76, 0.15);
                color: #A08030;
            }
            .sf-search-pill .remove-pill {
                font-size: 12px;
                color: #9CA3AF;
                margin-left: 2px;
            }
            .sf-search-pill .remove-pill:hover {
                color: #E31E24;
            }
            .sf-search-products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 16px;
            }
            .sf-search-card {
                border: 1px solid #E5E7EB;
                border-radius: 12px;
                padding: 12px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                text-decoration: none;
                color: inherit;
                transition: all 0.2s ease;
                background: #FFFFFF;
                position: relative;
            }
            .sf-search-card:hover {
                border-color: #C9A84C;
                box-shadow: 0 8px 20px rgba(0,0,0,0.06);
                transform: translateY(-2px);
            }
            .sf-search-card-img-wrap {
                width: 100%;
                height: 120px;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 10px;
                background: #FAFAFA;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }
            .sf-search-card-img {
                max-height: 100%;
                width: auto;
                object-fit: contain;
            }
            .sf-search-card-disc {
                position: absolute;
                top: 6px;
                left: 6px;
                background: #E31E24;
                color: #FFFFFF;
                font-size: 10px;
                font-weight: 700;
                padding: 2px 6px;
                border-radius: 4px;
            }
            .sf-search-card-title {
                font-size: 13px;
                font-weight: 600;
                color: #111827;
                line-height: 1.3;
                margin-bottom: 6px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .sf-search-card-prices {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 10px;
            }
            .sf-search-card-price {
                font-weight: 700;
                font-size: 14px;
                color: #111827;
            }
            .sf-search-card-compare {
                font-size: 11px;
                color: #9CA3AF;
                text-decoration: line-through;
            }
            .sf-search-card-btn {
                width: 100%;
                background: #111827;
                color: #FFFFFF;
                border: none;
                border-radius: 20px;
                padding: 6px 12px;
                font-size: 11px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s;
                text-align: center;
            }
            .sf-search-card-btn:hover {
                background: #C9A84C;
                color: #000000;
            }
            .sf-search-card-btn.out-of-stock {
                background: #F3F4F6;
                color: #9CA3AF;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(style);
    }

    // Config & state
    const maxHistoryCount = 5;
    const trendingSearches = ["Perfume", "Attar", "Oud", "Body Spray", "Luxury Fragrance"];
    const popularCategories = [
        { name: "Attars", url: "/category/attars" },
        { name: "Perfumes", url: "/category/perfumes" },
        { name: "Oud Collection", url: "/category/oud" },
        { name: "Gift Sets", url: "/category/gift-sets" }
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
        renderModalDefaultContent();
    }

    window.addSearchProductToCartRemoveHistory = removeHistoryItem;

    // Cart Helper
    window.addSearchProductToCart = function (e, variantId) {
        e.preventDefault();
        e.stopPropagation();
        const btn = e.target;
        if (btn.classList.contains('out-of-stock') || btn.disabled) return;

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i>';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('/cart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                variant_id: variantId,
                qty: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (window.Store) {
                    Store.emit('toast', { type: 'success', message: data.message || 'Added to cart' });
                    Store.emit('cart:updated', { count: data.total_items });
                    Store.emit('cart:added');
                }
            } else {
                if (window.Store) Store.emit('toast', { type: 'error', message: data.message || 'Error adding to cart' });
            }
        })
        .catch(err => {
            console.error('AJAX add-to-cart error', err);
            if (window.Store) Store.emit('toast', { type: 'error', message: 'Failed to add item to cart.' });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    };

    // Modal creation & bindings
    let modalBackdrop = null;
    let modalInput = null;
    let modalBody = null;

    function buildSearchModal() {
        if (document.getElementById('sfSearchModalBackdrop')) return;

        modalBackdrop = document.createElement('div');
        modalBackdrop.id = 'sfSearchModalBackdrop';
        modalBackdrop.className = 'sf-search-modal-backdrop';
        modalBackdrop.innerHTML = `
            <div class="sf-search-modal-container">
                <div class="sf-search-modal-header">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="sf-search-modal-input" id="sfSearchModalInput" placeholder="Search for attars, perfumes, oud, luxury fragrances..." autocomplete="off">
                    <button type="button" class="sf-search-modal-close" id="sfSearchModalClose" aria-label="Close search"><i class="bi bi-x"></i></button>
                </div>
                <div class="sf-search-modal-body" id="sfSearchModalBody"></div>
            </div>
        `;
        document.body.appendChild(modalBackdrop);

        modalInput = document.getElementById('sfSearchModalInput');
        modalBody = document.getElementById('sfSearchModalBody');

        // Close handlers
        document.getElementById('sfSearchModalClose').addEventListener('click', closeSearchModal);
        modalBackdrop.addEventListener('click', function (e) {
            if (e.target === modalBackdrop) closeSearchModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modalBackdrop.classList.contains('open')) {
                closeSearchModal();
            }
        });

        let debounceTimer;
        modalInput.addEventListener('input', function () {
            const val = this.value.trim();
            clearTimeout(debounceTimer);

            if (val === '') {
                renderModalDefaultContent();
                return;
            }

            modalBody.innerHTML = `<div style="text-align:center;padding:40px;color:#9CA3AF;"><i class="bi bi-arrow-repeat spin" style="font-size:24px;display:inline-block;animation:spin 1s linear infinite;margin-bottom:8px;"></i><br>Searching live catalog...</div>`;

            debounceTimer = setTimeout(() => {
                fetch('/api/search/suggest?q=' + encodeURIComponent(val), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    renderSearchResults(val, data.items || []);
                })
                .catch(err => {
                    console.error('Error fetching suggestions', err);
                    modalBody.innerHTML = `<div style="text-align:center;padding:40px;color:#EF4444;">Unable to load search results right now.</div>`;
                });
            }, 250);
        });

        // Form submit on Enter
        modalInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && this.value.trim() !== '') {
                saveHistory(this.value.trim());
                window.location.href = '/search?q=' + encodeURIComponent(this.value.trim());
            }
        });
    }

    function openSearchModal(initialQuery = '') {
        buildSearchModal();
        modalBackdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        if (initialQuery) {
            modalInput.value = initialQuery;
            modalInput.dispatchEvent(new Event('input'));
        } else {
            modalInput.value = '';
            renderModalDefaultContent();
        }

        setTimeout(() => modalInput.focus(), 100);
    }

    function closeSearchModal() {
        if (!modalBackdrop) return;
        modalBackdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function renderModalDefaultContent() {
        if (!modalBody) return;
        const history = getHistory();

        let html = `<div class="sf-search-grid-3col">
            <div>`;

        // 1. Recent Searches
        if (history.length > 0) {
            html += `<div class="sf-search-col-title"><i class="bi bi-clock-history"></i> Recent Searches</div>`;
            html += `<div class="sf-search-pills-wrap">`;
            history.forEach(item => {
                html += `<div class="sf-search-pill" onclick="window.location.href='/search?q=${encodeURIComponent(item)}'">
                    ${item}
                    <span class="remove-pill" onclick="window.addSearchProductToCartRemoveHistory(event, '${item.replace(/'/g, "\\'")}')"><i class="bi bi-x"></i></span>
                </div>`;
            });
            html += `</div>`;
        }

        // 2. Trending Searches
        html += `<div class="sf-search-col-title"><i class="bi bi-graph-up-arrow" style="color:#C9A84C;"></i> Trending Searches</div>`;
        html += `<div class="sf-search-pills-wrap">`;
        trendingSearches.forEach(item => {
            html += `<a href="/search?q=${encodeURIComponent(item)}" class="sf-search-pill">
                ${item}
            </a>`;
        });
        html += `</div>`;

        // 3. Popular Categories
        html += `<div class="sf-search-col-title"><i class="bi bi-grid"></i> Popular Categories</div>`;
        html += `<div class="sf-search-pills-wrap">`;
        popularCategories.forEach(cat => {
            html += `<a href="${cat.url}" class="sf-search-pill">${cat.name}</a>`;
        });
        html += `</div>`;

        html += `</div>
            <div>
                <div class="sf-search-col-title"><i class="bi bi-stars" style="color:#C9A84C;"></i> Quick Search Tips</div>
                <div style="background:#FAFBFD;border:1px solid #E5E7EB;border-radius:12px;padding:20px;font-size:13px;color:#4B5563;line-height:1.6;">
                    Type fragrance name (e.g. <strong>Attar</strong>, <strong>Oud</strong>, <strong>Musk</strong>), or notes like <strong>Rose</strong> or <strong>Sandalwood</strong>. Live catalog items will appear instantly!
                </div>
            </div>
        </div>`;

        modalBody.innerHTML = html;
    }

    function renderSearchResults(query, items) {
        if (!modalBody) return;

        if (items.length === 0) {
            modalBody.innerHTML = `
                <div style="text-align:center;padding:40px 20px;">
                    <i class="bi bi-search" style="font-size:36px;color:#D1D5DB;display:block;margin-bottom:12px;"></i>
                    <h3 style="font-size:16px;font-weight:600;color:#111827;margin-bottom:4px;">No direct results found for "${query}"</h3>
                    <p style="font-size:13px;color:#6B7280;margin-bottom:20px;">Try searching for broader terms like "Perfume", "Attar", or "Oud".</p>
                    <a href="/search?q=${encodeURIComponent(query)}" style="background:#111827;color:#FFFFFF;padding:8px 20px;border-radius:20px;text-decoration:none;font-size:12px;font-weight:700;">View All Search Results ➔</a>
                </div>
            `;
            return;
        }

        const brands = items.filter(i => i.type === 'brand');
        const categories = items.filter(i => i.type === 'category');
        const products = items.filter(i => i.type === 'product');

        let html = `<div class="sf-search-grid-3col">`;

        // Left sidebar: Brands & Categories
        html += `<div>`;
        if (brands.length > 0) {
            html += `<div class="sf-search-col-title"><i class="bi bi-patch-check-fill" style="color:#C9A84C;"></i> Brands</div>`;
            html += `<div style="margin-bottom:20px;display:flex;flex-direction:column;gap:8px;">`;
            brands.forEach(b => {
                html += `<a href="${b.url}" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid #E5E7EB;border-radius:8px;text-decoration:none;color:#111827;font-weight:600;font-size:13px;">
                    ${b.image ? `<img src="${b.image}" style="width:24px;height:24px;object-fit:contain;border-radius:4px;">` : '<i class="bi bi-bag"></i>'}
                    ${b.title}
                </a>`;
            });
            html += `</div>`;
        }

        if (categories.length > 0) {
            html += `<div class="sf-search-col-title"><i class="bi bi-grid-fill"></i> Categories</div>`;
            html += `<div style="display:flex;flex-direction:column;gap:8px;">`;
            categories.forEach(c => {
                html += `<a href="${c.url}" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border:1px solid #E5E7EB;border-radius:8px;text-decoration:none;color:#111827;font-weight:600;font-size:13px;">
                    <i class="bi bi-arrow-right-short" style="color:#C9A84C;font-size:18px;"></i> ${c.title}
                </a>`;
            });
            html += `</div>`;
        }
        html += `</div>`;

        // Right side: Products Grid
        html += `<div>`;
        html += `<div class="sf-search-col-title"><i class="bi bi-bag-check-fill" style="color:#C9A84C;"></i> Matching Products (${products.length})</div>`;
        html += `<div class="sf-search-products-grid">`;

        products.forEach(p => {
            html += `<div class="sf-search-card" onclick="saveHistory('${p.title.replace(/'/g, "\\'")}'); window.location.href='${p.url}'">
                <div>
                    <div class="sf-search-card-img-wrap">
                        ${p.discount > 0 ? `<span class="sf-search-card-disc">${p.discount}% OFF</span>` : ''}
                        ${p.image ? `<img src="${p.image}" class="sf-search-card-img" alt="${p.title}">` : '<i class="bi bi-bag" style="font-size:32px;color:#D1D5DB;"></i>'}
                    </div>
                    <div class="sf-search-card-title">${p.title}</div>
                    <div class="sf-search-card-prices">
                        <span class="sf-search-card-price">${p.price}</span>
                        ${p.compare_price ? `<span class="sf-search-card-compare">${p.compare_price}</span>` : ''}
                    </div>
                </div>
                ${p.in_stock ? `
                    <button class="sf-search-card-btn" onclick="window.addSearchProductToCart(event, ${p.id})">+ Quick Add</button>
                ` : `
                    <button class="sf-search-card-btn out-of-stock" disabled>Sold Out</button>
                `}
            </div>`;
        });

        html += `</div></div></div>`;

        modalBody.innerHTML = html;
    }

    // Attach click listeners on search input boxes
    function bindInputs() {
        const desktopInputs = document.querySelectorAll('.sf-header-search-input, #site-search-input, input[name="q"]');
        desktopInputs.forEach(input => {
            if (input.dataset.searchBound) return;
            input.dataset.searchBound = 'true';

            input.addEventListener('click', function (e) {
                e.preventDefault();
                openSearchModal(this.value);
            });

            input.addEventListener('focus', function (e) {
                e.preventDefault();
                openSearchModal(this.value);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindInputs);
    } else {
        bindInputs();
    }
})();
