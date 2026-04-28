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
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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
            <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" style="max-height: 40px;" loading="eager">
        </a>
    @elseif(file_exists(public_path('images/logo.png')))
        <a class="logo" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height: 40px;">
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
        <form action="{{ route('search') }}" method="get" role="search">
            <input type="search" name="q" class="search-input" placeholder="Search products…" aria-label="Search">
        </form>
        @auth
            <a href="{{ route('account.dashboard') }}" class="cart-icon ps-3"><i class="bi bi-person-circle"></i></a>
        @else
            <a href="{{ route('login') }}" class="cart-icon ps-3"><i class="bi bi-person"></i></a>
        @endauth
        <a href="{{ route('cart.index') }}" class="cart-icon" style="position:relative;display:inline-flex;align-items:center;margin-left:12px;">
            <i class="bi bi-bag" style="font-size:20px;"></i>
            {{-- Fix #12: Badge always rendered; hidden via style when count is 0 so JS can update --}}
            <span class="sf-cart-badge" style="pointer-events:none;{{ ($cartCount ?? 0) > 0 ? '' : 'display:none;' }}">{{ $cartCount ?? 0 }}</span>
        </a>
        <button class="sf-hamburger ms-3" onclick="document.getElementById('mobileDrawer').classList.add('open'); document.getElementById('drawerOverlay').classList.add('open');">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

{{-- ── Mobile Drawer ──────────────────────────────────────────── --}}
<div class="sf-drawer-overlay" id="drawerOverlay" onclick="document.getElementById('mobileDrawer').classList.remove('open'); this.classList.remove('open');"></div>
<div class="sf-mobile-drawer" id="mobileDrawer">
    <button class="close-drawer" onclick="document.getElementById('mobileDrawer').classList.remove('open'); document.getElementById('drawerOverlay').classList.remove('open');"><i class="bi bi-x"></i></button>
    <div style="margin-top: 40px;">
        {{-- Drawer Logo --}}
        @if($ss->get('theme.logo'))
            <a href="{{ route('home') }}" style="display:block;margin-bottom:20px;">
                <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" style="max-height:36px;">
            </a>
        @elseif(file_exists(public_path('images/logo.png')))
            <a href="{{ route('home') }}" style="display:block;margin-bottom:20px;">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height:36px;">
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
            <form action="{{ route('logout') }}" method="post" style="margin-top: 16px;">@csrf
                <button type="submit" style="background:none;border:none;color:var(--color-error);font-size:14px;padding:0;">Logout</button>
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
                    <a href="{{ route('home') }}" style="display:inline-block;margin-bottom:12px;">
                        <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" style="max-height:60px;">
                    </a>
                @elseif(file_exists(public_path('images/logo.png')))
                    <a href="{{ route('home') }}" style="display:inline-block;margin-bottom:12px;">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height:60px;">
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
            <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 24px;">
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
@stack('scripts')
</body>
</html>
