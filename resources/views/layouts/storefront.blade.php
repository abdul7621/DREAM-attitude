<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $ss = app(\App\Services\SettingsService::class); @endphp
    <title>@hasSection('title')@yield('title')@else{{ $pageTitle ?? $storeSettings['store_name'] ?? config('app.name') }}@endif</title>
    @if($ss->get('theme.favicon'))
        <link rel="icon" href="{{ asset('storage/' . $ss->get('theme.favicon')) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $ss->get('theme.favicon')) }}">
    @elseif(file_exists(public_path('favicon.ico')))
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <meta name="description" content="@hasSection('meta_description')@yield('meta_description')@else{{ $pageDescription ?? $storeSettings['meta_description'] ?? '' }}@endif">
    <meta property="og:title" content="@hasSection('title')@yield('title')@else{{ $pageTitle ?? $storeSettings['store_name'] ?? config('app.name') }}@endif">
    <meta property="og:description" content="@hasSection('meta_description')@yield('meta_description')@else{{ $pageDescription ?? $storeSettings['meta_description'] ?? '' }}@endif">
    <meta property="og:image" content="@hasSection('og_image') @yield('og_image') @else {{ asset('storage/' . ($ss->get('theme.og_image') ?? $ss->get('theme.logo') ?? '')) }} @endif">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url()->current() }}">
    @stack('meta')
    @include('partials.tracking-head')
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></noscript>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,400i,600,600i|dm-sans:300,400,500&display=swap" rel="stylesheet">
    <link href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}" rel="stylesheet">
    @php
        // Preload hero image for homepage
        $__heroPreload = null;
        if (request()->routeIs('home')) {
            $__ss = app(\App\Services\SettingsService::class);
            $__heroSlides = $__ss->get('theme.hero_slides');
            if (is_array($__heroSlides) && !empty($__heroSlides)) {
                $__heroPreload = asset('storage/' . $__heroSlides[0]['image']);
            } elseif ($__ss->get('theme.hero_image')) {
                $__heroPreload = asset('storage/' . $__ss->get('theme.hero_image'));
            }
        }
    @endphp
    @if($__heroPreload)
    <link rel="preload" as="image" href="{{ $__heroPreload }}">
    @endif
    
    <link rel="preload" href="https://unpkg.com/aos@2.3.1/dist/aos.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"></noscript>
    @stack('styles')
    
    @php
        $jsCart = $cartSummary ?? ['count' => 0, 'total' => '0.00'];
        $jsUser = auth()->check() ? ['id' => auth()->id(), 'loggedIn' => true, 'name' => auth()->user()->name] : ['id' => null, 'loggedIn' => false];
        $jsSettings = $storeSettings ?? ['codEnabled' => true, 'currency' => 'INR'];
    @endphp
    <script>
        window.Store = {
            cart: @json($jsCart),
            user: @json($jsUser),
            settings: @json($jsSettings),
            emit(event, data) { document.dispatchEvent(new CustomEvent(event, { detail: data })); },
            on(event, callback) { document.addEventListener(event, (e) => callback(e.detail)); },
            track(eventName, meta = {}) {
                try {
                    if (!navigator.sendBeacon) return;
                    var payload = {
                        event_name: eventName,
                        page_url: window.location.href,
                        page_type: window.location.pathname === '/' ? 'home' : (window.location.pathname.split('/')[1] || 'page'),
                        meta: meta
                    };
                    navigator.sendBeacon('/api/store/state', JSON.stringify(payload));
                } catch (e) { console.error('Track error', e); }
            }
        };
    </script>
    <script defer src="{{ asset('js/store.js') }}?v={{ filemtime(public_path('js/store.js')) }}"></script>
</head>
<body>
@include('partials.tracking-body')

{{-- ── Announcement Bar ────────────────────────────────── --}}
@php $announcementActive = $ss->get('theme.announcement_active', false); @endphp
@if($announcementActive && $ss->get('theme.announcement_text'))
<div class="sf-announce-bar" id="announcementBar">
    {{ $ss->get('theme.announcement_text') }}
    <button type="button" class="btn-dismiss" onclick="document.getElementById('announcementBar').style.display='none'; try{ sessionStorage.setItem('announce_closed','1'); }catch(e){}"><i class="bi bi-x"></i></button>
