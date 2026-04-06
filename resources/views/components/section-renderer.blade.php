@props(['sections', 'viewPrefix' => 'storefront.product.sections.', 'data' => []])

@foreach($sections as $section)
    @if(isset($section['enabled']) && $section['enabled'])
        @include($viewPrefix . $section['key'], $data)
    @endif
@endforeach
