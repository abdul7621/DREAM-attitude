<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .header {
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header .meta {
            color: #777;
            font-size: 12px;
        }
        .totals {
            width: 50%;
            float: right;
        }
        .totals td {
            border: none;
            padding: 4px 12px;
        }
        .totals .final {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            padding-top: 8px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .company-info {
            float: left;
        }
        .customer-info {
            float: right;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="company-info">
            <h1>{{ app(\App\Services\SettingsService::class)->get('store.name', config('app.name')) }}</h1>
            <div class="meta">
                {{ app(\App\Services\SettingsService::class)->get('store.email') }}<br>
                @if(app(\App\Services\SettingsService::class)->get('gst.enabled'))
                    GSTIN: {{ app(\App\Services\SettingsService::class)->get('gst.gstin', 'N/A') }}
                @endif
            </div>
        </div>
        <div class="customer-info">
            <h2>INVOICE</h2>
            <div class="meta">
                Order Number: {{ $order->order_number }}<br>
                Date: {{ $order->placed_at ? $order->placed_at->format('d M Y') : $order->created_at->format('d M Y') }}
            </div>
        </div>
    </div>

    <div class="clearfix" style="margin-bottom: 30px;">
        <div style="float: left; width: 45%;">
            <strong>Billed To:</strong><br>
            {{ $order->customer_name }}<br>
            {{ $order->address_line1 }}<br>
            @if($order->address_line2) {{ $order->address_line2 }}<br> @endif
            {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
            Phone: {{ $order->phone }}<br>
            Email: {{ $order->email }}
        </div>
        <div style="float: right; width: 45%; text-align: right;">
            <strong>Payment Method:</strong><br>
            {{ strtoupper($order->payment_method) }}<br>
            <strong>Payment Status:</strong><br>
            {{ ucfirst($order->payment_status) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>
                    {{ $item->product_name_snapshot }}
                    @if($item->variant_title_snapshot)
                        <br><small style="color: #777;">{{ $item->variant_title_snapshot }}</small>
                    @endif
                </td>
                <td>{{ $item->qty }}</td>
                <td class="text-right">₹{{ number_format((float)$item->unit_price, 2) }}</td>
                <td class="text-right">₹{{ number_format((float)$item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">₹{{ number_format((float)$order->subtotal, 2) }}</td>
            </tr>
            @if((float)$order->discount_total > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">-₹{{ number_format((float)$order->discount_total, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Shipping</td>
                <td class="text-right">₹{{ number_format((float)$order->shipping_total, 2) }}</td>
            </tr>
            @if((float)$order->tax_total > 0)
            <tr>
                <td>Tax ({{ app(\App\Services\SettingsService::class)->get('gst.inclusive', true) ? 'Incl.' : 'Excl.' }})</td>
                <td class="text-right">₹{{ number_format((float)$order->tax_total, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="final">Grand Total</td>
                <td class="text-right final">₹{{ number_format((float)$order->grand_total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 50px; text-align: center; color: #777; font-size: 12px;">
        Thank you for shopping with {{ app(\App\Services\SettingsService::class)->get('store.name', config('app.name')) }}!
    </div>
</body>
</html>
