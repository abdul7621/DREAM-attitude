<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Influencer Partner Login — {{ config('app.name', 'Dream Attitude') }}</title>
    
    @php $ss = app(\App\Services\SettingsService::class); @endphp
    @if($ss->get('theme.favicon'))
        <link rel="icon" href="{{ asset('storage/' . $ss->get('theme.favicon')) }}">
    @endif

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg-dark: #0A0A0A;
            --color-gold: #C9963A;
            --color-gold-light: #E8C97A;
            --color-border-gold: rgba(201, 150, 58, 0.35);
        }

        body {
            background: #0A0A0A;
            color: #FFF;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            margin: 0;
            background-image: radial-gradient(circle at top center, rgba(201, 150, 58, 0.08) 0%, transparent 60%);
        }

        .login-card {
            background: #141414;
            border: 1px solid var(--color-border-gold);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        }

        .form-control {
            background: #0A0A0A !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #FFF !important;
            padding: 12px 14px;
            font-size: 0.95rem;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--color-gold) !important;
            box-shadow: 0 0 0 3px rgba(201, 150, 58, 0.25) !important;
        }

        .btn-gold {
            background: linear-gradient(135deg, #D4AF37, #AA7C11);
            color: #0A0A0A;
            font-weight: 700;
            border: none;
            padding: 13px;
            border-radius: 8px;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            width: 100%;
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #E8C97A, #C9963A);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(201, 150, 58, 0.4);
            color: #000;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <a href="{{ route('home') }}" class="text-decoration-none">
            <h1 class="h3 fw-bold text-white mb-1" style="font-family:'Playfair Display', serif; letter-spacing: 1px;">
                {{ config('app.name', 'DREAM ATTITUDE') }}
            </h1>
        </a>
        <div class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1 mt-1 font-monospace" style="letter-spacing: 1px;">
            <i class="bi bi-stars me-1"></i> INFLUENCER PARTNER PORTAL
        </div>
        <p class="text-muted small mt-3 mb-0">Sign in with your partner credentials to track your referral sales & commission earnings.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-white small p-2 px-3 mb-3 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label text-muted small fw-semibold">Partner Login Email</label>
            <div class="input-group">
                <span class="input-group-text bg-black border-secondary border-opacity-25 text-muted"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="influencer@example.com" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between">
                <label class="form-label text-muted small fw-semibold">Password</label>
                <a href="{{ route('password.request') }}" class="text-warning text-decoration-none small" style="font-size: 0.78rem;">Forgot?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-black border-secondary border-opacity-25 text-muted"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-gold mb-3">
            Sign In to Partner Portal <i class="bi bi-arrow-right ms-1"></i>
        </button>

        <div class="text-center">
            <a href="{{ route('home') }}" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Return to Main Store
            </a>
        </div>
    </form>
</div>

</body>
</html>
