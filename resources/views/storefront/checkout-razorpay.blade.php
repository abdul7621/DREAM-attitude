@extends('layouts.storefront')

@section('title', 'Pay now')

@section('content')
    <h1 class="h3 mb-4">Complete payment</h1>
    <p class="text-muted">Order <strong>{{ $order->order_number }}</strong> · Total <strong>₹{{ number_format((float) $order->grand_total, 2) }}</strong></p>
    <p class="mb-4">You will be redirected to Razorpay secure checkout.</p>
    <button type="button" class="btn btn-primary btn-lg" id="payBtn">Pay ₹{{ number_format((float) $order->grand_total, 2) }}</button>

    <form id="payForm" action="{{ route('payments.razorpay.verify') }}" method="post" class="d-none">
        @csrf
        <input type="hidden" name="razorpay_order_id" id="ro">
        <input type="hidden" name="razorpay_payment_id" id="rp">
        <input type="hidden" name="razorpay_signature" id="rs">
    </form>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    const key = @json($razorpayKey);
    const amount = {{ (int) $amountPaise }};
    const rzOrderId = @json($order->razorpay_order_id);
    const btn = document.getElementById('payBtn');
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const options = {
            key: key,
            amount: amount,
            currency: 'INR',
            name: @json(config('app.name')),
            description: @json('Order '.$order->order_number),
            order_id: rzOrderId,
            handler: function (response) {
                document.getElementById('ro').value = response.razorpay_order_id;
                document.getElementById('rp').value = response.razorpay_payment_id;
                document.getElementById('rs').value = response.razorpay_signature;
                document.getElementById('payForm').submit();
            },
            prefill: {
                name: @json($customerName),
                email: @json($customerEmail ?? ''),
                contact: @json($customerPhone ?? '')
            },
            theme: { color: '#0d6efd' }
        };
        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function () {
            alert('Payment failed. You can try again.');
        });
        rzp.open();
    });
})();
</script>
@endpush
