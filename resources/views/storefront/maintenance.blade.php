<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; text-align: center; font-family: system-ui, sans-serif; }</style>
</head>
<body>
    <div>
        <h1 class="display-4 fw-bold text-dark mb-3">Be Right Back.</h1>
        <p class="text-muted fs-5 mb-4">We are currently updating our store to serve you better.<br>Please check back shortly.</p>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Go to Admin Dashboard</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="text-muted small">Admin Login</a>
        @endauth
    </div>
</body>
</html>