</div>
<script>
    try {
        if(sessionStorage.getItem('announce_closed') === '1') {
            document.getElementById('announcementBar').style.display = 'none';
        }
    } catch(e) {}
</script>
@endif

{{-- ── Header ──────────────────────────────────────────── --}}
<header class="sf-header">
    @if($ss->get('theme.logo'))
        <a class="logo" href="{{ route('home') }}">
            <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" class="sf-logo-header" loading="eager">
        </a>
    @elseif(file_exists(public_path('images/logo.png')))
        <a class="logo" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="sf-logo-header">
        </a>
    @else
        <a class="logo" href="{{ route('home') }}">{{ config('app.name') }}</a>
    @endif
    
    <nav class="sf-header-nav nav-links" id="desktopNav">
        @if (isset($globalMenus['header']) && $globalMenus['header']->parentItems->isNotEmpty())
            @foreach ($globalMenus['header']->parentItems as $item)
                @if($item->children->isNotEmpty())
                <div class="sf-nav-dropdown">
                    <a href="{{ $item->link ?: '#' }}" class="sf-nav-parent" @if($item->is_external) target="_blank" @endif>{{ $item->label }} <i class="bi bi-chevron-down sf-nav-chevron"></i></a>
                    <div class="sf-nav-dropdown-menu">
                        @foreach($item->children as $child)
                            <a href="{{ $child->link ?: '#' }}" @if($child->is_external) target="_blank" @endif>{{ $child->label }}</a>
                        @endforeach
                    </div>
                </div>
                @else
                <a href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a>
                @endif
            @endforeach
        @endif
    </nav>
    
    <div class="sf-header-nav">
        <form action="{{ route('search') }}" method="get" role="search" class="sf-desktop-search">
            <input type="search" name="q" class="search-input" placeholder="Search products…" aria-label="Search">
        </form>
        <button type="button" onclick="toggleMobileSearch()" class="sf-search-toggle sf-mobile-only" aria-label="Search" style="background:none; border:none; color:var(--color-text-primary); font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center; padding:4px;">
            <i class="bi bi-search"></i>
        </button>
        @auth
            <a href="{{ route('account.dashboard') }}" class="cart-icon ps-3"><i class="bi bi-person-circle"></i></a>
        @else
            <a href="{{ route('login') }}" class="cart-icon ps-3"><i class="bi bi-person"></i></a>
        @endauth
        <a href="{{ route('cart.index') }}" class="cart-icon sf-cart-wrapper">
            <i class="bi bi-bag sf-cart-icon"></i>
            {{-- Fix #12: Badge always rendered; hidden via style when count is 0 so JS can update --}}
            <span class="sf-cart-badge" style="pointer-events:none;{{ ($cartCount ?? 0) > 0 ? '' : 'display:none;' }}">{{ $cartCount ?? 0 }}</span>
        </a>
        <button class="sf-hamburger ms-3" onclick="document.getElementById('mobileDrawer').classList.add('open'); document.getElementById('drawerOverlay').classList.add('open');">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

{{-- Mobile Search Overlay --}}
<div class="sf-mobile-search-overlay" id="mobileSearchOverlay" style="display: none; background: var(--color-bg-primary); border-bottom: 1px solid var(--color-border); padding: 10px 16px; position: sticky; top: 60px; z-index: 99; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <form action="{{ route('search') }}" method="get" role="search" style="width: 100%;">
        <div style="display: flex; width: 100%; position: relative;">
            <input type="search" name="q" placeholder="Search products…" aria-label="Search" style="width: 100%; border: 1px solid var(--color-border); padding: 8px 16px; padding-right: 44px; border-radius: 24px; background: var(--color-bg-elevated); color: var(--color-text-primary); outline: none; font-size: 14px;">
            <button type="submit" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--color-gold); font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>
</div>

<script>
function toggleMobileSearch() {
    var overlay = document.getElementById('mobileSearchOverlay');
    if (overlay) {
        if (overlay.style.display === 'none') {
            overlay.style.display = 'block';
            var inp = overlay.querySelector('input');
            if (inp) inp.focus();
        } else {
            overlay.style.display = 'none';
        }
    }
}
</script>

{{-- ── Mobile Drawer ──────────────────────────────────────────── --}}
<div class="sf-drawer-overlay" id="drawerOverlay" onclick="document.getElementById('mobileDrawer').classList.remove('open'); this.classList.remove('open');"></div>
<div class="sf-mobile-drawer" id="mobileDrawer">
    <button class="close-drawer" onclick="document.getElementById('mobileDrawer').classList.remove('open'); document.getElementById('drawerOverlay').classList.remove('open');"><i class="bi bi-x"></i></button>
    <div class="mt-4 pt-3">
        {{-- Drawer Logo --}}
        @if($ss->get('theme.logo'))
            <a href="{{ route('home') }}" class="sf-drawer-logo-wrap">
                <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" class="sf-logo-drawer">
            </a>
        @elseif(file_exists(public_path('images/logo.png')))
            <a href="{{ route('home') }}" class="sf-drawer-logo-wrap">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="sf-logo-drawer">
            </a>
        @else
            <a href="{{ route('home') }}" style="display:block;margin-bottom:20px;color:var(--color-gold);font-weight:600;font-size:16px;text-transform:uppercase;letter-spacing:2px;text-decoration:none;">{{ config('app.name') }}</a>
        @endif
        @if (isset($globalMenus['header']) && $globalMenus['header']->parentItems->isNotEmpty())
            @foreach ($globalMenus['header']->parentItems as $mItem)
                @if($mItem->children->isNotEmpty())
                <div class="sf-drawer-group">
                    <div class="sf-drawer-parent">
                        <a href="{{ $mItem->link ?: '#' }}" @if($mItem->is_external) target="_blank" @endif>{{ $mItem->label }}</a>
                        <button type="button" class="sf-drawer-toggle" onclick="this.parentElement.parentElement.classList.toggle('open')" aria-label="Expand"><i class="bi bi-chevron-down"></i></button>
                    </div>
                    <div class="sf-drawer-children">
                        @foreach($mItem->children as $mChild)
                            <a href="{{ $mChild->link ?: '#' }}" @if($mChild->is_external) target="_blank" @endif>{{ $mChild->label }}</a>
                        @endforeach
                    </div>
                </div>
                @else
                <a href="{{ $mItem->link ?: '#' }}" @if($mItem->is_external) target="_blank" @endif>{{ $mItem->label }}</a>
                @endif
            @endforeach
        @endif
        <hr style="border-color: var(--color-border); margin: 16px 0;">
        @auth
            <a href="{{ route('account.dashboard') }}">My Account</a>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}">Admin</a>
            @endif
            <form action="{{ route('logout') }}" method="post" class="mt-3">@csrf
                <button type="submit" class="sf-btn-logout">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') ?? '/register' }}">Sign Up</a>
        @endauth
    </div>
