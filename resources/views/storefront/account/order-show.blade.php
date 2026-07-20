@extends('layouts.account')
@section('title', 'Order '.$order->order_number)
@section('account-content')
<h1 style="color:var(--color-text-primary);font-size:20px;font-weight:500;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;display:flex;align-items:center;gap:8px;">
    <i class="bi bi-receipt" style="color:var(--color-gold);"></i>Order {{ Str::limit($order->order_number, 20) }}
</h1>

{{-- Order Progress Timeline --}}
@php
    $statusSteps = ['placed', 'confirmed', 'packed', 'shipped', 'delivered'];
    $currentStepIdx = array_search(strtolower($order->order_status), $statusSteps);
    if (strtolower($order->order_status) === 'pending') $currentStepIdx = 0;
    
    $isCancelled = strtolower($order->order_status) === 'cancelled';
@endphp

@if (!$isCancelled)
<div class="sf-account-card mb-4" style="padding: 24px; border-color: var(--color-border) !important;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted);">
        <span>Ordered</span>
        <span>Confirmed</span>
        <span>Packed</span>
        <span>Dispatched</span>
        <span>Delivered</span>
    </div>
    <div style="display: flex; align-items: center; position: relative; height: 6px; background: rgba(0,0,0,0.05); border-radius: 4px; margin: 0 10px;">
        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: {{ $currentStepIdx !== false ? ($currentStepIdx / 4) * 100 : 0 }}%; background: var(--color-gold); border-radius: 4px; transition: width 0.5s ease;"></div>
        
        @for($s = 0; $s < 5; $s++)
            <div style="position: absolute; left: {{ ($s / 4) * 100 }}%; transform: translateX(-50%); width: 14px; height: 14px; border-radius: 50%; background: {{ $currentStepIdx !== false && $currentStepIdx >= $s ? 'var(--color-gold)' : '#e2e8f0' }}; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.08); z-index: 10;"></div>
        @endfor
    </div>
</div>
@else
<div class="sf-account-card mb-4" style="border-color: #dc3545 !important; background: rgba(220,53,69,0.04); color: #dc3545; font-weight: 600; font-size: 13px; padding: 16px 20px;">
    <i class="bi bi-x-circle-fill me-2"></i> This order has been cancelled.
</div>
@endif

