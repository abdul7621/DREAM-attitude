<div class="sf-pdp-info-block">
    @if ($product->is_bestseller)
        <span class="badge" style="background:var(--color-gold);color:#0a0a0a;padding:4px 8px;font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:600;border-radius:var(--radius-sm);margin-bottom:12px;display:inline-block;">Bestseller</span>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
        <h1 class="title">{{ $product->name }}</h1>
        @auth
            <button type="button" class="wishlist-heart" data-product-id="{{ $product->id }}" title="Wishlist" style="background:none;border:none;color:var(--color-text-muted);font-size:24px;cursor:pointer;">
                <i class="bi bi-heart"></i>
            </button>
        @endauth
    </div>

    {{-- Rating summary & Sales Count --}}
    <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 16px; margin: 8px 0 16px;">
        @if ($reviewCount > 0)
            <div style="display: flex; align-items: center; gap: 4px; color: var(--color-gold);">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}" style="font-size:14px;"></i>
                @endfor
                <span style="color: var(--color-text-muted); font-size: 13px;">({{ $reviewCount }})</span>
            </div>
        @endif
        @if(isset($product->sales_count) && $product->sales_count > 10)
            <span style="font-size: 12px; color: var(--color-success); background: rgba(39, 103, 73, 0.1); padding: 4px 8px; border-radius: var(--radius-sm);"><i class="bi bi-graph-up-arrow"></i> {{ $product->sales_count }} sold recently</span>
        @endif
    </div>

    @if ($product->short_description)
        <p class="sf-pdp-desc" style="margin-bottom: 24px;">{{ $product->short_description }}</p>
    @endif

    @php
        $defaultVar = $product->variants->where('is_active', true)->first();
        $defPrice = $defaultVar ? ((float) ($variantPrices[$defaultVar->id]['display'] ?? $defaultVar->price_retail)) : 0;
        $defCompare = $defaultVar ? ((float) ($variantPrices[$defaultVar->id]['compare'] ?? $defaultVar->compare_at_price)) : 0;
        $defSavings = $defCompare > $defPrice ? ($defCompare - $defPrice) : 0;
    @endphp

    {{-- Price --}}
    <div class="price-block" style="margin-bottom: 16px;">
        <span id="priceLabel" class="price">₹{{ number_format($defPrice) }}</span>
        <span class="mrp" id="compareLabel" style="display:{{ $defCompare > $defPrice ? 'inline' : 'none' }};">₹{{ number_format($defCompare) }}</span>
        <span id="savingsBadge" style="display:{{ $defSavings > 0 ? 'inline' : 'none' }};font-size:11px;color:var(--color-success);background:rgba(39, 103, 73, 0.1);padding:4px 8px;border-radius:var(--radius-sm);"><i class="bi bi-tag-fill"></i> Save <span id="savingsAmount">₹{{ number_format($defSavings) }}</span></span>
    </div>

    @php
        $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.product', config('commerce.conversion_copy.product') ?? []);
        $urgencyMsg = ($product->meta['urgency_message'] ?? null) ?: ($copy['urgency_message'] ?? '');
        $offerMsg = ($product->meta['offer_message'] ?? null) ?: ($copy['offer_message'] ?? '');
    @endphp

    @if($offerMsg)
        <div style="margin-bottom:12px; color:var(--color-success); font-size:12px; background:rgba(39, 103, 73, 0.1); padding:8px 12px; border-radius:var(--radius-sm); display:inline-block;">
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
        <div style="display:flex;align-items:center;gap:8px;background:var(--color-bg-elevated);color:var(--color-gold);padding:8px 12px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;margin-bottom:16px;">
            <span style="width:8px;height:8px;background:var(--color-gold);border-radius:50%;box-shadow:0 0 8px var(--color-gold);"></span>
            <span id="urgencyLabel">{{ str_replace('{stock}', $displayQty, $msg) }}</span>
        </div>
    @elseif($firstVariant && $firstVariant->track_inventory && $firstVariant->stock_qty <= 0)
        <div style="background:rgba(197,48,48,0.1);color:#f87171;padding:8px 12px;border-radius:var(--radius-sm);font-size:12px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
            <i class="bi bi-x-circle"></i>
            Out of Stock
        </div>
    @endif
</div>