</div>

<main>
    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Store.emit('toast', {type: 'success', message: {!! json_encode(session('status')) !!}});
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Store.emit('toast', {type: 'error', message: {!! json_encode(session('error')) !!}});
            });
        </script>
    @endif
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @foreach ($errors->all() as $e)
                    Store.emit('toast', {type: 'error', message: {!! json_encode($e) !!}});
                @endforeach
            });
        </script>
    @endif
    @if (session('analytics_add_to_cart'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.analyticsAddCartFired) {
                    window.analyticsAddCartFired = true;
                    Store.emit('analytics', {
                        event: 'add_to_cart',
                        ecommerce: {!! json_encode(session('analytics_add_to_cart')) !!}
                    });
                }
            });
        </script>
        {{ session()->forget('analytics_add_to_cart') /* Force clear to be safe */ }}
    @endif

    @yield('content')
</main>

@include('components.capture-modal')

{{-- ── Footer ──────────────────────────────────────────── --}}
<footer class="sf-footer">
    <div class="sf-container">
        <div class="sf-footer-grid">
            <div>
                @if($ss->get('theme.logo'))
                    <a href="{{ route('home') }}" class="sf-footer-logo-wrap">
                        <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" class="sf-logo-footer">
                    </a>
                @elseif(file_exists(public_path('images/logo.png')))
                    <a href="{{ route('home') }}" class="sf-footer-logo-wrap">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="sf-logo-footer">
                    </a>
                @else
                    <div class="brand">{{ config('app.name') }}</div>
                @endif
                <div class="tagline">{{ $ss->get('store.footer_text', 'Premium quality products delivered to your doorstep.') }}</div>
                <div class="social-icons">
                    @if($ss->get('store.instagram'))
                        <a href="{{ $ss->get('store.instagram') }}" target="_blank" rel="noopener"><i class="bi bi-instagram"></i></a>
                    @endif
                    @if($ss->get('store.facebook'))
                        <a href="{{ $ss->get('store.facebook') }}" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
                    @endif
                    @if($ss->get('store.whatsapp'))
                        <a href="https://wa.me/{{ $ss->get('store.whatsapp') }}" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i></a>
                    @endif
                </div>
            </div>
            
            <div>
                <button class="sf-footer-col-toggle" onclick="this.classList.toggle('open'); this.nextElementSibling.classList.toggle('open');">
                    <span>Shop</span><i class="bi bi-chevron-down"></i>
                </button>
                <div class="sf-footer-col-links links">
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('search') }}">All Products</a>
                    <a href="{{ route('cart.index') }}">Cart</a>
                </div>
            </div>
            
            <div>
                <button class="sf-footer-col-toggle" onclick="this.classList.toggle('open'); this.nextElementSibling.classList.toggle('open');">
                    <span>Policies</span><i class="bi bi-chevron-down"></i>
                </button>
                <div class="sf-footer-col-links links">
                    @if(isset($globalMenus['footer']) && $globalMenus['footer']->parentItems->isNotEmpty())
                        @foreach($globalMenus['footer']->parentItems as $item)
                            <a href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a>
                        @endforeach
                    @else
                        @php
                            $policyKeys = [
                                'privacy' => 'Privacy Policy', 
                                'returns' => 'Return & Refund Policy', 
                                'shipping' => 'Shipping Policy', 
                                'terms' => 'Terms & Conditions'
                            ];
                        @endphp
                        @foreach ($policyKeys as $key => $title)
                            @if ($ss->get('policies.' . $key))
                                <a href="{{ route('policy.show', str_replace('_', '-', $key)) }}">{{ $title }}</a>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            
            <div>
                <button class="sf-footer-col-toggle" onclick="this.classList.toggle('open'); this.nextElementSibling.classList.toggle('open');">
                    <span>Contact</span><i class="bi bi-chevron-down"></i>
                </button>
                <div class="sf-footer-col-links links">
                    @if ($ss->get('store.email'))
                        <a href="mailto:{{ $ss->get('store.email') }}"><i class="bi bi-envelope"></i> {{ $ss->get('store.email') }}</a>
                    @endif
                    @if ($ss->get('store.phone'))
                        <a href="tel:{{ $ss->get('store.phone') }}"><i class="bi bi-telephone"></i> {{ $ss->get('store.phone') }}</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="sf-footer-trust">
            <span><i class="bi bi-shield-check"></i> Secure Checkout</span>
            <span><i class="bi bi-truck"></i> Fast Delivery</span>
            <span><i class="bi bi-arrow-repeat"></i> Easy Returns</span>
        </div>
        
        @if($ss->get('theme.footer_seo_text'))
            <div class="sf-footer-seo-text">
                {!! nl2br(e($ss->get('theme.footer_seo_text'))) !!}
            </div>
        @endif
        
        <div class="sf-footer-copy">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>

