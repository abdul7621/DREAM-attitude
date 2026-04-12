@component('mail::message')
# Your Order Has Been Shipped!

Hi {{ $data['customer_name'] }},

Good news! Your order #{{ $data['order_number'] }} has been dispatched and is on its way to you.

@if(!empty($data['tracking_url']))
@component('mail::button', ['url' => $data['tracking_url']])
Track Your Order
@endcomponent
@elseif(!empty($data['awb_number']))
**AWB Number:** {{ $data['awb_number'] }}  
**Courier:** {{ $data['courier_name'] ?? 'Partner' }}
@endif

Thank you for shopping with us!

Thanks,<br>
{{ config('app.name') }}
@endcomponent
