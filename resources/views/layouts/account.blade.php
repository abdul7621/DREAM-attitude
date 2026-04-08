@extends('layouts.storefront')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        {{-- ── Sidebar (Desktop: left column, Mobile: horizontal tabs) ── --}}
        <div class="col-lg-3">
            <div class="d-none d-lg-block">
                <div class="bg-white rounded shadow-sm p-3">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-person-circle me-1"></i> My Account</h6>
                    <nav class="nav flex-column gap-1">
                        <a href="{{ route('account.dashboard') }}"
                           class="nav-link px-3 py-2 rounded {{ request()->routeIs('account.dashboard') ? 'active bg-primary text-white' : 'text-dark' }}">
                            <i class="bi bi-grid me-2"></i>Dashboard
                        </a>
                        <a href="{{ route('account.orders') }}"
                           class="nav-link px-3 py-2 rounded {{ request()->routeIs('account.orders') && !request()->routeIs('account.orders.show') ? 'active bg-primary text-white' : 'text-dark' }}">
                            <i class="bi bi-receipt me-2"></i>Orders
                        </a>
                        <a href="{{ route('account.wishlist') }}"
                           class="nav-link px-3 py-2 rounded {{ request()->routeIs('account.wishlist') ? 'active bg-primary text-white' : 'text-dark' }}">
                            <i class="bi bi-heart me-2"></i>Wishlist
                        </a>
                        <a href="{{ route('account.addresses.index') }}"
                           class="nav-link px-3 py-2 rounded {{ request()->routeIs('account.addresses.*') ? 'active bg-primary text-white' : 'text-dark' }}">
                            <i class="bi bi-geo-alt me-2"></i>Addresses
                        </a>
                        <a href="{{ route('account.returns') }}"
                           class="nav-link px-3 py-2 rounded {{ request()->routeIs('account.returns') ? 'active bg-primary text-white' : 'text-dark' }}">
                            <i class="bi bi-arrow-return-left me-2"></i>Returns
                        </a>
                        <a href="{{ route('account.profile') }}"
                           class="nav-link px-3 py-2 rounded {{ request()->routeIs('account.profile') ? 'active bg-primary text-white' : 'text-dark' }}">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                        <hr class="my-2">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="nav-link px-3 py-2 rounded text-danger border-0 bg-transparent w-100 text-start">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </nav>
                </div>
            </div>
            {{-- Mobile horizontal tabs --}}
            <div class="d-lg-none">
                <div class="bg-white rounded shadow-sm p-2 overflow-auto">
                    <div class="d-flex gap-2 flex-nowrap" style="min-width: max-content;">
                        <a href="{{ route('account.dashboard') }}" class="btn btn-sm {{ request()->routeIs('account.dashboard') ? 'btn-primary' : 'btn-outline-secondary' }}">Dashboard</a>
                        <a href="{{ route('account.orders') }}" class="btn btn-sm {{ request()->routeIs('account.orders') && !request()->routeIs('account.orders.show') ? 'btn-primary' : 'btn-outline-secondary' }}">Orders</a>
                        <a href="{{ route('account.wishlist') }}" class="btn btn-sm {{ request()->routeIs('account.wishlist') ? 'btn-primary' : 'btn-outline-secondary' }}">Wishlist</a>
                        <a href="{{ route('account.addresses.index') }}" class="btn btn-sm {{ request()->routeIs('account.addresses.*') ? 'btn-primary' : 'btn-outline-secondary' }}">Addresses</a>
                        <a href="{{ route('account.returns') }}" class="btn btn-sm {{ request()->routeIs('account.returns') ? 'btn-primary' : 'btn-outline-secondary' }}">Returns</a>
                        <a href="{{ route('account.profile') }}" class="btn btn-sm {{ request()->routeIs('account.profile') ? 'btn-primary' : 'btn-outline-secondary' }}">Profile</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Main content area ── --}}
        <div class="col-lg-9">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('account-content')
        </div>
    </div>
</div>
@endsection
