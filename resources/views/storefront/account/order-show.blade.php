@extends('layouts.account')
@section('title', 'Order '.$order->order_number)
@section('account-content')
<h1 style="color:white;font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-receipt" style="color:var(--color-gold);"></i>Order {{ Str::limit($order->order_number, 20) }}
</h1>

<div style="display:grid;gap:24px;grid-template-columns:1fr;">

    {{-- Order Items --}}
    <div class="sf-account-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--color-border);color:white;font-weight:600;font-size:14px;">Order Items</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--color-border);">
                        <th style="padding:10px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Product</th>
                        <th style="padding:10px 20px;text-align:left;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Variant</th>
                        <th style="padding:10px 20px;text-align:right;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Qty</th>
                        <th style="padding:10px 20px;text-align:right;color:var(--color-gold);font-size:11px;text-transform:uppercase;letter-spacing:1px;font-weight:500;">Price</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($order->orderItems as $item)
                    <tr style="border-bottom:1px solid var(--color-border);">
                        <td style="padding:12px 20px;color:white;font-size:13px;">{{ $item->product_name_snapshot }}</td>
                        <td style="padding:12px 20px;color:var(--color-text-secondary);font-size:13px;">
                            @if($item->variant_title_snapshot && !in_array(strtolower(trim($item->variant_title_snapshot)), ['default', 'default title', '']))
                                {{ $item->variant_title_snapshot }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding:12px 20px;text-align:right;color:var(--color-text-secondary);font-size:13px;">{{ $item->qty }}</td>
                        <td style="padding:12px 20px;text-align:right;color:white;font-size:13px;font-weight:500;">₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="sf-account-card">
        <div style="font-weight:600;color:white;font-size:14px;margin-bottom:16px;">Summary</div>
        <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
            <span style="color:var(--color-text-secondary);font-size:13px;">Status</span>
            <span class="sf-badge {{ strtolower($order->order_status) }}">{{ \App\Models\Order::STATUS_LABELS[$order->order_status]['label'] ?? $order->order_status }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
            <span style="color:var(--color-text-secondary);font-size:13px;">Payment</span>
            <span class="sf-badge {{ $order->payment_status === 'paid' ? 'delivered' : 'processing' }}">{{ ucfirst($order->payment_status) }}</span>
        </div>
        <div style="border-top:1px solid var(--color-border);padding-top:12px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--color-text-secondary);margin-bottom:6px;">
                <span>Subtotal</span><span style="color:white;">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if ($order->discount_total > 0)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                <span style="color:var(--color-success);">Discount</span><span style="color:var(--color-success);">−₹{{ number_format($order->discount_total, 2) }}</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--color-text-secondary);margin-bottom:6px;">
                <span>Shipping</span><span style="color:white;">₹{{ number_format($order->shipping_total, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;border-top:1px solid var(--color-border-gold);padding-top:10px;margin-top:10px;">
                <span style="color:var(--color-gold);font-weight:600;font-size:16px;">Total</span>
                <span style="color:var(--color-gold);font-weight:600;font-size:16px;">₹{{ number_format($order->grand_total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Shipment --}}
    @if ($order->shipments->isNotEmpty())
    @php $ship = $order->shipments->first(); @endphp
    <div class="sf-account-card">
        <div style="font-weight:600;color:white;font-size:14px;margin-bottom:12px;">Shipment</div>
        <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:8px;">Status: <strong style="color:white;">{{ $ship->status }}</strong></p>
        @if ($ship->awb)
            <p style="color:var(--color-text-secondary);font-size:13px;margin-bottom:8px;">AWB: <strong style="color:white;">{{ $ship->awb }}</strong></p>
        @endif
        @if ($ship->tracking_url)
            <a href="{{ $ship->tracking_url }}" target="_blank" style="color:var(--color-gold);font-size:12px;text-transform:uppercase;letter-spacing:1px;text-decoration:none;border:1px solid var(--color-gold);padding:8px 16px;border-radius:var(--radius-sm);display:inline-block;">Track Package →</a>
        @endif
    </div>
    @endif

    {{-- Return request --}}
    @if ($order->order_status === 'delivered' && $order->returnRequests->isEmpty())
    <div class="sf-account-card" style="border-color:rgba(201,168,76,0.3);">
        <div style="font-weight:600;color:var(--color-gold);font-size:14px;margin-bottom:12px;">Request a Return</div>
        <form action="{{ route('account.orders.return.store', $order) }}" method="post">
            @csrf
            <div style="margin-bottom:12px;">
                <label class="sf-label">Reason for return</label>
                <textarea name="reason" class="sf-input" rows="3" required placeholder="Please describe the issue..." style="resize:vertical;"></textarea>
            </div>
            <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 24px;height:40px;font-size:12px;">Submit Return Request</button>
        </form>
    </div>
    @elseif ($order->returnRequests->isNotEmpty())
        @php $ret = $order->returnRequests->first(); @endphp
        <div class="sf-account-card" style="display:flex;align-items:center;gap:8px;color:var(--color-text-secondary);font-size:13px;">
            <i class="bi bi-info-circle" style="color:var(--color-gold);"></i>
            Return request submitted — Status: <strong style="color:white;">{{ $ret->status }}</strong>
        </div>
    @endif

    {{-- Actions --}}
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <form action="{{ route('account.orders.reorder', $order) }}" method="post">
            @csrf
            <button type="submit" style="background:transparent;border:1px solid var(--color-gold);color:var(--color-gold);padding:10px 20px;border-radius:var(--radius-sm);font-size:12px;text-transform:uppercase;letter-spacing:1px;cursor:pointer;transition:var(--transition);">
                <i class="bi bi-arrow-repeat" style="margin-right:4px;"></i>Reorder This
            </button>
        </form>
        <a href="{{ route('account.orders') }}" style="color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-decoration:none;">← All Orders</a>
    </div>
</div>
@endsection
