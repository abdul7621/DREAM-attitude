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
            .sf-search-modal-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(17, 24, 39, 0.75);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                z-index: 99999;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                display: flex;
                justify-content: center;
                align-items: flex-start;
                padding: 20px 12px;
                overflow-y: auto;
            }
            .sf-search-modal-backdrop.open {
                opacity: 1;
                visibility: visible;
            }
            .sf-search-modal-container {
                background: #FFFFFF;
                width: 100%;
                max-width: 900px;
                border-radius: 20px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
                border: 1px solid rgba(201, 168, 76, 0.3);
                overflow: hidden;
                transform: translateY(-20px) scale(0.98);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                margin-top: 20px;
            }
            .sf-search-modal-backdrop.open .sf-search-modal-container {
                transform: translateY(0) scale(1);
            }
            .sf-search-modal-header {
                padding: 16px 20px;
                border-bottom: 1px solid #E5E7EB;
                display: flex;
                align-items: center;
                gap: 12px;
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
                min-width: 0;
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
                flex-shrink: 0;
            }
            .sf-search-modal-close:hover {
                background: #E31E24;
                color: #FFFFFF;
            }
            .sf-search-modal-body {
                padding: 20px;
                max-height: 75vh;
                overflow-y: auto;
            }
            .sf-search-grid-3col {
                display: grid;
                grid-template-columns: 240px 1fr;
                gap: 20px;
            }
            @media (max-width: 768px) {
                .sf-search-grid-3col {
                    grid-template-columns: 1fr;
                }
                .sf-search-modal-backdrop {
                    padding: 10px 8px;
                }
                .sf-search-modal-container {
                    margin-top: 5px;
                    border-radius: 16px;
                }
            }
            .sf-search-col-title {
                font-size: 11px;
                font-weight: 700;
                color: #9CA3AF;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .sf-search-pills-wrap {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                margin-bottom: 16px;
            }
            .sf-search-pill {
                background: #F3F4F6;
                padding: 5px 10px;
                border-radius: 16px;
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
            .sf-search-products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 12px;
            }
            .sf-search-card {
                border: 1px solid #E5E7EB;
                border-radius: 12px;
                padding: 10px;
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
                height: 110px;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 8px;
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
                top: 4px;
                left: 4px;
                background: #E31E24;
                color: #FFFFFF;
                font-size: 9px;
                font-weight: 700;
                padding: 2px 6px;
                border-radius: 4px;
            }
            .sf-search-card-title {
                font-size: 12px;
                font-weight: 600;
                color: #111827;
                line-height: 1.3;
                margin-bottom: 4px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .sf-search-card-prices {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 8px;
            }
            .sf-search-card-price {
                font-weight: 700;
                font-size: 13px;
                color: #111827;
            }
            .sf-search-card-compare {
                font-size: 10px;
                color: #9CA3AF;
                text-decoration: line-through;
            }
            .sf-search-card-btn {
                width: 100%;
                background: #111827;
                color: #FFFFFF;
                border: none;
                border-radius: 16px;
                padding: 5px 10px;
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
        `;
        document.head.appendChild(style);
    }

    // Modal DOM Elements
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

        // Close Handlers
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

            debounceTimer = setTimeout(() => {
                fetchSuggestions(val);
            }, 150);
        });

        // Submit on Enter
        modalInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && this.value.trim() !== '') {
                window.location.href = '/search?q=' + encodeURIComponent(this.value.trim());
            }
        });
    }

    function fetchSuggestions(query) {
        if (!modalBody) return;
        modalBody.innerHTML = `<div style="text-align:center;padding:30px;color:#9CA3AF;"><i class="bi bi-arrow-repeat spin" style="font-size:20px;display:inline-block;animation:spin 1s linear infinite;margin-bottom:6px;"></i><br>Searching catalog...</div>`;

        fetch('/api/search/suggest?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            renderSearchResults(query, data.items || [], data.is_fallback || false);
        })
        .catch(err => {
            console.error('Error fetching suggestions', err);
            modalBody.innerHTML = `<div style="text-align:center;padding:30px;color:#EF4444;">Unable to load search results right now.</div>`;
        });
    }

    function openSearchModal(initialQuery = '') {
        buildSearchModal();
        modalBackdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        if (initialQuery) {
            modalInput.value = initialQuery;
        }
        fetchSuggestions(modalInput.value.trim());

        setTimeout(() => modalInput.focus(), 100);
    }

    function closeSearchModal() {
        if (!modalBackdrop) return;
        modalBackdrop.classList.remove('open');
        document.body.style.overflow = '';
    }

    function renderSearchResults(query, items, isFallback) {
        if (!modalBody) return;

        const brands = items.filter(i => i.type === 'brand');
        const categories = items.filter(i => i.type === 'category');
        const products = items.filter(i => i.type === 'product');

        let html = '';

        if (isFallback && query) {
            html += `<div style="background:#FEF3C7;color:#92400E;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;margin-bottom:16px;">
                Showing catalog recommendations for "${query}"
            </div>`;
        }

        html += `<div class="sf-search-grid-3col">`;

        // Left Column: Categories & Brands
        html += `<div>`;
        if (categories.length > 0) {
            html += `<div class="sf-search-col-title"><i class="bi bi-grid-fill" style="color:#C9A84C;"></i> Categories</div>`;
            html += `<div class="sf-search-pills-wrap">`;
            categories.forEach(c => {
                html += `<a href="${c.url}" class="sf-search-pill"><i class="bi bi-arrow-right-short"></i> ${c.title}</a>`;
            });
            html += `</div>`;
        }

        if (brands.length > 0) {
            html += `<div class="sf-search-col-title"><i class="bi bi-patch-check-fill" style="color:#C9A84C;"></i> Brands</div>`;
            html += `<div class="sf-search-pills-wrap">`;
            brands.forEach(b => {
                html += `<a href="${b.url}" class="sf-search-pill">${b.title}</a>`;
            });
            html += `</div>`;
        }

        html += `<div class="sf-search-col-title"><i class="bi bi-graph-up-arrow" style="color:#C9A84C;"></i> Popular Searches</div>`;
        html += `<div class="sf-search-pills-wrap">
            <a href="/search?q=Perfume" class="sf-search-pill">Perfume</a>
            <a href="/search?q=Attar" class="sf-search-pill">Attar</a>
            <a href="/search?q=Oud" class="sf-search-pill">Oud</a>
        </div>`;
        html += `</div>`;

        // Right Column: Products Grid
        html += `<div>`;
        html += `<div class="sf-search-col-title"><i class="bi bi-bag-check-fill" style="color:#C9A84C;"></i> ${query ? 'Results' : 'Popular Products'} (${products.length})</div>`;
        html += `<div class="sf-search-products-grid">`;

        products.forEach(p => {
            html += `<div class="sf-search-card" onclick="window.location.href='${p.url}'">
                <div>
                    <div class="sf-search-card-img-wrap">
                        ${p.discount > 0 ? `<span class="sf-search-card-disc">${p.discount}% OFF</span>` : ''}
                        ${p.image ? `<img src="${p.image}" class="sf-search-card-img" alt="${p.title}">` : '<i class="bi bi-bag" style="font-size:28px;color:#D1D5DB;"></i>'}
                    </div>
                    <div class="sf-search-card-title">${p.title}</div>
                    <div class="sf-search-card-prices">
                        <span class="sf-search-card-price">${p.price}</span>
                        ${p.compare_price ? `<span class="sf-search-card-compare">${p.compare_price}</span>` : ''}
                    </div>
                </div>
                ${p.in_stock ? `
                    <button type="button" class="sf-search-card-btn" onclick="window.addSearchProductToCart(event, ${p.id})">+ Quick Add</button>
                ` : `
                    <button type="button" class="sf-search-card-btn" style="background:#F3F4F6;color:#9CA3AF;" disabled>Sold Out</button>
                `}
            </div>`;
        });

        html += `</div></div></div>`;

        modalBody.innerHTML = html;
    }

    // Global Delegated Click Handler (Works 100% on Mobile & Desktop)
    document.addEventListener('click', function (e) {
        const target = e.target;
        const searchInput = target.closest('input[type="search"], input[name="q"], .search-input, .sf-header-search-input, #site-search-input');
        const searchBtn = target.closest('.sf-search-toggle, button[aria-label="Search"], a[aria-label="Search"]');

        if (searchInput && !searchInput.classList.contains('sf-search-modal-input')) {
            e.preventDefault();
            openSearchModal(searchInput.value);
        } else if (searchBtn && !searchBtn.closest('.sf-search-modal-header')) {
            e.preventDefault();
            openSearchModal();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', buildSearchModal);
    } else {
        buildSearchModal();
    }
})();
