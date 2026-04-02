<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Your Order Shipped!</title></head>
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:20px;color:#222">
<h2>Your Order Has Shipped 🚚</h2>
<p>Hi {{ $customer_name }},</p>
<p>Great news! Your order <strong>#{{ $order_number }}</strong> is on its way.</p>
@if($awb)
<p>AWB / Tracking Number: <strong>{{ $awb }}</strong></p>
@endif
@if($tracking_url)
<p><a href="{{ $tracking_url }}" style="background:#4f46e5;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px">Track Your Order →</a></p>
@endif
<p style="margin-top:32px;color:#888;font-size:13px">Thank you for shopping with us!</p>
</body></html>
