@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)

@section('content')
    {{-- ── Header ────────────────────────────────────────────── --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h4 mb-1">Order {{ $order->order_number }}</h1>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                <span class="badge bg-{{ $order->paymentColor() }}">{{ $order->paymentLabel() }}</span>
                <span class="badge bg-{{ $order->payment_method === 'cod' ? 'warning text-dark' : 'primary' }}">{{ strtoupper($order->payment_method) }}</span>
                <span class="small text-muted">{{ $order->placed_at?->format('d M Y, h:i A') ?? '—' }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-earmark-pdf"></i> Invoice</a>
            <a href="{{ route('admin.orders.packing', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box"></i> Packing Slip</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>

    <div class="row g-3">
        {{-- ── Left Column ───────────────────────────────────── --}}
        <div class="col-lg-8">
            {{-- Items --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-bag"></i> Order Items
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>Product</th><th>SKU</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Line Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderItems as $oi)
                                <tr>
                                    <td>
                                        {{ $oi->product_name_snapshot }}
                                        @if ($oi->variant_title_snapshot) <span class="text-muted">— {{ $oi->variant_title_snapshot }}</span> @endif
                                    </td>
                                    <td class="small text-muted">{{ $oi->sku_snapshot ?? '—' }}</td>
                                    <td class="text-center">{{ $oi->qty }}</td>
                                    <td class="text-end">₹{{ number_format((float) $oi->unit_price, 2) }}</td>
                                    <td class="text-end fw-semibold">₹{{ number_format((float) $oi->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top">
                    <table class="table table-sm table-borderless mb-0" style="max-width:300px;margin-left:auto;">
                        <tr><td class="text-muted">Subtotal</td><td class="text-end">₹{{ number_format((float) $order->subtotal, 2) }}</td></tr>
                        @if ((float) $order->discount_total > 0)
                            <tr><td class="text-muted">Discount</td><td class="text-end text-success">−₹{{ number_format((float) $order->discount_total, 2) }}</td></tr>
                        @endif
                        <tr><td class="text-muted">Shipping</td><td class="text-end">₹{{ number_format((float) $order->shipping_total, 2) }}</td></tr>
                        @if ((float) $order->tax_total > 0)
                            <tr><td class="text-muted">Tax</td><td class="text-end">₹{{ number_format((float) $order->tax_total, 2) }}</td></tr>
                        @endif
                        <tr class="fw-bold border-top"><td>Grand Total</td><td class="text-end">₹{{ number_format((float) $order->grand_total, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- Shipments --}}
            @if ($order->shipments->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-truck"></i> Shipments
                    </div>
                    <div class="card-body">
                        @foreach ($order->shipments as $s)
                            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                                <div>
                                    <strong>{{ ucfirst($s->carrier) }}</strong>
                                    <span class="text-muted mx-1">•</span>
                                    AWB: <code>{{ $s->awb ?? '—' }}</code>
                                    <span class="badge bg-{{ $s->status === 'delivered' ? 'success' : 'dark' }} ms-2">{{ ucfirst($s->status) }}</span>
                                </div>
                                @if ($s->tracking_url)
                                    <a href="{{ $s->tracking_url }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Track</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Returns --}}
            @if ($order->returnRequests->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-return-left"></i> Returns
                    </div>
                    <div class="card-body">
                        @foreach ($order->returnRequests as $rr)
                            <div class="border rounded p-2 mb-2">
                                <strong>Return #{{ $rr->id }}</strong>
                                <span class="badge bg-{{ $rr->status === 'approved' ? 'success' : ($rr->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($rr->status) }}</span>
                                <p class="small text-muted mb-0 mt-1">{{ $rr->reason }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Coupon --}}
            @if ($order->coupon)
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-ticket-perforated"></i> Coupon Applied
                    </div>
                    <div class="card-body">
                        <code>{{ $order->coupon_code_snapshot ?? $order->coupon->code }}</code>
                        — {{ $order->coupon->type }} {{ $order->coupon->value }}
                    </div>
                </div>
            @endif

            {{-- UTM / Attribution --}}
            @if ($order->utm_source || $order->fbclid || $order->gclid)
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up-arrow"></i> Attribution
                    </div>
                    <div class="card-body">
                        <div class="row g-2 small">
                            @if ($order->utm_source)<div class="col-6 col-md-4"><strong>Source:</strong> {{ $order->utm_source }}</div>@endif
                            @if ($order->utm_medium)<div class="col-6 col-md-4"><strong>Medium:</strong> {{ $order->utm_medium }}</div>@endif
                            @if ($order->utm_campaign)<div class="col-6 col-md-4"><strong>Campaign:</strong> {{ $order->utm_campaign }}</div>@endif
                            @if ($order->fbclid)<div class="col-12"><strong>FBCLID:</strong> <code>{{ $order->fbclid }}</code></div>@endif
                            @if ($order->gclid)<div class="col-12"><strong>GCLID:</strong> <code>{{ $order->gclid }}</code></div>@endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Right Column ──────────────────────────────────── --}}
        <div class="col-lg-4">
            {{-- Customer --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-person"></i> Customer</div>
                <div class="card-body">
                    <p class="fw-semibold mb-1">{{ $order->customer_name }}</p>
                    <p class="small text-muted mb-1"><i class="bi bi-telephone"></i> {{ $order->phone }}</p>
                    <p class="small text-muted mb-0"><i class="bi bi-envelope"></i> {{ $order->email }}</p>
                </div>
            </div>

            {{-- Address --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-geo-alt"></i> Shipping Address</div>
                <div class="card-body small">
                    {{ $order->address_line1 }}<br>
                    @if ($order->address_line2){{ $order->address_line2 }}<br>@endif
                    {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}<br>
                    {{ $order->country ?? 'India' }}
                </div>
            </div>

            {{-- Notes --}}
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-sticky"></i> Admin Notes</div>
                <div class="card-body small">
                    {{ $order->notes ?: 'No notes yet.' }}
                </div>
            </div>

            {{-- ═══ STATUS UPDATE FORM ══════════════════════════ --}}
            @php $next = $order->nextStatuses(); @endphp
            @if (count($next) > 0)
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white"><i class="bi bi-arrow-right-circle"></i> Update Status</div>
                    <div class="card-body">
                        <form action="{{ route('admin.orders.update', $order) }}" method="post">
                            @csrf @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Move to:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($next as $ns)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="order_status" id="status_{{ $ns }}" value="{{ $ns }}" {{ $loop->first ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status_{{ $ns }}">
                                                <span class="badge bg-{{ \App\Models\Order::STATUS_LABELS[$ns]['color'] ?? 'secondary' }}">
                                                    {{ \App\Models\Order::STATUS_LABELS[$ns]['label'] ?? ucfirst($ns) }}
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Show tracking fields when shipping is an option --}}
                            @if (in_array('shipped', $next, true))
                                <div id="shippingFields">
                                    <div class="mb-2">
                                        <label class="form-label small">AWB / Tracking No.</label>
                                        <input type="text" name="awb" class="form-control form-control-sm" placeholder="Enter AWB number">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Tracking URL</label>
                                        <input type="url" name="tracking_url" class="form-control form-control-sm" placeholder="https://...">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Carrier</label>
                                        <select name="carrier" class="form-select form-select-sm">
                                            <option value="manual">Manual</option>
                                            <option value="shiprocket">Shiprocket</option>
                                            <option value="delhivery">Delhivery</option>
                                            <option value="bluedart">BlueDart</option>
                                            <option value="dtdc">DTDC</option>
                                            <option value="india_post">India Post</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label small">Admin Notes (optional)</label>
                                <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Internal note…">{{ $order->notes }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-check2-circle"></i> Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center text-muted small py-3">
                        <i class="bi bi-check-circle" style="font-size:1.5rem;"></i><br>
                        This order has reached its final status.
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
