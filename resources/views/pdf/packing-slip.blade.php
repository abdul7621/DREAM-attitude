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
        <thead><tr><th>Product</th><th>SKU</th><th>Qty</th></tr></thead>
        <tbody>
            @foreach ($order->orderItems as $oi)
                <tr>
                    <td>{{ $oi->product_name_snapshot }} @if ($oi->variant_title_snapshot) — {{ $oi->variant_title_snapshot }} @endif</td>
                    <td>{{ $oi->sku_snapshot }}</td>
                    <td>{{ $oi->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
