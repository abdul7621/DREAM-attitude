@extends('layouts.storefront')

@section('title', 'Cart')

@section('content')
    <h1 class="h3 mb-4">Shopping cart</h1>
    @if ($lines->isEmpty())
        <p class="text-muted">Your cart is empty.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Continue shopping</a>
    @else
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table align-middle mb-0">
                <thead><tr><th>Product</th><th class="text-end">Price</th><th class="text-center" style="width:8rem;">Qty</th><th class="text-end">Line</th><th></th></tr></thead>
                <tbody>
                @foreach ($lines as $row)
                    @php
                        $item = $row['item'];
                        $variant = $row['variant'];
                        $product = $row['product'];
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $product->name }}</div>
                            <div class="small text-muted">{{ $variant->title }} @if ($variant->sku) · {{ $variant->sku }} @endif</div>
                        </td>
                        <td class="text-end">₹{{ number_format((float) $row['unit_price'], 2) }}</td>
                        <td>
                            <form action="{{ route('cart.items.update', $item) }}" method="post" class="d-flex gap-1 justify-content-center align-items-center">
                                @csrf
                                @method('PUT')
                                <input type="number" name="qty" value="{{ $item->qty }}" min="0" max="9999" class="form-control form-control-sm" style="width:5rem;">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Update</button>
                            </form>
                        </td>
                        <td class="text-end">₹{{ number_format((float) $row['line_total'], 2) }}</td>
                        <td class="text-end">
                            <form action="{{ route('cart.items.destroy', $item) }}" method="post" onsubmit="return confirm('Remove this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="bg-white shadow-sm rounded p-3">
                    <h2 class="h6 mb-3">Coupon</h2>
                    @if ($errors->has('coupon'))
                        <div class="alert alert-danger py-2 small">{{ $errors->first('coupon') }}</div>
                    @endif
                    @if ($totals['coupon'])
                        <p class="mb-2 small text-success">{{ __('Applied: :code', ['code' => $totals['coupon']->code]) }}</p>
                        <form action="{{ route('cart.coupon.remove') }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Remove coupon') }}</button>
                        </form>
                    @else
                        <form action="{{ route('cart.coupon.apply') }}" method="post" class="d-flex gap-2 flex-wrap">
                            @csrf
                            <input type="text" name="code" value="{{ old('code') }}" class="form-control form-control-sm" placeholder="{{ __('Coupon code') }}" maxlength="64" style="max-width:14rem;">
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Apply') }}</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="bg-white shadow-sm rounded p-3">
                    <h2 class="h6 mb-3">Summary</h2>
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ __('Subtotal') }}</span>
                        <span>₹{{ number_format((float) $totals['subtotal'], 2) }}</span>
                    </div>
                    @if ((float) $totals['discount'] > 0)
                        <div class="d-flex justify-content-between small mb-1 text-success">
                            <span>{{ __('Discount') }}</span>
                            <span>−₹{{ number_format((float) $totals['discount'], 2) }}</span>
                        </div>
                    @endif
                    <p class="small text-muted mb-2">{{ __('Shipping is calculated at checkout using your PIN code.') }}</p>
                    <div class="d-flex justify-content-between fw-semibold border-top pt-2">
                        <span>{{ __('Estimated (excl. shipping)') }}</span>
                        <span>₹{{ number_format((float) $totals['grand'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">{{ __('Continue shopping') }}</a>
            <a href="{{ route('checkout.create') }}" class="btn btn-primary btn-lg">{{ __('Checkout') }}</a>
        </div>
    @endif
@endsection

@if (session('analytics_add_to_cart'))
@push('scripts')
@php $a = session('analytics_add_to_cart'); @endphp
<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({ ecommerce: null });
dataLayer.push({
    event: 'add_to_cart',
    ecommerce: {
        currency: @json($a['currency']),
        value: {{ $a['value'] }},
        items: @json($a['items'])
    }
});
@if (config('commerce.meta.pixel_id'))
if (typeof fbq === 'function') {
    fbq('track', 'AddToCart', {
        value: {{ $a['value'] }},
        currency: @json($a['currency']),
        content_ids: [@json($a['items'][0]['item_id'] ?? '')],
        content_type: 'product'
    });
}
@endif
</script>
@endpush
@endif
