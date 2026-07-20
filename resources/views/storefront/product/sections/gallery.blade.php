<div class="sf-pdp-gallery">
    @if ($product->primaryImage())
        <div class="main-img-wrap">
            <img src="{{ asset('storage/'.$product->primaryImage()->path) }}" class="main-img" alt="{{ $product->primaryImage()->alt_text ?? $product->name }} - {{ config('app.name') }}" loading="eager" fetchpriority="high" width="600" height="600">
        </div>
    @else
        <div style="background:var(--color-bg-elevated);width:100%;position:relative;padding-top:100%;border: 1px solid var(--color-border);border-radius: var(--radius-md);"></div>
    @endif
    @if ($product->images->count() > 1)
        <div class="sf-pdp-thumbs" style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px;">
            @foreach ($product->images as $i => $image)
                <button type="button" aria-label="View Image {{ $i + 1 }}" style="padding: 0; border: none; background: none; width: 72px; height: 72px; flex-shrink: 0; cursor: pointer;"
                     onclick="document.querySelector('.main-img').src='{{ asset('storage/'.$image->path) }}'; document.querySelectorAll('.sf-pdp-thumbs img').forEach(t=>t.classList.remove('active')); this.querySelector('img').classList.add('active');">
                     <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $product->name }} - Image {{ $i + 1 }} - {{ config('app.name') }}" loading="lazy"
                          class="{{ $i === 0 ? 'active' : '' }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 1px solid var(--color-border); transition: var(--transition);">
                </button>
            @endforeach
        </div>
    @endif
</div>
