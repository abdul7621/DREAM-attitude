@extends('layouts.storefront')

@section('title', $q ? __('Search: :q', ['q' => $q]) : __('Search'))

@section('content')
    <h1 class="h3 mb-4">{{ __('Search') }}</h1>
    <form action="{{ route('search') }}" method="get" class="mb-4 position-relative">
        <div class="input-group input-group-lg">
            <input type="search" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('Search products…') }}" autocomplete="off" id="site-search-input" list="search-suggestions">
            <datalist id="search-suggestions"></datalist>
            <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
        </div>
    </form>
    @if ($q === '')
        <p class="text-muted">{{ __('Enter a search term.') }}</p>
    @elseif ($products->isEmpty())
        <p class="text-muted">{{ __('No products found.') }}</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            @foreach ($products as $product)
                <div class="col">
                    @include('components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @endif
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
