@extends('layouts.storefront')

@section('title', 'Checkout')

@section('content')
    <h1 class="h3 mb-4">Checkout</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <form id="checkout-form" action="{{ route('checkout.store') }}" method="post" class="bg-white p-3 p-md-4 rounded shadow-sm">
                @csrf
                <h2 class="h6 mb-3">Shipping</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Full name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required maxlength="32">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address line 1 *</label>
                        <input type="text" name="address_line1" value="{{ old('address_line1') }}" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address line 2</label>
                        <input type="text" name="address_line2" value="{{ old('address_line2') }}" class="form-control" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">City *</label>
                        <input type="text" name="city" value="{{ old('city') }}" class="form-control" required maxlength="128">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">State *</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="form-control" required maxlength="128">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">PIN code *</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" class="form-control" required maxlength="16">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" value="{{ old('country', 'IN') }}" class="form-control" maxlength="8">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Order notes</label>
                        <textarea name="notes" rows="2" class="form-control" maxlength="2000">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <hr class="my-4">
                <h2 class="h6 mb-3">Payment</h2>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="payment_method" id="payCod" value="cod" @checked(old('payment_method', 'cod') === 'cod') required>
                    <label class="form-check-label" for="payCod">Cash on delivery (COD)</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="payment_method" id="payRz" value="razorpay" @checked(old('payment_method') === 'razorpay')>
                    <label class="form-check-label" for="payRz">Pay online (Razorpay)</label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Place order</button>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="bg-white p-3 rounded shadow-sm">
                <h2 class="h6 mb-3">Order summary</h2>
                <ul class="list-unstyled small mb-0">
                    @foreach ($lines as $row)
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span>{{ $row['product']->name }} × {{ $row['item']->qty }}</span>
                            <span>₹{{ number_format((float) $row['line_total'], 2) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex justify-content-between mt-3 small">
                    <span>Subtotal</span>
                    <span>₹{{ number_format((float) $totals['subtotal'], 2) }}</span>
                </div>
                @if ((float) $totals['discount'] > 0)
                    <div class="d-flex justify-content-between small text-success">
                        <span>Discount</span>
                        <span>−₹{{ number_format((float) $totals['discount'], 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between small">
                    <span>Shipping</span>
                    <span>₹{{ number_format((float) $totals['shipping'], 2) }}</span>
                </div>
                @if ((float) $totals['tax'] > 0)
                    <div class="d-flex justify-content-between small">
                        <span>Tax</span>
                        <span>₹{{ number_format((float) $totals['tax'], 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between mt-2 fw-semibold border-top pt-2">
                    <span>Total</span>
                    <span>₹{{ number_format((float) $totals['grand'], 2) }}</span>
                </div>
                @if ($totals['coupon'])
                    <p class="small text-muted mb-0 mt-2">{{ __('Coupon :code applied.', ['code' => $totals['coupon']->code]) }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('checkout-form');
    if (!form) return;
    form.addEventListener('submit', function () {
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({ ecommerce: null });
        dataLayer.push({
            event: 'begin_checkout',
            ecommerce: {
                currency: '{{ config('commerce.currency', 'INR') }}',
                value: {{ (float) $totals['grand'] }},
                items: [
                    @foreach ($lines as $row)
                    {
                        item_id: @json($row['variant']->sku ?: 'v'.$row['variant']->id),
                        item_name: @json($row['product']->name),
                        price: {{ (float) $row['unit_price'] }},
                        quantity: {{ (int) $row['item']->qty }}
                    }@if(! $loop->last),@endif
                    @endforeach
                ]
            }
        });
        @if (config('commerce.meta.pixel_id'))
        if (typeof fbq === 'function') {
            fbq('track', 'InitiateCheckout', {
                value: {{ (float) $totals['grand'] }},
                currency: '{{ config('commerce.currency', 'INR') }}',
                num_items: {{ (int) $lines->sum(fn ($r) => $r['item']->qty) }}
            });
        }
        @endif
    });
})();
</script>
@endpush
