<div class="mb-4">
    @if ($product->is_bestseller)
        <x-sf-badge variant="warning" class="text-dark mb-2"><i class="bi bi-fire"></i> Bestseller</x-sf-badge>
    @endif

    <div class="d-flex justify-content-between align-items-start gap-3">
        <h1 class="product-name fs-2 fw-bold mb-1">{{ $product->name }}</h1>
        @auth
            <button type="button" class="btn btn-light rounded-circle shadow-sm p-2 wishlist-heart border" data-product-id="{{ $product->id }}" style="line-height:1; min-width: 42px; min-height: 42px;" aria-label="Add to Wishlist">
                <i class="bi bi-heart fs-5 text-muted"></i>
            </button>
        @endauth
    </div>

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

    @php
        $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
        $urgencyMsg = ($product->meta['urgency_message'] ?? null) ?: ($copy['urgency_message'] ?? '');
        $offerMsg = ($product->meta['offer_message'] ?? null) ?: ($copy['offer_message'] ?? '');
    @endphp

    @if($offerMsg)
        <div class="mt-2 text-success fw-semibold small bg-success bg-opacity-10 px-3 py-2 rounded border border-success border-opacity-25 d-inline-block">
            <i class="bi bi-percent me-1"></i> {{ $offerMsg }}
        </div>
    @endif

    {{-- Urgency --}}
    @php 
        $firstVariant = $product->variants->first(); 
        $stockCap = $product->meta['stock_display_cap'] ?? app(\App\Services\SettingsService::class)->get('theme.default_stock_display_cap', 80);
        $showStock = $product->meta['show_stock_count'] ?? true;
    @endphp
    @if ($firstVariant && $firstVariant->track_inventory && $firstVariant->stock_qty > 0 && $showStock)
        @php
            $displayQty = min($firstVariant->stock_qty, $stockCap);
            $msg = $urgencyMsg ?: '🔥 Selling Fast! Only {stock} Left – Order Now!';
        @endphp
        <div class="mt-2 sf-urgency">
            <span class="sf-pulse-dot"></span>
            <span id="urgencyLabel">{{ str_replace('{stock}', $displayQty, $msg) }}</span>
        </div>
    @elseif($urgencyMsg)
        <div class="mt-2 sf-urgency">
            <span class="sf-pulse-dot"></span>
            <span>{{ str_replace('{stock}', '', $urgencyMsg) }}</span>
        </div>
    @endif
</div>
