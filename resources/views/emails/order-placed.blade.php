@component('mail::message')
# Order #{{ $data['order_number'] }} Confirmed

Hi {{ $data['customer_name'] }},

Thank you for your purchase! We've received your order and are getting it ready for shipment.

**Order Details:**
- **Total:** ₹{{ current(explode('.', $data['grand_total'])) }}
- **Payment Method:** {{ strtoupper($data['payment_method']) }}
- **Status:** {{ $data['order_status'] }}

We will notify you once your order has been shipped.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
