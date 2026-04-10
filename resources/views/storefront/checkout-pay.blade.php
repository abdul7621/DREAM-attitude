@extends('layouts.storefront')

@section('title', 'Pay now')

@section('content')
    <h1 class="h3 mb-4">Complete payment</h1>
    <p class="text-muted">Order <strong>{{ $order->order_number }}</strong> · Total <strong>₹{{ number_format((float) $order->grand_total, 2) }}</strong></p>
    
    @if($gateway === 'razorpay')
        <p class="mb-4">You will be redirected to Razorpay secure checkout.</p>
        <button type="button" class="btn btn-primary btn-lg" id="payBtn">Pay ₹{{ number_format((float) $order->grand_total, 2) }}</button>

        <form id="payForm" action="{{ route('payments.verify') }}" method="post" class="d-none">
            @csrf
            <input type="hidden" name="razorpay_order_id" id="ro">
            <input type="hidden" name="razorpay_payment_id" id="rp">
            <input type="hidden" name="razorpay_signature" id="rs">
        </form>

        @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
        (function () {
            const paymentData = @json($paymentData);
            const rzOrderId = paymentData.provider_order_id;
            const btn = document.getElementById('payBtn');
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const options = {
                    key: paymentData.key,
                    amount: paymentData.amount,
                    currency: paymentData.currency,
                    name: paymentData.name,
                    description: paymentData.description,
                    order_id: rzOrderId,
                    handler: function (response) {
                        document.getElementById('ro').value = response.razorpay_order_id;
                        document.getElementById('rp').value = response.razorpay_payment_id;
                        document.getElementById('rs').value = response.razorpay_signature;
                        document.getElementById('payForm').submit();
                    },
                    prefill: {
                        name: @json($order->customer_name),
                        email: @json($order->email ?? ''),
                        contact: @json($order->phone ?? '')
                    },
                    theme: { color: '#0d6efd' }
                };
                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function () {
                    if (window.Store) {
                        Store.emit('toast', { type: 'error', message: 'Payment failed' });
                    } else {
                        alert('Payment failed. You can try again.');
                    }
                });
                rzp.open();
            });
            // Auto click if we want smoothly
            btn.click();
        })();
        </script>
        @endpush

    @elseif($gateway === 'payu')
        <p class="mb-4">Redirecting to PayU secure checkout...</p>
        <form id="payuForm" action="{{ $paymentData['payment_url'] }}" method="post" class="d-none">
            <input type="hidden" name="key" value="{{ $paymentData['key'] }}" />
            <input type="hidden" name="txnid" value="{{ $paymentData['provider_order_id'] }}" />
            <input type="hidden" name="productinfo" value="{{ $paymentData['productinfo'] }}" />
            <input type="hidden" name="amount" value="{{ $paymentData['amount'] }}" />
            <input type="hidden" name="email" value="{{ $paymentData['email'] }}" />
            <input type="hidden" name="firstname" value="{{ $paymentData['firstname'] }}" />
            <input type="hidden" name="surl" value="{{ $paymentData['surl'] }}" />
            <input type="hidden" name="furl" value="{{ $paymentData['furl'] }}" />
            <input type="hidden" name="phone" value="{{ $paymentData['phone'] }}" />
            <input type="hidden" name="hash" value="{{ $paymentData['hash'] }}" />
        </form>
        @push('scripts')
        <script>
            document.getElementById('payuForm').submit();
        </script>
        @endpush

    @else
        <p class="mb-4">Redirecting to secure payment gateway...</p>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        @push('scripts')
        <script>
            window.location.href = @json($paymentData['payment_url'] ?? route('checkout.create'));
        </script>
        @endpush
    @endif

@endsection
