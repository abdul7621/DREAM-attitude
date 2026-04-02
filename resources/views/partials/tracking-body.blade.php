@php $gtm = config('commerce.gtm.container_id'); @endphp
@if ($gtm)
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif
@php $pixel = config('commerce.meta.pixel_id'); @endphp
@if ($pixel)
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixel }}&ev=PageView&noscript=1" alt="" /></noscript>
@endif
