<div class="sf-product-gallery">
    @if ($product->images->isNotEmpty())
        <div id="pCarousel" class="carousel slide" data-bs-ride="false">
            <div class="carousel-inner rounded">
                @foreach ($product->images as $i => $image)
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100" alt="{{ $image->alt_text ?? $product->name }}">
                    </div>
                @endforeach
            </div>
            @if ($product->images->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#pCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#pCarousel" data-bs-slide="next"><span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span></button>
            @endif
        </div>
        {{-- Thumbnails --}}
        @if ($product->images->count() > 1)
            <div class="d-flex gap-2 mt-3 flex-wrap">
                @foreach ($product->images as $i => $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="thumb"
                         class="rounded border {{ $i === 0 ? 'border-dark' : '' }}"
                         style="width:60px;height:60px;object-fit:cover;cursor:pointer;opacity:{{ $i === 0 ? '1' : '.6' }};"
                         onclick="document.querySelector('#pCarousel').querySelector('[data-bs-slide-to]') || bootstrap.Carousel.getOrCreateInstance(document.getElementById('pCarousel')).to({{ $i }}); document.querySelectorAll('.sf-product-gallery .d-flex img').forEach(t=>{t.style.opacity='.6';t.classList.remove('border-dark')}); this.style.opacity='1'; this.classList.add('border-dark');">
                @endforeach
            </div>
        @endif
    @else
        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="min-height:400px;"><i class="bi bi-image text-muted" style="font-size:3rem;"></i></div>
    @endif
</div>
