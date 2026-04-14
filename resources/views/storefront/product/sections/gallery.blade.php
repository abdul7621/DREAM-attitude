<div class="sf-pdp-gallery">
    @if ($product->primaryImage())
        <img src="{{ asset('storage/'.$product->primaryImage()->path) }}" class="main-img" alt="{{ $product->primaryImage()->alt_text ?? $product->name }}">
    @else
        <div style="background:var(--color-bg-elevated);width:100%;position:relative;padding-top:100%;border: 1px solid var(--color-border);border-radius: var(--radius-md);"></div>
    @endif
    @if ($product->images->count() > 1)
        <div class="sf-pdp-thumbs">
            @foreach ($product->images as $i => $image)
                <img src="{{ asset('storage/'.$image->path) }}" alt="thumb"
                     class="{{ $i === 0 ? 'active' : '' }}"
                     onclick="document.querySelector('.main-img').src=this.src; document.querySelectorAll('.sf-pdp-thumbs img').forEach(t=>t.classList.remove('active')); this.classList.add('active');">
            @endforeach
        </div>
    @endif
</div>
