<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Packing {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Packing slip</h2>
    <p><strong>Order:</strong> {{ $order->order_number }}<br>
        <strong>Ship to:</strong> {{ $order->customer_name }}, {{ $order->phone }}</p>
    <p>{{ $order->address_line1 }}@if ($order->address_line2), {{ $order->address_line2 }}@endif<br>
        {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}, {{ $order->country }}</p>
    <table>
        <thead>
            <tr>
                <th>Product Item</th>
                <th style="width: 90px; text-align: center;">Size / Volume</th>
                <th style="width: 110px;">SKU</th>
                <th style="width: 50px; text-align: center;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $oi)
                <tr>
                    <td>
                        <strong>{{ $oi->product_name_snapshot }}</strong>
                    </td>
                    <td style="text-align: center; font-weight: bold; font-size: 13px; color: #111;">
                        {{ $oi->volume_badge ?? ($oi->variant_title_snapshot ?: '—') }}
                    </td>
                    <td><code>{{ $oi->sku_snapshot ?? '—' }}</code></td>
                    <td style="text-align: center; font-weight: bold; font-size: 14px;">{{ $oi->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
