@props([
    'type' => 'text',
    'name',
    'label' => null,
    'error' => null,
    'icon' => null,
    'required' => false,
    'readonly' => false,
])

@php
    $resolvedError = $error ?? ($errors->has($name) ? $errors->first($name) : null);
    $resolvedValue = old($name, $attributes->get('value', ''));
@endphp

<div class="mb-3 sf-input-wrapper">
    @if($label)
        <label for="{{ $name }}" class="form-label sf-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    @endif
    <div class="input-group">
        @if($icon)
            <span class="input-group-text"><i class="bi {{ $icon }}"></i></span>
        @endif
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
            value="{{ $resolvedValue }}"
            {{ $attributes->merge(['class' => 'form-control' . ($resolvedError ? ' is-invalid' : '')]) }}
            @if($required) required @endif
            @if($readonly) readonly @endif
        >
    </div>
    @if($resolvedError)
        <div class="invalid-feedback d-block">{{ $resolvedError }}</div>
    @endif
</div>