{{-- Mobile Bottom Navigation (4 items: Home, Shop, Cart, Account) --}}
<nav class="sf-bottom-nav" aria-label="Mobile navigation">
    <div class="sf-bottom-nav-inner">
        <a href="{{ route('home') }}" class="sf-bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-house-door"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('search') }}" class="sf-bottom-nav-item {{ request()->routeIs('search') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span>Shop</span>
        </a>
        <a href="{{ route('cart.index') }}" class="sf-bottom-nav-item {{ request()->routeIs('cart.*') ? 'active' : '' }}">
            <i class="bi bi-bag"></i>
            @php $cartCount = count(session('cart.items', [])); @endphp
            @if($cartCount > 0)
            <span class="sf-bnav-badge">{{ $cartCount }}</span>
            @endif
            <span>Cart</span>
        </a>
        <a href="{{ route('account.dashboard') }}" class="sf-bottom-nav-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
            <i class="bi bi-person"></i>
            <span>Account</span>
        </a>
    </div>
</nav>

<x-toast />

{{-- Wishlist Heart System --}}
<script>
(function() {
    const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // Hydrate hearts from server
    if (isLoggedIn) {
        var hydrateWishlist = function() {
            fetch('{{ route("account.api.wishlist-ids") }}', {
                credentials: 'same-origin'
            })
                .then(function(r) { return r.json(); })
                .then(function(ids) {
                    var hearts = document.querySelectorAll('.wishlist-heart');
                    for (var h = 0; h < hearts.length; h++) {
                        var btn = hearts[h];
                        var pid = parseInt(btn.dataset.productId);
                        if (ids.indexOf(pid) !== -1) {
                            btn.classList.add('active');
                            var icon = btn.querySelector('i');
                            if (icon) icon.className = 'bi bi-heart-fill';
                            btn.dataset.wishlisted = '1';
                        }
                    }
                }).catch(function() {});
        };

        if ('requestIdleCallback' in window) {
            requestIdleCallback(hydrateWishlist);
        } else {
            setTimeout(hydrateWishlist, 1000);
        }
    }

    // Toggle click handler (safe for all browsers - no closest())
    document.addEventListener('click', function(e) {
        var el = e.target;
        var btn = null;
        while (el && el !== document) {
            if (el.classList && el.classList.contains('wishlist-heart')) { btn = el; break; }
            el = el.parentElement;
        }
        if (!btn) return;
        e.preventDefault();

        if (!isLoggedIn) {
            window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.href);
            return;
        }

        var productId = btn.dataset.productId;
        var icon = btn.querySelector('i');

        fetch('{{ route("account.wishlist.toggle") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.wishlisted) {
                btn.classList.add('active');
                if (icon) icon.className = 'bi bi-heart-fill';
                btn.dataset.wishlisted = '1';
            } else {
                btn.classList.remove('active');
                if (icon) icon.className = 'bi bi-heart';
                btn.dataset.wishlisted = '0';
            }
            if (window.Store) Store.emit('toast', { type: 'success', message: data.message });
        })
        .catch(function() {
            if (window.Store) Store.emit('toast', { type: 'error', message: 'Could not update wishlist.' });
        });
    });
})();
</script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var initAOS = function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    once: true,
                    easing: 'ease-out-cubic',
                    offset: 50
                });
            }
        };

        if ('requestIdleCallback' in window) {
            requestIdleCallback(initAOS);
        } else {
            setTimeout(initAOS, 500);
        }

        /* Number Counter Animation */
        const counters = document.querySelectorAll('.counter');
        const observerOptions = { root: null, threshold: 0.1 };
        
        if (counters.length > 0) {
            const observer = new IntersectionObserver(function(entries, observer) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.getAttribute('data-target'));
                        const duration = 2000;
                        let startTime = null;

                        function step(timestamp) {
                            if (!startTime) startTime = timestamp;
                            const progress = Math.min((timestamp - startTime) / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 3); // ease-out-cubic
                            entry.target.innerText = Math.floor(eased * target) + (entry.target.getAttribute('data-suffix') || '');
                            
                            if (progress < 1) {
                                window.requestAnimationFrame(step);
                            } else {
                                entry.target.innerText = target + (entry.target.getAttribute('data-suffix') || '');
                            }
                        }
                        window.requestAnimationFrame(step);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            counters.forEach(counter => observer.observe(counter));
        }
    });
