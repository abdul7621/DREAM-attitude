<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? $storeSettings['store_name'] ?? config('app.name') }}</title>
    <meta name="description" content="{{ $pageDescription ?? $storeSettings['meta_description'] ?? '' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @stack('meta')
    @include('partials.tracking-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/storefront.css') }}?v={{ filemtime(public_path('css/storefront.css')) }}" rel="stylesheet">
    
    @php $ss = app(\App\Services\SettingsService::class); @endphp
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
            on(event, callback) { document.addEventListener(event, (e) => callback(e.detail)); }
        };
    </script>
    <script src="{{ asset('js/store.js') }}"></script>
</head>
<body>
@include('partials.tracking-body')

{{-- ── Announcement Bar ────────────────────────────────── --}}
@php $announcementActive = $ss->get('theme.announcement_active', false); @endphp
@if($announcementActive && $ss->get('theme.announcement_text'))
<div class="sf-announce-bar" id="announcementBar">
    {{ $ss->get('theme.announcement_text') }}
    <button type="button" class="btn-dismiss" onclick="document.getElementById('announcementBar').style.display='none'; sessionStorage.setItem('announce_closed','1');"><i class="bi bi-x"></i></button>
</div>
<script>
    if(sessionStorage.getItem('announce_closed') === '1') {
        document.getElementById('announcementBar').style.display = 'none';
    }
</script>
@endif

{{-- ── Header ──────────────────────────────────────────── --}}
<header class="sf-header">
    @if($ss->get('theme.logo'))
        <a class="logo" href="{{ route('home') }}">
            <img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" style="max-height: 40px;">
        </a>
    @else
        <a class="logo" href="{{ route('home') }}">{{ config('app.name') }}</a>
    @endif
    
    <nav class="sf-header-nav nav-links" id="desktopNav">
        @if (isset($globalMenus['header']) && $globalMenus['header']->parentItems->isNotEmpty())
            @foreach ($globalMenus['header']->parentItems as $item)
                <a href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a>
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
        <a href="{{ route('cart.index') }}" class="cart-icon position-relative ms-3">
            <i class="bi bi-bag"></i>
            @if (($cartCount ?? 0) > 0)
                <span class="sf-cart-badge">{{ $cartCount }}</span>
            @endif
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
        @if (isset($globalMenus['header']) && $globalMenus['header']->parentItems->isNotEmpty())
            @foreach ($globalMenus['header']->parentItems as $item)
                <a href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a>
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

{{-- ── Footer ──────────────────────────────────────────── --}}
<footer class="sf-footer">
    <div class="sf-container">
        <div class="sf-footer-grid">
            <div>
                <div class="brand">{{ config('app.name') }}</div>
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
                <h4>Shop</h4>
                <div class="links">
                    <a href="{{ route('home') }}">Home</a>
                    <a href="{{ route('search') }}">All Products</a>
                    <a href="{{ route('cart.index') }}">Cart</a>
                </div>
            </div>
            
            <div>
                <h4>Policies</h4>
                <div class="links">
                    @if(isset($globalMenus['footer']) && $globalMenus['footer']->parentItems->isNotEmpty())
                        @foreach($globalMenus['footer']->parentItems as $item)
                            <a href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a>
                        @endforeach
                    @else
                        @foreach (['privacy-policy','return-policy','shipping-policy','terms-conditions'] as $slug)
                            @php $pg = \App\Models\Page::where('slug', $slug)->where('is_active', true)->first(); @endphp
                            @if ($pg)
                                <a href="{{ route('page.show', $pg) }}">{{ $pg->title }}</a>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
            
            <div>
                <h4>Contact</h4>
                <div class="links">
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

<x-toast />

{{-- Wishlist Heart System --}}
<script>
(function() {
    const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Hydrate hearts from server
    if (isLoggedIn) {
        fetch('{{ route("account.api.wishlist-ids") }}')
            .then(r => r.json())
            .then(ids => {
                document.querySelectorAll('.wishlist-heart').forEach(btn => {
                    const pid = parseInt(btn.dataset.productId);
                    if (ids.includes(pid)) {
                        btn.classList.add('active');
                        btn.querySelector('i').className = 'bi bi-heart-fill';
                        btn.dataset.wishlisted = '1';
                    }
                });
            }).catch(() => {});
    }

    // Toggle click handler
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.wishlist-heart');
        if (!btn) return;
        e.preventDefault();

        if (!isLoggedIn) {
            window.location.href = '{{ route("login") }}?redirect=' + encodeURIComponent(window.location.href);
            return;
        }

        const productId = btn.dataset.productId;
        const icon = btn.querySelector('i');

        fetch('{{ route("account.wishlist.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.wishlisted) {
                btn.classList.add('active');
                icon.className = 'bi bi-heart-fill';
                btn.dataset.wishlisted = '1';
            } else {
                btn.classList.remove('active');
                icon.className = 'bi bi-heart';
                btn.dataset.wishlisted = '0';
            }
            if (window.Store) Store.emit('toast', { type: 'success', message: data.message });
        })
        .catch(() => {
            if (window.Store) Store.emit('toast', { type: 'error', message: 'Could not update wishlist.' });
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
