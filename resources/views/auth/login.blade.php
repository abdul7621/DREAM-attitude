@extends('layouts.storefront')

@section('title', 'Login')

@section('content')
<section class="sf-section">
    <div class="sf-container">
        <div class="sf-auth-card">
            @php $ss = app(\App\Services\SettingsService::class); @endphp
            @if($ss->get('theme.logo'))
                <div class="logo"><a href="{{ route('home') }}"><img src="{{ asset('storage/' . $ss->get('theme.logo')) }}" alt="{{ config('app.name') }}" style="max-height:48px;"></a></div>
            @elseif(file_exists(public_path('images/logo.png')))
                <div class="logo"><a href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" style="max-height:48px;"></a></div>
            @else
                <div class="logo">{{ config('app.name') }}</div>
            @endif
            <h1 style="color: var(--color-text-primary); font-size: 18px; text-align: center; margin-bottom: 24px; text-transform: uppercase;">Login</h1>
            @if ($errors->any())
                <div style="background: rgba(197, 48, 48, 0.1); border: 1px solid var(--color-error); color: var(--color-error); padding: 12px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 24px;">{{ $errors->first() }}</div>
            @endif
            <form method="post" action="{{ route('login') }}">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label class="sf-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="sf-input" required autofocus>
                </div>
                <div style="margin-bottom: 24px;">
                    <label class="sf-label">Password</label>
                    <input type="password" name="password" class="sf-input" required>
                </div>
                <div style="margin-bottom: 24px; display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="remember" id="r1" value="1" style="accent-color: var(--color-gold);">
                    <label for="r1" style="color: var(--color-text-secondary); font-size: 12px;">Remember me</label>
                </div>
                <button type="submit" class="sf-btn-primary">Sign in</button>
            </form>
            @if(Route::has('register'))
            <div class="sf-auth-link">
                Don't have an account? <a href="{{ route('register') }}">Sign up</a>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