</script>

@stack('scripts')

{{-- ── Side Cart Drawer ──────────────────── --}}
<div class="sf-sidecart-overlay" id="sideCartOverlay"></div>
<div class="sf-sidecart" id="sideCartDrawer">
    <div class="sf-sidecart-header">
        <h6><i class="bi bi-bag me-1"></i> Your Cart (<span id="scCount">0</span>)</h6>
        <button type="button" class="sf-sidecart-close" id="sideCartClose"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="sf-sidecart-progress" id="scShippingBar" style="display:none;">
        <div class="sf-sidecart-progress-track">
            <div class="sf-sidecart-progress-fill" id="scProgressFill" style="width:0%"></div>
        </div>
        <small id="scProgressText"></small>
    </div>
    <div class="sf-sidecart-body" id="scBody">
        <div class="sf-sidecart-empty" id="scEmpty">
            <i class="bi bi-bag-x" style="font-size:40px;opacity:.3;"></i>
            <p>Your cart is empty</p>
        </div>
    </div>
    <div class="sf-sidecart-footer" id="scFooter" style="display:none;">
        <div class="sf-sidecart-subtotal">
            <span>Subtotal</span>
            <span id="scSubtotal">₹0</span>
        </div>
        <div style="display:flex; flex-direction:column; gap:8px;">
            <a href="{{ route('cart.index') }}" style="display:block; text-align:center; padding:12px; border:1px solid var(--color-gold); color:#0A0A0A; border-radius:4px; text-decoration:none; font-weight:600; font-size:12px; text-transform:uppercase; font-family:'DM Sans', sans-serif;">View Cart</a>
            <a href="{{ route('checkout.create') }}" class="sf-btn-primary" style="display:flex; align-items:center; justify-content:center; text-decoration:none;">Checkout</a>
        </div>
    </div>
