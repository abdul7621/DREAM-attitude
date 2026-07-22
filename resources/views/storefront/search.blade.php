@extends('layouts.storefront')

@section('title', $q ? __('Search: :q', ['q' => $q]) : __('Shop All Products'))

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')

{{-- Page Header --}}
<div class="sf-page-header" style="background: linear-gradient(180deg, #FAF5E8 0%, #FFFFFF 100%); padding: 36px 0 24px; border-bottom: 1px solid #EAEAEA; text-align: center;">
    <div class="sf-container">
        {{-- Breadcrumb --}}
        <nav class="sf-breadcrumb" style="display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 12px; color: #6B7280; margin-bottom: 10px;">
            <a href="{{ route('home') }}" style="color: #374151; text-decoration: none;">Home</a> 
            <span style="color: #D1D5DB;">/</span> 
            <span style="color: var(--color-gold); font-weight: 600;">{{ $q ? 'Search Results' : 'All Products' }}</span>
        </nav>

        <h1 style="font-family: 'Playfair Display', serif; font-size: 30px; font-weight: 700; color: #111827; margin: 0 0 16px; text-transform: capitalize; letter-spacing: 0.5px;">
            {{ $q ? __('Search Results for ":q"', ['q' => $q]) : __('Shop All Beauty Products') }}
        </h1>

        {{-- Search Input Form --}}
        <form action="{{ route('search') }}" method="get" style="max-width: 620px; margin: 0 auto; position: relative;">
            <div style="display: flex; background: #FFFFFF; border: 2px solid var(--color-gold); border-radius: 30px; padding: 4px 6px 4px 20px; box-shadow: 0 4px 16px rgba(201, 168, 76, 0.12); align-items: center;">
                <i class="bi bi-search" style="color: var(--color-gold); font-size: 16px; margin-right: 10px;"></i>
                <input type="search" name="q" value="{{ $rawQ ?? $q }}" style="flex: 1; border: none; background: transparent; padding: 10px 0; font-size: 14px; outline: none; color: #111827;" placeholder="Search products, fragrances, attars..." autocomplete="off" id="site-search-input">
                <button type="submit" style="background: #111827; color: #FFFFFF; border: none; padding: 10px 24px; border-radius: 24px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background 0.2s;">
                    Search
                </button>
            </div>
        </form>

        {{-- Popular Search Tags --}}
        @if(isset($popularTags) && $popularTags->isNotEmpty())
            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-top: 14px; align-items: center;">
                <span style="font-size: 11px; color: #9CA3AF; text-transform: uppercase; font-weight: 600;">Popular:</span>
                @foreach($popularTags as $cat)
                    <a href="{{ route('search', ['q' => $cat->name]) }}" style="font-size: 11px; color: #4B5563; background: #FFFFFF; border: 1px solid #EAEAEA; padding: 4px 10px; border-radius: 12px; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--color-gold)';this.style.color='var(--color-gold)';" onmouseout="this.style.borderColor='#EAEAEA';this.style.color='#4B5563';">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<section class="sf-section" style="padding-top: 28px; padding-bottom: 60px;">
    <div class="sf-container">
        @if ($products->isEmpty())
            {{-- Did You Mean suggestion --}}
            @if(isset($suggestion) && $suggestion)
                <div style="max-width: 620px; margin: 20px auto; background: #FEF9C3; border: 1.5px solid #FDE047; padding: 16px 20px; border-radius: 16px; text-align: center; color: #713F12; font-size: 14px; font-weight: 600; box-shadow: 0 4px 12px rgba(254, 240, 138, 0.3);">
                    <i class="bi bi-lightbulb-fill" style="color: #CA8A04; margin-right: 6px; font-size: 16px;"></i>
                    We couldn't find results for "{{ $rawQ ?? $q }}". Did you mean: 
                    <a href="{{ route('search', ['q' => $suggestion]) }}" style="color: #A16207; text-decoration: underline; font-weight: 700; font-size: 15px;">{{ $suggestion }}</a>?
                </div>
            @endif

            {{-- Branded Empty State --}}
            <div class="sf-empty-state" style="padding: 48px 20px; background: #FFFFFF; border-radius: 20px; border: 1px solid #EAEAEA; max-width: 620px; margin: 30px auto; text-align: center; box-shadow: 0 8px 24px rgba(0,0,0,0.02);">
                <div class="sf-empty-icon" style="width: 72px; height: 72px; background: #FAF5E8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="bi bi-search" style="color: var(--color-gold); font-size: 28px;"></i>
                </div>
                <h3 class="sf-empty-title" style="font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 8px;">No matching products found</h3>
                <p class="sf-empty-sub" style="font-size: 13px; color: #6B7280; margin: 0 0 24px; line-height: 1.5;">
                    We couldn't find matches for <strong>"{{ $rawQ ?? $q }}"</strong>. Please check spelling or explore popular categories.
                </p>
                <div style="display:flex; justify-content:center; gap:12px;">
                    <a href="{{ route('home') }}" class="btn-add btn-gradient" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 28px; font-size: 12px; font-weight: 700; border-radius: 24px; background: #111827; color: #fff; text-decoration: none; transition: background 0.2s;">
                        <i class="bi bi-arrow-left"></i> Return to Home
                    </a>
                    <a href="https://wa.me/917096206785?text={{ urlencode('Hi Dream Attitude Team, I was looking for: '.($rawQ ?? $q)) }}" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 24px; font-size: 12px; font-weight: 700; border-radius: 24px; background: #25D366; color: #fff; text-decoration: none;">
                        <i class="bi bi-whatsapp"></i> Ask on WhatsApp
                    </a>
                </div>
            </div>

            {{-- Suggested Bestsellers Fallback --}}
            @if(isset($bestsellers) && $bestsellers->isNotEmpty())
                <div style="margin-top: 50px;">
                    <div style="text-align: center; margin-bottom: 28px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--color-gold); text-transform: uppercase; letter-spacing: 2px;">Try These Instead</span>
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 24px; margin: 4px 0 0; color: #111827;">Popular Bestsellers</h3>
                    </div>
                    <div class="sf-product-grid">
                        @foreach ($bestsellers as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            {{-- Toolbar --}}
            <div class="sf-shop-toolbar" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;padding-bottom:16px;border-bottom:1px solid var(--color-border);">
                <div style="display:flex;align-items:center;gap:16px;">
                    <span style="font-size:13px;color:#6B7280;text-transform:uppercase;letter-spacing:0.5px;font-weight:500;">Showing {{ $products->total() }} results</span>
                </div>
                
                <div style="display:flex;align-items:center;gap:12px;">
                    <select onchange="window.location=this.value" class="sf-input" style="width:auto;min-width:200px;padding:8px 14px;font-size:13px;background:#FFFFFF;border:1.5px solid #EAEAEA;border-radius:20px;cursor:pointer;outline:none;">
                        @php
                            $currentUrl = request()->fullUrlWithQuery(['sort' => '']);
                            $currentUrl = str_replace('sort=&', '', $currentUrl);
                            $currentUrl = str_replace('?sort=', '?', $currentUrl);
                            $separator = str_contains($currentUrl, '?') ? '&' : '?';
                        @endphp
                        <option value="{{ $currentUrl.$separator }}sort=newest" {{ request('sort','newest')=='newest'?'selected':'' }}>Sort by latest</option>
                        <option value="{{ $currentUrl.$separator }}sort=price_asc" {{ request('sort')=='price_asc'?'selected':'' }}>Sort by price: low to high</option>
                        <option value="{{ $currentUrl.$separator }}sort=price_desc" {{ request('sort')=='price_desc'?'selected':'' }}>Sort by price: high to low</option>
                        <option value="{{ $currentUrl.$separator }}sort=bestseller" {{ request('sort')=='bestseller'?'selected':'' }}>Sort by popularity</option>
                    </select>
                </div>
            </div>

            <div class="sf-product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            
            <div style="margin-top: 40px;">
                {{ $products->links('vendor.pagination.storefront') }}
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script defer src="{{ asset('js/smart-search.js') }}"></script>
<script>
if (window.Store && @json($q)) {
    Store.track('search', {
        query: @json($q),
        results: {{ $products->total() ?? 0 }}
    });
}
</script>
@endpush
