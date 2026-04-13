@if (isset($relatedProducts) && $relatedProducts->isNotEmpty())
    <section class="sf-section" style="background:var(--color-bg-surface);border-radius:var(--radius-md);padding:32px 0;">
        <div class="sf-container">
            <h2 class="sf-section-title" style="margin-bottom:24px;">You May Also Like</h2>
            <div class="sf-product-grid">
                @foreach ($relatedProducts as $rp)
                    <x-product-card :product="$rp" />
                @endforeach
            </div>
        </div>
    </section>
@endif
