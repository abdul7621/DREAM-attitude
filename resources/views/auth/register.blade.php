@extends('layouts.storefront')
@section('title', 'Create Account')
@section('content')
<section class="sf-section">
<div class="sf-container">
<div class="sf-auth-card">
  <div class="logo">{{ config('app.name') }}</div>
  <h1 style="color:white;font-size:18px;text-align:center;
    margin-bottom:24px;text-transform:uppercase;">
    Create Account</h1>
  @if($errors->any())
    <div style="background:rgba(197,48,48,0.1);
      border:1px solid var(--color-error);
      color:var(--color-error);padding:12px;
      border-radius:var(--radius-sm);font-size:13px;
      margin-bottom:24px;">
      {{ $errors->first() }}</div>
  @endif
  <form method="post" action="{{ route('register') }}">
    @csrf
    <div style="margin-bottom:16px;">
      <label class="sf-label">Full Name</label>
      <input type="text" name="name" 
        value="{{ old('name') }}" 
        class="sf-input" required autofocus>
    </div>
    <div style="margin-bottom:16px;">
      <label class="sf-label">Email</label>
      <input type="email" name="email" 
        value="{{ old('email') }}" 
        class="sf-input" required>
    </div>
    <div style="margin-bottom:16px;">
      <label class="sf-label">Phone Number</label>
      <input type="tel" name="phone" 
        value="{{ old('phone') }}" 
        class="sf-input" required 
        maxlength="10" inputmode="numeric">
    </div>
    <div style="margin-bottom:16px;">
      <label class="sf-label">Password</label>
      <input type="password" name="password" 
        class="sf-input" required>
    </div>
    <div style="margin-bottom:24px;">
      <label class="sf-label">Confirm Password</label>
      <input type="password" 
        name="password_confirmation" 
        class="sf-input" required>
    </div>
    <button type="submit" class="sf-btn-primary">
      Create Account</button>
  </form>
  <div class="sf-auth-link">
    Already have an account? 
    <a href="{{ route('login') }}">Sign in</a>
  </div>
</div>
</div>
</section>
@endsection
