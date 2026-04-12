@if (isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
    <section class="sf-section mt-5 border-top pt-5">
        <h2 class="fs-4 fw-bold mb-4">Recently Viewed</h2>
        <div class="sf-product-grid">
            @foreach ($recentlyViewed as $rv)
                <x-product-card :product="$rv" />
            @endforeach
        </div>
    </section>
@endif
