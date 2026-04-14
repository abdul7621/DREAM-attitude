@if (isset($frequentlyBought) && $frequentlyBought->isNotEmpty())
    <section class="sf-section" style="background:var(--color-bg-elevated);border-radius:var(--radius-md);padding:32px 0;">
        <div class="sf-container">
            <h2 class="sf-section-title" style="margin-bottom:24px;">
                <i class="bi bi-bag-heart" style="color:var(--color-gold);margin-right:8px;"></i>Frequently Bought Together
            </h2>
            <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
                {{-- Current Product --}}
                <div style="text-align:center;width:130px;">
                    <div style="position:relative;">
                        <div style="position:relative;width:100%;padding-top:100%;overflow:hidden;border-radius:var(--radius-md);border:2px solid var(--color-gold);">
                            <img src="{{ $product->primaryImage() ? asset('storage/'.$product->primaryImage()->path) : '' }}"
                                 style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;"
                                 alt="Current">
                        </div>
                        <span style="position:absolute;top:-8px;right:-8px;background:var(--color-gold);color:#0a0a0a;font-size:9px;padding:3px 6px;border-radius:var(--radius-sm);font-weight:600;text-transform:uppercase;z-index:2;">This Item</span>
                    </div>
                    <div style="font-size:12px;font-weight:500;margin-top:8px;color:var(--color-text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $product->name }}">{{ $product->name }}</div>
                </div>

                @foreach ($frequentlyBought as $fb)
                    <div style="color:var(--color-text-muted);font-size:20px;"><i class="bi bi-plus-lg"></i></div>
                    <div style="text-align:center;width:130px;">
                        <a href="{{ route('product.show', $fb) }}" style="text-decoration:none;">
                            <div style="position:relative;width:100%;padding-top:100%;overflow:hidden;border-radius:var(--radius-md);border:1px solid var(--color-border);">
                                <img src="{{ $fb->primaryImage() ? asset('storage/'.$fb->primaryImage()->path) : '' }}"
                                     style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;"
                                     alt="{{ $fb->name }}">
                            <div style="font-size:12px;font-weight:500;margin-top:8px;color:var(--color-text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $fb->name }}">{{ $fb->name }}</div>
                            <div style="font-size:13px;font-weight:600;color:var(--color-gold);">₹{{ $fb->variants->where('is_active', true)->sortBy('price_retail')->first()?->price_retail ?? 0 }}</div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
