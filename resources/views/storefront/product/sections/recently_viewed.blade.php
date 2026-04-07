@if (isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
    <section class="sf-section mt-5 border-top pt-5">
        <h2 class="fs-4 fw-bold mb-4">Recently Viewed</h2>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-3">
            @foreach ($recentlyViewed as $rv)
                <div class="col">
                    <x-product-card :product="$rv" />
                </div>
            @endforeach
        </div>
    </section>
@endif
