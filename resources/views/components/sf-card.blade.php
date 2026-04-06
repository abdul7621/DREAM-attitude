@props([
    'padding' => 'md',
    'shadow' => 'sm',
])

@php
$paddingClass = match($padding) {
    'sm' => 'p-2',
    'lg' => 'p-4',
    default => 'p-3',
};
$shadowClass = match($shadow) {
    'none' => '',
    'md' => 'shadow-md',
    'lg' => 'shadow-lg',
    default => 'shadow-sm',
};
@endphp

<div {{ $attributes->merge(['class' => 'card sf-card bg-white border-0 ' . $shadowClass]) }}>
    <div class="card-body {{ $paddingClass }}">
        {{ $slot }}
    </div>
</div>
