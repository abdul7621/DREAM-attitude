@extends('layouts.storefront')

@section('title', 'My Account')

@section('content')
<section class="sf-section" style="padding: 40px 0; background: var(--color-bg-primary);">
<div class="sf-container">
    @if (session('success'))
        <div style="background:rgba(25,135,84,0.1); border:1px solid var(--color-success); color:var(--color-success); padding:12px; border-radius:var(--radius-sm); font-size:13px; margin-bottom:24px;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background:rgba(197,48,48,0.1); border:1px solid var(--color-error); color:var(--color-error); padding:12px; border-radius:var(--radius-sm); font-size:13px; margin-bottom:24px;">
            {{ session('error') }}
        </div>
    @endif
    @if (session('status'))
        <div style="background:rgba(25,135,84,0.1); border:1px solid var(--color-success); color:var(--color-success); padding:12px; border-radius:var(--radius-sm); font-size:13px; margin-bottom:24px;">
            {{ session('status') }}
        </div>
    @endif

    <div class="sf-account-layout">
      <div class="sf-sidebar">
        <a href="{{ route('account.dashboard') }}" 
          class="sf-sidebar-link 
          {{ request()->routeIs('account.dashboard') 
             ? 'active' : '' }}">
          Dashboard</a>
        <a href="{{ route('account.orders') }}"
          class="sf-sidebar-link
          {{ request()->routeIs('account.orders*') 
             ? 'active' : '' }}">
          My Orders</a>
        <a href="{{ route('account.profile') }}"
          class="sf-sidebar-link
          {{ request()->routeIs('account.profile*') 
             ? 'active' : '' }}">
          Profile</a>
        <a href="{{ route('account.addresses.index') }}"
          class="sf-sidebar-link
          {{ request()->routeIs('account.addresses*') 
             ? 'active' : '' }}">
          Addresses</a>
        <a href="{{ route('account.wishlist') }}"
          class="sf-sidebar-link
          {{ request()->routeIs('account.wishlist*') 
             ? 'active' : '' }}">
          Wishlist</a>
        <form method="POST" 
          action="{{ route('logout') }}" 
          style="margin-top:auto;padding:12px 20px;">
          @csrf
          <button type="submit" style="background:none;
            border:none;color:var(--color-error);
            font-size:13px;cursor:pointer;
            text-transform:uppercase;letter-spacing:0.5px;">
            Logout</button>
        </form>
      </div>
      <div class="sf-account-content">
          @yield('account-content')
      </div>
    </div>
</div>
</section>
@endsection
