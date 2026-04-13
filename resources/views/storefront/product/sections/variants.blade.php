@php
    $realVariants = $product->variants
        ->where('is_active', true)
        ->filter(fn($v) =>
            !in_array(strtolower(trim($v->title)),
            ['default title', 'default', ''])
        );
    $hasRealVariants = $realVariants->count() > 0;
@endphp

@if($hasRealVariants)
<div class="sf-pdp-info-block">
    <span class="variant-label">Select Variant</span>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;">
        @foreach($realVariants as $index => $v)
            @php
                $p = $variantPrices[$v->id] ?? [];
                $vImg = '';
                try {
                    $vImg = $v->images->first()?->url() ?? '';
                } catch (\Exception $e) {
                    $vImg = '';
                }
            @endphp
            <button type="button"
                class="variant-btn sf-variant-btn {{ $loop->first && (!$v->track_inventory || $v->stock_qty > 0) ? 'active' : '' }} {{ ($v->track_inventory && $v->stock_qty <= 0) ? 'disabled' : '' }}"
                data-id="{{ $v->id }}"
                data-price="{{ $p['display'] ?? $v->price_retail }}"
                data-compare="{{ $p['compare'] ?? '' }}"
                data-name="{{ $v->title }}"
                data-stock="{{ $v->track_inventory ? $v->stock_qty : 999 }}"
                data-track="{{ $v->track_inventory ? '1' : '0' }}"
                data-img="{{ $vImg }}"
                @if($v->track_inventory && $v->stock_qty <= 0) disabled @endif>
                {{ $v->title }}
            </button>
        @endforeach
    </div>
    <input type="hidden" name="variant_id" id="hidden_variant_id"
        value="{{ $realVariants->first()?->id }}">
</div>
@else
{{-- Single/Default variant — no selection shown --}}
<input type="hidden" name="variant_id"
    id="hidden_variant_id"
    value="{{ $product->variants->first()?->id }}">
@endif
