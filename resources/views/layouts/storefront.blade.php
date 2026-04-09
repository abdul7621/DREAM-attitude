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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/storefront.css') }}" rel="stylesheet">
    
    {{-- ── Dynamic Theme Engine ──────────────────────────────────── --}}
    @php $ss = app(\App\Services\SettingsService::class); @endphp
    <style>
        :root {
            --brand-primary: {{ $ss->get('theme.primary_color', '#000000') }};
            --brand-secondary: {{ $ss->get('theme.secondary_color', '#6c757d') }};
            --brand-radius: {{ $ss->get('theme.border_radius', '0.375rem') }};
            --brand-font: {!! $ss->get('theme.font_family', 'Inter, sans-serif') !!};
        }
        
        body, h1, h2, h3, h4, h5, h6, .nav-link, .btn {
            font-family: var(--brand-font) !important;
        }
        
        .btn-primary { 
            background-color: var(--brand-primary) !important; 
            border-color: var(--brand-primary) !important; 
            color: #fff !important;
            border-radius: var(--brand-radius);
        }
        @if($ss->get('theme.button_style') === 'outline')
        .btn-primary {
            background-color: transparent !important;
            color: var(--brand-primary) !important;
        }
        .btn-primary:hover {
            background-color: var(--brand-primary) !important;
            color: #fff !important;
        }
        @endif

        .badge.bg-primary { background-color: var(--brand-primary) !important; }
        .text-primary { color: var(--brand-primary) !important; }
        .bg-primary { background-color: var(--brand-primary) !important; }
        .sf-header { background-color: var(--brand-primary) !important; }
        
        .card { 
            border-radius: var(--brand-radius) !important; 
            @if($ss->get('theme.card_shadow') === 'shadow-sm')
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important; border: none !important;
            @elseif($ss->get('theme.card_shadow') === 'shadow')
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.125) !important; border: none !important;
            @endif
        }
    </style>
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

            emit(event, data) {
                document.dispatchEvent(new CustomEvent(event, { detail: data }));
            },
            on(event, callback) {
                document.addEventListener(event, (e) => callback(e.detail));
            }
        };
    </script>
    <script src="{{ asset('js/store.js') }}"></script>
</head>
<body>
@include('partials.tracking-body')

{{-- ── Announcement Bar ────────────────────────────────── --}}
@php $announcementActive = $ss->get('theme.announcement_active', false); @endphp
@if($announcementActive && $ss->get('theme.announcement_text'))
<div class="sf-announcement-bar text-center py-2 small fw-medium position-relative" 
     style="background: var(--brand-primary); color: #fff; z-index: 1040;" id="announcementBar">
    {{ $ss->get('theme.announcement_text') }}
    <button type="button" class="btn-close btn-close-white position-absolute end-0 top-50 translate-middle-y me-3" style="font-size:.6rem; padding: 0.5rem;" 
            onclick="document.getElementById('announcementBar').remove();"></button>
</div>
@endif

{{-- ── Header ──────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg sf-header">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list text-white" style="font-size:1.5rem;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            {{-- Dynamic Header Menu --}}
            @if (isset($globalMenus['header']) && $globalMenus['header']->parentItems->isNotEmpty())
                <ul class="navbar-nav me-auto gap-1">
                    @foreach ($globalMenus['header']->parentItems as $item)
                        @if($item->children->count() > 0)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="{{ $item->link ?: '#' }}" role="button" data-bs-toggle="dropdown" aria-expanded="false" @if($item->is_external) target="_blank" @endif>
                                    {{ $item->label }}
                                </a>
                                <ul class="dropdown-menu border-0 shadow-sm">
                                    @foreach($item->children as $child)
                                        <li><a class="dropdown-item" href="{{ $child->link ?: '#' }}" @if($child->is_external) target="_blank" @endif>{{ $child->label }}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif

            <div class="d-flex align-items-center gap-3 ms-auto mt-2 mt-lg-0">
                {{-- Search --}}
                <form action="{{ route('search') }}" method="get" role="search">
                    <input type="search" name="q" class="search-box" placeholder="Search products…" aria-label="Search">
                </form>
                {{-- Cart --}}
                <a href="{{ route('cart.index') }}" class="btn-cart">
                    <i class="bi bi-bag"></i>
                    @if (($cartCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                {{-- Account --}}
                @auth
                    <a href="{{ route('account.dashboard') }}" class="nav-link d-none d-lg-inline"><i class="bi bi-person-circle"></i></a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link d-none d-lg-inline" style="font-size:.78rem;">Admin</a>
                    @endif
                    <form action="{{ route('logout') }}" method="post" class="d-inline">@csrf
                        <button type="submit" class="btn btn-sm btn-outline-light" style="font-size:.75rem;">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link"><i class="bi bi-person"></i> Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- ── Main Content ────────────────────────────────────── --}}
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
                // Ensure single-fire execution logic via window variable state
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
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h6>{{ config('app.name') }}</h6>
                <p class="small" style="max-width:280px;">Premium quality products delivered to your doorstep. 100% authentic, fast delivery across India.</p>
                <div class="mt-3">
                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Shop</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('search') }}">All Products</a></li>
                    <li><a href="{{ route('cart.index') }}">Cart</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Policies</h6>
                <ul class="footer-links">
                    @if(isset($globalMenus['footer']) && $globalMenus['footer']->parentItems->isNotEmpty())
                        @foreach($globalMenus['footer']->parentItems as $item)
                            <li><a href="{{ $item->link ?: '#' }}" @if($item->is_external) target="_blank" @endif>{{ $item->label }}</a></li>
                        @endforeach
                    @else
                        @foreach (['privacy-policy','return-policy','shipping-policy','terms-conditions'] as $slug)
                            @php $pg = \App\Models\Page::where('slug', $slug)->where('is_active', true)->first(); @endphp
                            @if ($pg)
                                <li><a href="{{ route('page.show', $pg) }}">{{ $pg->title }}</a></li>
                            @endif
                        @endforeach
                    @endif
                </ul>
            </div>
            <div class="col-lg-3">
                <h6>Contact</h6>
                <ul class="footer-links">
                    @php $ss = app(\App\Services\SettingsService::class); @endphp
                    @if ($ss->get('store.email'))
                        <li><i class="bi bi-envelope me-1"></i> <a href="mailto:{{ $ss->get('store.email') }}">{{ $ss->get('store.email') }}</a></li>
                    @endif
                    @if ($ss->get('store.phone'))
                        <li><i class="bi bi-telephone me-1"></i> <a href="tel:{{ $ss->get('store.phone') }}">{{ $ss->get('store.phone') }}</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom text-center">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>

<x-toast />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
                        btn.querySelector('i').className = 'bi bi-heart-fill text-danger';
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
                icon.className = 'bi bi-heart-fill text-danger';
                btn.dataset.wishlisted = '1';
            } else {
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

