@props([
    'variant' => 'info',
])

<span {{ $attributes->merge(['class' => 'badge bg-' . $variant . ' sf-badge']) }}>
    {{ $slot }}
</span>
