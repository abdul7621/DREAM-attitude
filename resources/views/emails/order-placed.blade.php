<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Order Confirmed</title></head>
<body style="font-family:sans-serif;max-width:560px;margin:0 auto;padding:20px;color:#222">
<h2>Order Confirmed ✅</h2>
<p>Hi {{ $customer_name }},</p>
<p>Your order <strong>#{{ $order_number }}</strong> has been placed successfully!</p>
<p>Total: <strong>{{ $grand_total }}</strong></p>
<p>We'll send you a shipping update once your order is dispatched.</p>
<p style="margin-top:32px;color:#888;font-size:13px">Thank you for shopping with us!</p>
</body></html>
