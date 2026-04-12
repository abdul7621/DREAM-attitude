@if (isset($relatedProducts) && $relatedProducts->isNotEmpty())
    <section class="sf-section mt-5">
        <h2 class="fs-4 fw-bold mb-4">You May Also Like</h2>
        <div class="sf-product-grid">
            @foreach ($relatedProducts as $rp)
                <x-product-card :product="$rp" />
            @endforeach
        </div>
    </section>
@endif
