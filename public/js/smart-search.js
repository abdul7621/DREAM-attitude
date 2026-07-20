(function () {
    const input = document.getElementById('site-search-input');
    const container = document.getElementById('search-autocomplete-results');
    const suggestUrl = '/api/search/suggest';

    if (!input || !container) return;

    let debounceTimeout;

    // Load recent searches from localStorage
    function getRecentSearches() {
        try {
            return JSON.parse(localStorage.getItem('recent_searches')) || [];
        } catch (e) {
            return [];
        }
    }

    // Save a search query to localStorage
    function saveSearchQuery(query) {
        if (!query) return;
        query = query.trim();
        if (query.length < 2) return;

        let searches = getRecentSearches();
        searches = searches.filter(item => item.toLowerCase() !== query.toLowerCase());
        searches.unshift(query);
        searches = searches.slice(0, 5); // Keep top 5
        localStorage.setItem('recent_searches', JSON.stringify(searches));
    }

    // Render suggestions
    function renderSuggestions(items) {
        container.innerHTML = '';
        if (items.length === 0) {
            container.innerHTML = '<div class="p-3 text-muted text-center small">No matching products found</div>';
            return;
        }

        const listGroup = document.createElement('div');
        listGroup.className = 'list-group list-group-flush';

        items.forEach(item => {
            const anchor = document.createElement('a');
            anchor.href = item.url;
            anchor.className = 'list-group-item list-group-item-action d-flex align-items-center p-2 text-decoration-none text-dark';
            anchor.style.gap = '12px';

            const img = document.createElement('img');
            img.src = item.image;
            img.alt = item.title;
            img.style.width = '44px';
            img.style.height = '44px';
            img.style.objectFit = 'cover';
            img.className = 'rounded border';

            const info = document.createElement('div');
            info.className = 'flex-grow-1 min-w-0';

            const title = document.createElement('div');
            title.className = 'text-truncate fw-semibold small';
            title.textContent = item.title;
            title.style.color = '#1e293b';

            const priceContainer = document.createElement('div');
            priceContainer.className = 'd-flex align-items-center gap-2';

            const price = document.createElement('span');
            price.className = 'fw-bold text-success small';
            price.textContent = item.price;

            priceContainer.appendChild(price);

            if (item.compare_price) {
                const comp = document.createElement('span');
                comp.className = 'text-muted text-decoration-line-through small';
                comp.style.fontSize = '11px';
                comp.textContent = item.compare_price;
                priceContainer.appendChild(comp);
            }

            info.appendChild(title);
            info.appendChild(priceContainer);

            anchor.appendChild(img);
            anchor.appendChild(info);

            // Record successful selection to history
            anchor.addEventListener('click', function() {
                saveSearchQuery(input.value);
            });

            listGroup.appendChild(anchor);
        });

        container.appendChild(listGroup);
        container.style.display = 'block';
    }

    // Render recent searches
    function showRecentSearches() {
        const searches = getRecentSearches();
        if (searches.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.innerHTML = '';
        const wrapper = document.createElement('div');
        wrapper.className = 'p-3';

        const title = document.createElement('div');
        title.className = 'text-muted small fw-bold mb-2 text-uppercase';
        title.style.letterSpacing = '0.5px';
        title.textContent = 'Recent Searches';

        const list = document.createElement('div');
        list.className = 'd-flex flex-wrap gap-2';

        searches.forEach(search => {
            const btn = document.createElement('a');
            btn.href = '/search?q=' + encodeURIComponent(search);
            btn.className = 'btn btn-sm btn-light rounded-pill px-3 text-secondary border d-flex align-items-center gap-1';
            btn.innerHTML = `<i class="bi bi-clock-history"></i> ${search}`;
            list.appendChild(btn);
        });

        wrapper.appendChild(title);
        wrapper.appendChild(list);
        container.appendChild(wrapper);
        container.style.display = 'block';
    }

    // Attach search input events
    input.addEventListener('input', function () {
        clearTimeout(debounceTimeout);
        const q = this.value.trim();

        if (q.length < 2) {
            if (q.length === 0) {
                showRecentSearches();
            } else {
                container.style.display = 'none';
            }
            return;
        }

        debounceTimeout = setTimeout(function () {
            fetch(suggestUrl + '?q=' + encodeURIComponent(q), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                renderSuggestions(data.items || []);
            })
            .catch(() => {});
        }, 150);
    });

    input.addEventListener('focus', function () {
        if (this.value.trim().length === 0) {
            showRecentSearches();
        }
    });

    // Save query on form submit
    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            saveSearchQuery(input.value);
        });
    }

    // Hide suggestions dropdown on click outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !container.contains(e.target)) {
            container.style.display = 'none';
        }
    });
})();
