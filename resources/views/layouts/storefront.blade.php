<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @stack('meta')
    @include('partials.tracking-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/storefront.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
@include('partials.tracking-body')

{{-- ── Header ──────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg sf-header">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list text-white" style="font-size:1.5rem;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            {{-- Categories --}}
            @php $navCategories = \App\Models\Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->take(6)->get(); @endphp
            @if ($navCategories->isNotEmpty())
                <ul class="navbar-nav me-auto gap-1">
                    @foreach ($navCategories as $cat)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('category.show', $cat) }}">{{ $cat->name }}</a>
                        </li>
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
                    <a href="{{ route('account.orders') }}" class="nav-link d-none d-lg-inline"><i class="bi bi-person-circle"></i></a>
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
<main>
    @if (session('status'))
        <div class="container mt-3"><div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i> {{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div></div>
    @endif
    @if ($errors->any())
        <div class="container mt-3"><div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
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
                    @foreach (['privacy-policy','return-policy','shipping-policy','terms-conditions'] as $slug)
                        @php $pg = \App\Models\Page::where('slug', $slug)->where('is_active', true)->first(); @endphp
                        @if ($pg)
                            <li><a href="{{ route('page.show', $pg) }}">{{ $pg->title }}</a></li>
                        @endif
                    @endforeach
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
