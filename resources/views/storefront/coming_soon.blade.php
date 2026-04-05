<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coming Soon — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background: #111827; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; text-align: center; font-family: system-ui, sans-serif; }</style>
</head>
<body>
    <div>
        <h1 class="display-3 fw-bolder mb-3">Something Big is Coming.</h1>
        <p class="text-secondary fs-5 mb-4">We are building an amazing experience for you.<br>Stay tuned!</p>
        @auth
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Go to Admin Dashboard</a>
            @endif
        @else
            <a href="{{ route('login') }}" class="text-secondary small">Admin Login</a>
        @endauth
    </div>
</body>
</html>