</div>

{{-- Quick View Modal --}}
<div class="sf-quickview-modal-overlay" id="quickViewModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1060; align-items: center; justify-content: center; padding: 16px; opacity: 0; transition: opacity 0.3s ease;">
    <div class="sf-quickview-modal-content" style="background: var(--color-bg-primary); border-radius: 16px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; position: relative; display: flex; flex-direction: column; box-shadow: 0 10px 30px rgba(0,0,0,0.3); border: 1px solid var(--color-border); transform: scale(0.95); transition: transform 0.3s ease;">
        
        <!-- Close Button -->
        <button type="button" class="btn-close-qv" onclick="closeQuickView()" style="position: absolute; right: 16px; top: 16px; background: none; border: none; font-size: 24px; color: var(--color-text-muted); cursor: pointer; z-index: 110;">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="row g-0" style="flex: 1;">
            <!-- Left Column: Gallery -->
            <div class="col-md-6 p-4 d-flex flex-column align-items-center justify-content-center bg-light" style="border-right: 1px solid var(--color-border); min-height: 300px; position: relative;">
                <div id="qv-badge-save" class="badge bg-danger position-absolute" style="top: 16px; left: 16px; z-index: 100; display: none;">SAVE</div>
                <img id="qv-main-image" src="" alt="" class="img-fluid rounded shadow-sm" style="max-height: 350px; object-fit: contain;">
                <div id="qv-thumbnails" class="d-flex gap-2 mt-3 overflow-x-auto w-100 justify-content-center" style="max-width: 100%;"></div>
            </div>

            <!-- Right Column: Product details -->
            <div class="col-md-6 p-4 d-flex flex-column">
                <h3 id="qv-product-name" class="h4 fw-bold mb-2" style="color: var(--color-text-primary); margin-top: 20px;"></h3>
                <p id="qv-short-desc" class="text-muted small mb-3"></p>

                <div class="price-row mb-4">
                    <span id="qv-price-retail" class="fs-4 fw-bold text-success"></span>
                    <span id="qv-compare-price" class="text-muted text-decoration-line-through ms-2 small"></span>
                </div>

                <!-- Product Form -->
                <form id="qv-add-to-cart-form" action="{{ route('cart.items.store') }}" method="POST" class="mt-auto">
                    @csrf
                    <input type="hidden" name="variant_id" id="qv-variant-id">
                    
                    <!-- Variant Selector -->
                    <div id="qv-variants-container" class="mb-4">
                        <label class="form-label small fw-bold mb-2">Select Option</label>
                        <div id="qv-variant-chips" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Quantity and Button -->
                    <div class="d-flex gap-3 align-items-center">
                        <div class="input-group" style="width: 120px; display: flex; align-items: center;">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="changeQvQty(-1)"><i class="bi bi-dash"></i></button>
                            <input type="number" name="qty" id="qv-qty-input" class="form-control form-control-sm text-center" value="1" min="1" readonly style="border-left:0; border-right:0;">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="changeQvQty(1)"><i class="bi bi-plus"></i></button>
                        </div>

                        <button type="submit" id="qv-submit-btn" class="btn btn-primary flex-grow-1" style="border-radius: 8px; font-weight: 600; padding: 10px 16px;">
                            Add to Cart
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
var qvProductData = null;

function closeQuickView() {
    var overlay = document.getElementById('quickViewModalOverlay');
    var content = overlay.querySelector('.sf-quickview-modal-content');
    if (overlay) {
        overlay.style.opacity = '0';
        content.style.transform = 'scale(0.95)';
        setTimeout(function() {
            overlay.style.display = 'none';
        }, 300);
    }
}

