@props([
    'icon' => 'bi-shield-check',
    'title' => 'Secure',
    'text' => ''
])

<div {{ $attributes->merge(['class' => 'sf-trust-badge d-flex align-items-center mb-2']) }}>
    <div class="sf-trust-icon text-success me-2 fs-5">
        <i class="bi {{ $icon }}"></i>
    </div>
    <div>
        <div class="fw-semibold" style="font-size: 0.9rem;">{{ $title }}</div>
        @if($text)
            <div class="text-muted" style="font-size: 0.8rem;">{{ $text }}</div>
        @endif
    </div>
</div>
