<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">⚡ Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.orders.index') }}">Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.products.index') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.categories.index') }}">Categories</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.coupons.index') }}">Coupons</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.shipping-rules.index') }}">Shipping</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.returns.index') }}">Returns</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.reviews.index') }}">Reviews</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.customers.index') }}">Customers</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.pages.index') }}">Pages</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.redirects.index') }}">Redirects</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.import.index') }}">Import</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.settings.edit') }}">Settings</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}" target="_blank">Storefront ↗</a></li>
                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="post" class="d-inline">@csrf
                        <button type="submit" class="btn btn-sm btn-outline-light my-1">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>

