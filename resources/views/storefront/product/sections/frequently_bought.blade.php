@if (isset($frequentlyBought) && $frequentlyBought->isNotEmpty())
    <section class="sf-section mt-5 p-4 rounded" style="background-color: var(--sf-bg-light, #f8f9fa);">
        <h2 class="fs-4 fw-bold mb-4"><i class="bi bi-bag-heart me-2"></i> Frequently Bought Together</h2>
        <div class="d-flex flex-wrap gap-3 align-items-center">
            {{-- Current Product --}}
            <div class="text-center" style="width: 130px;">
                <div class="position-relative">
                    <img src="{{ $product->primaryImage() ? asset('storage/'.$product->primaryImage()->path) : 'https://placehold.co/150' }}" class="img-thumbnail border-2" style="border-color: var(--sf-primary, #000) !important;" alt="Current">
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-white" style="background-color: var(--sf-primary, #000);">This Item</span>
                </div>
                <div class="small fw-semibold mt-2 text-truncate" title="{{ $product->name }}">{{ $product->name }}</div>
            </div>
            
            @foreach ($frequentlyBought as $fb)
                <div class="text-muted fs-4"><i class="bi bi-plus-lg"></i></div>
                <div class="text-center" style="width: 130px;">
                    <a href="{{ route('product.show', $fb) }}" class="text-decoration-none text-dark sf-hover-lift">
                        <img src="{{ $fb->primaryImage() ? asset('storage/'.$fb->primaryImage()->path) : 'https://placehold.co/150' }}" class="img-thumbnail shadow-sm" alt="{{ $fb->name }}">
                        <div class="small fw-semibold mt-2 text-truncate" title="{{ $fb->name }}">{{ $fb->name }}</div>
                        <div class="small fw-bold" style="color: var(--sf-primary, #000);">₹{{ $fb->variants->where('is_active', true)->sortBy('price_retail')->first()?->price_retail ?? 0 }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif
