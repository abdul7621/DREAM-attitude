@if (isset($relatedProducts) && $relatedProducts->isNotEmpty())
    <section class="sf-section mt-5">
        <h2 class="fs-4 fw-bold mb-4">You May Also Like</h2>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            @foreach ($relatedProducts as $rp)
                <div class="col">
                    <x-product-card :product="$rp" />
                </div>
            @endforeach
        </div>
    </section>
@endif