<div style="display:grid;gap:24px;grid-template-columns:1fr;">

    {{-- Order Items --}}
    <div class="sf-account-card" style="padding:0;overflow:hidden; border-color: var(--color-border) !important;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--color-border);color:var(--color-text-primary);font-weight:600;font-size:14px;">Order Items</div>
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
                        <td style="padding:12px 20px;color:var(--color-text-primary);font-size:13px;">{{ $item->product_name_snapshot }}</td>
                        <td style="padding:12px 20px;color:var(--color-text-secondary);font-size:13px;">
                            @if($item->variant_title_snapshot && !in_array(strtolower(trim($item->variant_title_snapshot)), ['default', 'default title', '']))
                                {{ $item->variant_title_snapshot }}
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding:12px 20px;text-align:right;color:var(--color-text-secondary);font-size:13px;">{{ $item->qty }}</td>
                        <td style="padding:12px 20px;text-align:right;color:var(--color-text-primary);font-size:13px;font-weight:500;">₹{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Order Summary --}}
    <div class="sf-account-card" style="border-color: var(--color-border) !important;">
        <div style="font-weight:600;color:var(--color-text-primary);font-size:14px;margin-bottom:16px;">Summary</div>
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
                <span>Subtotal</span><span style="color:var(--color-text-primary);">₹{{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if ($order->discount_total > 0)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                <span style="color:var(--color-success);">Discount</span><span style="color:var(--color-success);">−₹{{ number_format($order->discount_total, 2) }}</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--color-text-secondary);margin-bottom:6px;">
                <span>Shipping</span><span style="color:var(--color-text-primary);">₹{{ number_format($order->shipping_total, 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;border-top:1px solid var(--color-border-gold);padding-top:10px;margin-top:10px;">
                <span style="color:var(--color-gold);font-weight:600;font-size:16px;">Total</span>
                <span style="color:var(--color-gold);font-weight:600;font-size:16px;">₹{{ number_format($order->grand_total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Shipment --}}
    @if ($order->shipments->isNotEmpty())
    @php
        $ship = $order->shipments->first();
        // Dynamic fallback tracking URL if null
        $effectiveTrackingUrl = $ship->tracking_url;
        if (!$effectiveTrackingUrl && $ship->awb) {
            $carrierKey = strtolower($ship->carrier ?? '');
            if (str_contains($carrierKey, 'ithink')) {
                $effectiveTrackingUrl = 'https://ithinklogistics.com/track?awb=' . urlencode($ship->awb);
            } elseif (str_contains($carrierKey, 'delhivery')) {
                $effectiveTrackingUrl = 'https://www.delhivery.com/track/package/' . urlencode($ship->awb);
            } elseif (str_contains($carrierKey, 'dtdc')) {
                $effectiveTrackingUrl = 'https://www.dtdc.in/tracking/shipment-tracking.asp?strCnNo=' . urlencode($ship->awb);
            } elseif (str_contains($carrierKey, 'bluedart')) {
                $effectiveTrackingUrl = 'https://www.bluedart.com/tracking?key=' . urlencode($ship->awb);
            }
        }
    @endphp
    <div class="sf-account-card" style="border-color: var(--color-border) !important;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <div style="font-weight:600;color:var(--color-text-primary);font-size:14px;"><i class="bi bi-truck" style="color:var(--color-gold);margin-right:6px;"></i>Shipment & Tracking Details</div>
            <span class="sf-badge {{ $ship->status === 'delivered' ? 'delivered' : 'processing' }}">{{ ucfirst($ship->status) }}</span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:16px;align-items:center;margin-bottom:16px;font-size:13px;color:var(--color-text-secondary);">
            <div>Carrier: <strong style="color:var(--color-text-primary);">{{ ucfirst($ship->carrier ?? 'Standard Courier') }}</strong></div>
            @if ($ship->awb)
                <div>AWB / Waybill: <strong style="color:var(--color-text-primary);" id="shipAwbText">{{ $ship->awb }}</strong>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $ship->awb }}'); this.innerText='Copied!'; setTimeout(()=>this.innerText='Copy', 2000);" style="background:var(--color-bg-elevated);border:1px solid var(--color-border);padding:2px 8px;border-radius:4px;font-size:11px;cursor:pointer;margin-left:4px;">Copy</button>
                </div>
            @endif
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            @if ($effectiveTrackingUrl)
                <a href="{{ $effectiveTrackingUrl }}" target="_blank" style="background:var(--color-gold);color:#000000;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;text-decoration:none;padding:10px 20px;border-radius:var(--radius-sm);display:inline-flex;align-items:center;gap:6px;">
                    <i class="bi bi-geo-alt-fill"></i> Track Package Live ➔
                </a>
            @endif
            <a href="https://wa.me/919876543210?text={{ urlencode('Hi Dream Attitude Team, I need help with my Order #'.$order->order_number.($ship->awb ? ' (AWB: '.$ship->awb.')' : '')) }}" target="_blank" style="background:#25D366;color:#FFFFFF;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;text-decoration:none;padding:10px 20px;border-radius:var(--radius-sm);display:inline-flex;align-items:center;gap:6px;">
                <i class="bi bi-whatsapp"></i> WhatsApp Order Support
            </a>
        </div>
    </div>
    @endif

    {{-- Return request --}}
    @if ($order->order_status === 'delivered' && $order->returnRequests->isEmpty())
    <div class="sf-account-card" style="border-color:rgba(201,168,76,0.3) !important;">
        <div style="font-weight:600;color:var(--color-gold);font-size:14px;margin-bottom:12px;">Request a Return / Replacement</div>
        <form action="{{ route('account.orders.return.store', $order) }}" method="post">
            @csrf
            <div style="margin-bottom:12px;">
                <label class="sf-label">Reason for request *</label>
                <textarea name="reason" class="sf-input" rows="3" required placeholder="Please describe the issue..." style="resize:vertical;"></textarea>
            </div>
            
            <div style="margin-bottom:16px;">
                <label class="sf-label">Request Type *</label>
                <div style="display: flex; gap: 16px; align-items: center; margin-top: 4px;">
                    <label style="font-size: 13px; color: var(--color-text-primary); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="type" value="refund" checked> Refund (Store Credit)
                    </label>
                    <label style="font-size: 13px; color: var(--color-text-primary); cursor: pointer; display: flex; align-items: center; gap: 6px;">
                        <input type="radio" name="type" value="replacement"> Replacement
                    </label>
                </div>
            </div>
            
            <button type="submit" class="sf-btn-primary" style="width:auto;padding:0 24px;height:40px;font-size:12px;">Submit Request</button>
        </form>
    </div>
    @elseif ($order->returnRequests->isNotEmpty())
        @php $ret = $order->returnRequests->first(); @endphp
        <div class="sf-account-card" style="display:flex;align-items:center;gap:8px;color:var(--color-text-secondary);font-size:13px; border-color: var(--color-border) !important;">
            <i class="bi bi-info-circle" style="color:var(--color-gold);"></i>
            <span>
                {{ ucfirst($ret->type ?? 'return') }} request submitted — Status: <strong style="color:var(--color-text-primary);">{{ $ret->status }}</strong>
            </span>
        </div>
    @endif

    {{-- Actions --}}
    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <form action="{{ route('account.orders.reorder', $order) }}" method="post" style="display:inline;">
            @csrf
            <button type="submit" style="background:transparent;border:1px solid var(--color-gold);color:var(--color-gold);padding:10px 20px;border-radius:var(--radius-sm);font-size:12px;text-transform:uppercase;letter-spacing:1px;cursor:pointer;transition:var(--transition);">
                <i class="bi bi-arrow-repeat" style="margin-right:4px;"></i>Reorder This
            </button>
        </form>

        <a href="{{ route('account.orders.invoice', $order) }}" class="text-decoration-none" style="display:inline-flex; align-items:center; gap:6px; border:1px solid var(--color-border); color:var(--color-text-primary); padding:10px 20px; border-radius:var(--radius-sm); font-size:12px; text-transform:uppercase; letter-spacing:1px;">
            <i class="bi bi-file-pdf text-danger"></i> Invoice PDF
        </a>

        @if(in_array($order->order_status, ['placed', 'pending']))
            <form action="{{ route('account.orders.cancel', $order) }}" method="post" onsubmit="return confirm('Are you sure you want to cancel this order?');" style="display:inline;">
                @csrf
                <button type="submit" style="background:transparent; border:1px solid #dc3545; color:#dc3545; padding:10px 20px; border-radius:var(--radius-sm); font-size:12px; text-transform:uppercase; letter-spacing:1px; cursor:pointer;">
                    <i class="bi bi-x-circle"></i> Cancel Order
                </button>
            </form>
        @endif

        <a href="{{ route('account.orders') }}" style="color:var(--color-text-muted);font-size:12px;text-transform:uppercase;letter-spacing:0.5px;text-decoration:none; margin-left: auto;">← All Orders</a>
    </div>
</div>
@endsection