function changeQvQty(val) {
    var inp = document.getElementById('qv-qty-input');
    var current = parseInt(inp.value) || 1;
    var newval = current + val;
    if (newval < 1) newval = 1;
    inp.value = newval;
}

// Global click event to catch quick view clicks
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.sf-quickview-btn');
    if (!btn) return;
    e.preventDefault();

    var slug = btn.dataset.productSlug;
    if (!slug) return;

    fetch('/p/' + slug + '?qv=1', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        qvProductData = data;
        
        // Populate fields
        document.getElementById('qv-product-name').textContent = data.name;
        document.getElementById('qv-short-desc').textContent = data.short_description || '';
        
        // Image Gallery
        var mainImg = document.getElementById('qv-main-image');
        mainImg.src = data.images[0] || '/images/placeholder.png';
        
        var thumbs = document.getElementById('qv-thumbnails');
        thumbs.innerHTML = '';
        if (data.images.length > 1) {
            data.images.forEach(img => {
                var thumb = document.createElement('img');
                thumb.src = img;
                thumb.style.width = '50px';
                thumb.style.height = '50px';
                thumb.style.objectFit = 'cover';
                thumb.style.cursor = 'pointer';
                thumb.className = 'border rounded p-1';
                thumb.onclick = function() { mainImg.src = img; };
                thumbs.appendChild(thumb);
            });
        }

        // Reset Qty
        document.getElementById('qv-qty-input').value = 1;

        // Render variants
        var chips = document.getElementById('qv-variant-chips');
        chips.innerHTML = '';
        
        var isSingleVariant = data.variants.length === 1 && (data.variants[0].title.toLowerCase() === 'default title' || data.variants[0].title.trim() === '');
        
        if (isSingleVariant) {
            document.getElementById('qv-variants-container').style.display = 'none';
            selectQvVariant(data.variants[0]);
        } else {
            document.getElementById('qv-variants-container').style.display = 'block';
            data.variants.forEach((v, index) => {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'btn btn-sm btn-outline-secondary' + (index === 0 ? ' active' : '');
                chip.style.margin = '2px';
                chip.textContent = v.title;
                chip.onclick = function() {
                    chips.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    chip.classList.add('active');
                    selectQvVariant(v);
                };
                chips.appendChild(chip);
            });
            selectQvVariant(data.variants[0]);
        }

        // Show Modal
        var overlay = document.getElementById('quickViewModalOverlay');
        var content = overlay.querySelector('.sf-quickview-modal-content');
        overlay.style.display = 'flex';
        setTimeout(function() {
            overlay.style.opacity = '1';
            content.style.transform = 'scale(1)';
        }, 50);
    });
});

function selectQvVariant(v) {
    document.getElementById('qv-variant-id').value = v.id;
    document.getElementById('qv-price-retail').textContent = '₹' + parseFloat(v.price).toFixed(2);
    
    var compEl = document.getElementById('qv-compare-price');
    var badgeEl = document.getElementById('qv-badge-save');
    
    if (v.compare_price && parseFloat(v.compare_price) > parseFloat(v.price)) {
        compEl.textContent = '₹' + parseFloat(v.compare_price).toFixed(2);
        compEl.style.display = 'inline';
        
        var saveAmt = parseFloat(v.compare_price) - parseFloat(v.price);
        badgeEl.textContent = 'SAVE ₹' + saveAmt.toFixed(0);
        badgeEl.style.display = 'block';
    } else {
        compEl.style.display = 'none';
        badgeEl.style.display = 'none';
    }

    var submitBtn = document.getElementById('qv-submit-btn');
    if (v.track_inventory && parseInt(v.stock_qty) <= 0) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Out of Stock';
        submitBtn.className = 'btn btn-secondary flex-grow-1';
    } else {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add to Cart';
        submitBtn.className = 'btn btn-primary flex-grow-1';
    }
}
</script>

<script>
    window.__sideCartConfig = {
        freeThreshold: 500,
        showBar: true,
        currency: '{{ config('commerce.currency', 'INR') }}'
    };

    // Global AJAX Add to Cart for product grids is handled globally in store.js
</script>
<script defer src="{{ asset('js/side-cart.js') }}?v={{ @filemtime(public_path('js/side-cart.js')) ?: '1' }}"></script>

@include('partials.whatsapp_widget')

</body>
</html>
