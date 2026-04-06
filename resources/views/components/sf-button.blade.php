@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'icon' => null,
    'type' => 'button',
    'href' => null,
])

@php
$classes = 'btn btn-' . $variant;
if ($size !== 'md') {
    $classes .= ' btn-' . $size;
}
if ($loading) {
    $classes .= ' disabled sf-loading';
}
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="bi {{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($loading) disabled @endif>
        @if($loading)
            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
        @elseif($icon)
            <i class="bi {{ $icon }} me-1"></i>
        @endif
        {{ $slot }}
    </button>
@endif
