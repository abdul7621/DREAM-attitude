@extends('layouts.storefront')

@section('title', 'Reset Password')

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
            <h1 style="color: var(--color-text-primary); font-size: 18px; text-align: center; margin-bottom: 8px; text-transform: uppercase;">Reset Password</h1>
            <p style="color: var(--color-text-secondary); font-size: 13px; text-align: center; margin-bottom: 24px;">Enter your email address and we will send you a password reset link.</p>
            @if (session('status'))
                <div style="background: rgba(72, 187, 120, 0.1); border: 1px solid #48bb78; color: #48bb78; padding: 12px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 24px;">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div style="background: rgba(197, 48, 48, 0.1); border: 1px solid var(--color-error); color: var(--color-error); padding: 12px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 24px;">{{ $errors->first() }}</div>
            @endif
            <form method="post" action="{{ route('password.email') }}">
                @csrf
                <div style="margin-bottom: 24px;">
                    <label class="sf-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="sf-input" required autofocus>
                </div>
                <button type="submit" class="sf-btn-primary">Send Reset Link</button>
            </form>
            <div class="sf-auth-link">
                <a href="{{ route('login') }}">Back to Login</a>
            </div>
        </div>
    </div>
</section>
@endsection
