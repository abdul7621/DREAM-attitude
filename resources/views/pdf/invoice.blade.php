<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .muted { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <h2>Tax invoice</h2>
    <p><strong>{{ config('commerce.name', config('app.name')) }}</strong><br>
        <span class="muted">GSTIN: {{ config('commerce.gstin', '—') }}</span></p>
    <p>
        <strong>Invoice #:</strong> {{ $order->order_number }}<br>
        <strong>Date:</strong> {{ $order->placed_at?->timezone(config('commerce.timezone'))->format('d M Y, H:i') ?? '—' }}<br>
        <strong>Payment:</strong> {{ strtoupper($order->payment_method) }} — {{ $order->payment_status }}
    </p>
    <h3>Bill to</h3>
    <p>
        {{ $order->customer_name }}<br>
        @if ($order->email) {{ $order->email }}<br> @endif
        {{ $order->phone }}<br>
        {{ $order->address_line1 }}<br>
        @if ($order->address_line2) {{ $order->address_line2 }}<br> @endif
        {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
        {{ $order->country }}
    </p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th class="right">Qty</th>
                <th class="right">Rate</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $i => $oi)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $oi->product_name_snapshot }} @if ($oi->variant_title_snapshot) ({{ $oi->variant_title_snapshot }}) @endif<br><span class="muted">SKU: {{ $oi->sku_snapshot }}</span></td>
                    <td class="right">{{ $oi->qty }}</td>
                    <td class="right">₹{{ number_format((float) $oi->unit_price, 2) }}</td>
                    <td class="right">₹{{ number_format((float) $oi->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="right">
        <strong>Subtotal:</strong> ₹{{ number_format((float) $order->subtotal, 2) }}<br>
        @if ((float) $order->discount_total > 0)
            <strong>Discount:</strong> −₹{{ number_format((float) $order->discount_total, 2) }}<br>
        @endif
        <strong>Shipping:</strong> ₹{{ number_format((float) $order->shipping_total, 2) }}<br>
        @if ((float) $order->tax_total > 0)
            <strong>Tax (GST):</strong> ₹{{ number_format((float) $order->tax_total, 2) }}<br>
        @endif
        <strong>Grand total:</strong> ₹{{ number_format((float) $order->grand_total, 2) }} {{ $order->currency }}
    </p>
    @if ($order->coupon_code_snapshot)
        <p class="muted">Coupon: {{ $order->coupon_code_snapshot }}</p>
    @endif
</body>
</html>
