@extends('layouts.storefront')

@section('title', $originalQuery ? __('Search: :q', ['q' => $originalQuery]) : __('All Products'))

@section('content')
<section class="sf-section">
    <div class="sf-container">
        <h1 class="h3 mb-4">{{ $originalQuery ? __('Search Results') : __('All Products') }}</h1>
        
        <form action="{{ route('search') }}" method="get" class="mb-4 position-relative">
            <div class="input-group input-group-lg">
                <input type="search" name="q" value="{{ $originalQuery }}" class="form-control rounded-start" placeholder="{{ __('Search products…') }}" autocomplete="off" id="site-search-input">
                <button class="btn btn-primary px-4" type="submit"><i class="bi bi-search"></i> {{ __('Search') }}</button>
            </div>
            {{-- Autocomplete results container --}}
            <div id="search-autocomplete-results" class="position-absolute w-100 bg-white shadow rounded mt-1 border" style="display: none; z-index: 1000; max-height: 400px; overflow-y: auto;"></div>
        </form>

        {{-- Spelling suggestion banner --}}
        @if (!empty($spellingSuggestion))
            <div class="alert alert-info py-2 px-3 mb-4 rounded d-flex align-items-center gap-2" style="font-size: 14px; border: none; background: rgba(37,99,235,0.08); color: var(--color-gold);">
                <i class="bi bi-lightbulb-fill text-warning"></i>
                <span>{{ __('Did you mean:') }} 
                    <a href="{{ route('search', ['q' => $spellingSuggestion]) }}" class="fw-bold text-decoration-underline" style="color: var(--color-gold);">{{ $spellingSuggestion }}</a>?
                </span>
            </div>
        @endif

        @if ($products->isEmpty())
            <div class="text-center py-5 px-4 mb-5 bg-white rounded shadow-sm border">
                <div class="mb-4 text-muted" style="font-size: 4rem;">
                    <i class="bi bi-search-heart"></i>
                </div>
                <h2 class="h4 mb-2 fw-semibold">{{ __('No results found for ":query"', ['query' => $originalQuery]) }}</h2>
                @if(!empty($mappedQuery))
                    <p class="text-muted small mb-4">(Mapped synonym: <em>{{ $mappedQuery }}</em>)</p>
                @endif
                <p class="text-muted mb-4">{{ __('We couldn\'t find any matches. Double check spelling or try these keywords:') }}</p>
                
                {{-- Popular/Trending tags --}}
                <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                    <a href="{{ route('search', ['q' => 'hair dryer']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Hair Dryer</a>
                    <a href="{{ route('search', ['q' => 'shampoo']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Shampoo</a>
                    <a href="{{ route('search', ['q' => 'hair straightener']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Straightener</a>
                    <a href="{{ route('search', ['q' => 'curler']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Curler</a>
                    <a href="{{ route('search', ['q' => 'comb']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Comb</a>
                </div>

                {{-- Support redirection --}}
                <div class="d-inline-block">
                    <a href="https://wa.me/919999999999?text=Hi%20Dream%20Attitude,%20I%20couldn't%20find%20the%20product%20I%20was%20looking%20for:%20{{ urlencode($originalQuery) }}" target="_blank" class="btn btn-success d-flex align-items-center gap-2 rounded-pill px-4">
                        <i class="bi bi-whatsapp"></i> {{ __('Inquire on WhatsApp') }}
                    </a>
                </div>
            </div>

            {{-- Bestsellers section --}}
            @if ($bestsellers->isNotEmpty())
                <div class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h4 mb-0 fw-semibold">{{ __('Popular Bestsellers') }}</h3>
                        <span class="text-muted small">{{ __('Handpicked favorites') }}</span>
                    </div>
                    <div class="sf-product-grid">
                        @foreach ($bestsellers as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="sf-product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-4">
                {{ $products->links('vendor.pagination.storefront') }}
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/smart-search.js') }}"></script>
<script>
if (window.Store && @json($originalQuery)) {
    Store.track('search', {
        query: @json($originalQuery),
        results: {{ $products->total() }}
    });
}
</script>
@endpush
