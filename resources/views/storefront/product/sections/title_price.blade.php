<div class="mb-4">
    @if ($product->is_bestseller)
        <x-sf-badge variant="warning" class="text-dark mb-2"><i class="bi bi-fire"></i> Bestseller</x-sf-badge>
    @endif

    <h1 class="product-name fs-2 fw-bold mb-1">{{ $product->name }}</h1>

    {{-- Rating summary & Sales Count --}}
    <div class="d-flex align-items-center flex-wrap gap-3 mt-2">
        @if ($reviewCount > 0)
            <div class="d-flex align-items-center gap-1">
                <div class="text-warning">
                    @for ($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}" style="font-size:.9rem;"></i>
                    @endfor
                </div>
                <span class="small text-muted">({{ $reviewCount }})</span>
            </div>
        @endif
        @if(isset($product->sales_count) && $product->sales_count > 10)
            <span class="small text-success fw-semibold bg-success bg-opacity-10 px-2 py-1 rounded"><i class="bi bi-graph-up-arrow"></i> {{ $product->sales_count }} sold recently</span>
        @endif
    </div>

    @if ($product->short_description)
        <p class="text-muted mt-3 mb-0">{{ $product->short_description }}</p>
    @endif

    {{-- Price --}}
    <div class="product-price-lg mt-3 d-flex align-items-center gap-2 flex-wrap">
        <span id="priceLabel" class="fs-1 fw-bold text-dark">₹0</span>
        <span class="compare fs-4 text-muted text-decoration-line-through" id="compareLabel" style="display:none;"></span>
        <span id="savingsBadge" style="display:none;"><x-sf-badge variant="success"><i class="bi bi-tag-fill"></i> Save <span id="savingsAmount"></span></x-sf-badge></span>
    </div>

    {{-- Urgency --}}
    @php $firstVariant = $product->variants->first(); @endphp
    @if ($firstVariant && $firstVariant->track_inventory && $firstVariant->stock_qty <= config('commerce.pricing.low_stock_threshold', 5) && $firstVariant->stock_qty > 0)
        <div class="mt-2">
            <span class="text-danger fw-bold small"><i class="bi bi-lightning-charge-fill"></i> Only {{ $firstVariant->stock_qty }} left in stock!</span>
        </div>
    @endif
</div>
