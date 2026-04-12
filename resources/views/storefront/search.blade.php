@extends('layouts.storefront')

@section('title', $q ? __('Search: :q', ['q' => $q]) : __('All Products'))

@section('content')
<section class="sf-section">
    <div class="sf-container">
        <h1 class="h3 mb-4">{{ $q ? __('Search Results') : __('All Products') }}</h1>
        <form action="{{ route('search') }}" method="get" class="mb-4 position-relative">
            <div class="input-group input-group-lg">
                <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Search products…') }}" autocomplete="off" id="site-search-input" list="search-suggestions">
                <datalist id="search-suggestions"></datalist>
                <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
            </div>
        </form>
        @if ($products->isEmpty())
            <p class="text-muted">{{ __('No products found.') }}</p>
        @else
            <div class="sf-product-grid">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('site-search-input');
    const list = document.getElementById('search-suggestions');
    const suggestUrl = @json(route('search.suggest'));
    if (!input || !list) return;
    let t;
    input.addEventListener('input', function () {
        clearTimeout(t);
        const q = this.value.trim();
        list.innerHTML = '';
        if (q.length < 2) return;
        t = setTimeout(function () {
            fetch(suggestUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    (data.items || []).forEach(function (item) {
                        const opt = document.createElement('option');
                        opt.value = item.title;
                        list.appendChild(opt);
                    });
                }).catch(function () {});
        }, 250);
    });
})();
</script>
@endpush
