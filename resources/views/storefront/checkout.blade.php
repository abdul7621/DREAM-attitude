@extends('layouts.storefront')

@section('title', 'Checkout')

@section('content')
<div class="sf-checkout-page pb-5 bg-light min-vh-100">
    {{-- Top Header / Progress Indicator --}}
    <div class="bg-white border-bottom mb-4 shadow-sm">
        <div class="container py-3">
            <div class="d-flex justify-content-center align-items-center gap-2 gap-md-4">
                <div class="d-flex flex-column align-items-center">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;"><i class="bi bi-check fs-5"></i></div>
                    <small class="fw-semibold mt-1">Cart</small>
                </div>
                <div class="bg-success rounded" style="height:4px;width:50px;margin-top:-20px;"></div>
                <div class="d-flex flex-column align-items-center">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:36px;height:36px;background-color: var(--sf-primary, #000);">2</div>
                    <small class="fw-bold mt-1" style="color: var(--sf-primary, #000);">Shipping</small>
                </div>
                <div class="bg-secondary bg-opacity-25 rounded" style="height:4px;width:50px;margin-top:-20px;"></div>
                <div class="d-flex flex-column align-items-center">
                    <div class="rounded-circle bg-light border text-muted d-flex align-items-center justify-content-center fw-bold" style="width:36px;height:36px;">3</div>
                    <small class="text-muted mt-1">Payment</small>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4 flex-lg-row-reverse">
            {{-- ── Right: Order Summary ────────────────────────────────── --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3 fw-bold fs-5 border-bottom"><i class="bi bi-bag-check me-2"></i> Order Summary</div>
                    <div class="card-body">
                        <ul class="list-unstyled small mb-4">
                            @foreach ($lines as $row)
                                <li class="d-flex justify-content-between py-2 border-bottom align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <img src="{{ $row['product']->primaryImage() ? asset('storage/'.$row['product']->primaryImage()->path) : 'https://placehold.co/50' }}" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">{{ $row['item']->qty }}</span>
                                        </div>
                                        <span class="fw-semibold">{{ $row['product']->name }} <span class="d-block text-muted small mt-1">{{ Str::limit($row['variant']->title, 20) }}</span></span>
                                    </div>
                                    <span class="fw-bold">₹{{ number_format((float) $row['line_total'], 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between mt-3 text-secondary">
                            <span>Subtotal</span>
                            <span class="fw-semibold">₹{{ number_format((float) $totals['subtotal'], 2) }}</span>
                        </div>
                        @if ((float) $totals['discount'] > 0)
                            <div class="d-flex justify-content-between text-success mt-2">
                                <span>Discount</span>
                                <span class="fw-semibold">−₹{{ number_format((float) $totals['discount'], 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mt-2 text-secondary">
                            <span>Shipping</span>
                            @if ((float) $totals['shipping'] === 0.0)
                                <span class="text-success fw-semibold">FREE</span>
                            @else
                                <span class="fw-semibold">₹{{ number_format((float) $totals['shipping'], 2) }}</span>
                            @endif
                        </div>
                        @if ((float) $totals['tax'] > 0)
                            <div class="d-flex justify-content-between mt-2 text-secondary">
                                <span>Tax</span>
                                <span class="fw-semibold">₹{{ number_format((float) $totals['tax'], 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mt-3 fw-bold fs-5 border-top pt-3 text-dark">
                            <span>Total</span>
                            <span>₹{{ number_format((float) $totals['grand'], 2) }}</span>
                        </div>
                        @if ($totals['coupon'])
                            <p class="small text-muted mb-0 mt-3 bg-light p-2 rounded"><i class="bi bi-tag-fill text-success me-1"></i> {{ __('Coupon :code applied.', ['code' => $totals['coupon']->code]) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Left: Checkout Form ─────────────────────────────────── --}}
            <div class="col-lg-7">
                <form id="checkout-form" action="{{ route('checkout.store') }}" method="post">
                    @csrf
                    
                    {{-- Contact Info --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 fw-bold fs-5 border-bottom"><i class="bi bi-person-circle me-2"></i> Contact Information</div>
                        <div class="card-body">
                           <div class="row g-3">
                                <div class="col-md-6">
                                    <x-sf-input type="text" name="customer_name" id="customer_name" label="Full Name *" value="{{ old('customer_name') }}" required />
                                </div>
                                <div class="col-md-6">
                                    <x-sf-input type="tel" name="phone" id="phone" label="Phone Number *" value="{{ old('phone') }}" required />
                                    <small class="text-danger d-none" id="phone_err">Enter a valid 10-digit number</small>
                                </div>
                                <div class="col-12">
                                    <x-sf-input type="email" name="email" id="email" label="Email (Optional, to text updates)" value="{{ old('email') }}" />
                                </div>
                           </div>
                        </div>
                    </div>

                    {{-- Shipping Info --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 fw-bold fs-5 border-bottom"><i class="bi bi-truck me-2"></i> Shipping Address</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="position-relative">
                                        <x-sf-input type="text" id="postal_code" name="postal_code" label="PIN Code *" value="{{ old('postal_code') }}" required maxlength="6" inputmode="numeric" />
                                        <div class="spinner-border spinner-border-sm text-primary position-absolute top-50 end-0 translate-middle mt-2 d-none" id="pin_spinner" role="status"></div>
                                    </div>
                                    <small class="text-danger d-none" id="pin_err">Enter a valid 6-digit PIN code</small>
                                </div>
                                <div class="col-md-4">
                                    <x-sf-input type="text" id="city" name="city" label="City *" value="{{ old('city') }}" required readonly />
                                </div>
                                <div class="col-md-4">
                                    <x-sf-input type="text" id="state" name="state" label="State *" value="{{ old('state') }}" required readonly />
                                </div>
                                <div class="col-12">
                                    <x-sf-input type="text" name="address_line1" id="address_line1" label="House/Flat No., Building Name *" value="{{ old('address_line1') }}" required />
                                </div>
                                <div class="col-12">
                                    <x-sf-input type="text" name="address_line2" id="address_line2" label="Street/Area/Landmark (Optional)" value="{{ old('address_line2') }}" />
                                </div>
                                <div class="col-12 d-none">
                                    <input type="hidden" name="country" value="IN">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Toggle --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 fw-bold fs-5 border-bottom"><i class="bi bi-receipt me-2"></i> Billing Address</div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="sameAsShipping" value="1" checked style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label ps-2 mt-1 fw-medium" for="sameAsShipping" style="cursor: pointer;">Billing address is same as shipping address</label>
                            </div>
                            
                            <div id="billingAddressForm" class="d-none mt-4 pt-3 border-top">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <x-sf-input type="text" name="billing_customer_name" id="billing_customer_name" label="Billing Name" value="{{ old('billing_customer_name') }}" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-sf-input type="text" name="billing_postal_code" id="billing_postal_code" label="PIN Code" value="{{ old('billing_postal_code') }}" maxlength="6" inputmode="numeric" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-sf-input type="text" name="billing_city" id="billing_city" label="City" value="{{ old('billing_city') }}" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-sf-input type="text" name="billing_state" id="billing_state" label="State" value="{{ old('billing_state') }}" />
                                    </div>
                                    <div class="col-12">
                                        <x-sf-input type="text" name="billing_address_line1" id="billing_address_line1" label="House/Flat No." value="{{ old('billing_address_line1') }}" />
                                    </div>
                                    <div class="col-12">
                                        <x-sf-input type="text" name="billing_address_line2" id="billing_address_line2" label="Street/Area (Optional)" value="{{ old('billing_address_line2') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 fw-bold fs-5 border-bottom"><i class="bi bi-credit-card me-2"></i> Payment Method</div>
                        <div class="card-body p-3">
                            <x-sf-card class="mb-3 payment-card" style="cursor: pointer;" id="card_prepaid">
                                <label class="d-flex gap-3 align-items-start m-0 w-100" style="cursor: pointer;">
                                    <input class="form-check-input mt-1 shadow-sm" type="radio" name="payment_method" value="razorpay" @checked(old('payment_method', 'razorpay') === 'razorpay') style="width: 1.5em; height: 1.5em;">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-6">Pay Online (UPI, Cards, NetBanking)</span>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2"><i class="bi bi-lightning-fill"></i> Recommended</span>
                                        </div>
                                        <p class="text-secondary small mt-1 mb-2">Safe and secure payments powered by Razorpay.</p>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-light text-dark border"><i class="bi bi-phone"></i> UPI</span>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-credit-card"></i> Cards</span>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-bank"></i> NetBanking</span>
                                        </div>
                                    </div>
                                </label>
                            </x-sf-card>
                            
                            <x-sf-card class="payment-card" style="cursor: pointer;" id="card_cod">
                                <label class="d-flex gap-3 align-items-start m-0 w-100" style="cursor: pointer;">
                                    <input class="form-check-input mt-1 shadow-sm" type="radio" name="payment_method" value="cod" @checked(old('payment_method') === 'cod') style="width: 1.5em; height: 1.5em;">
                                    <div class="w-100">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold fs-6">Cash on Delivery (COD)</span>
                                            <span class="fw-semibold text-secondary small">₹0 Additional Fee</span>
                                        </div>
                                        <p class="text-secondary small mt-1 mb-0">Pay when your order is delivered to you.</p>
                                    </div>
                                </label>
                            </x-sf-card>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg w-100 py-3 fw-bold fs-5 shadow sf-hover-lift" id="submitBtn" style="background-color: var(--sf-primary, #000); color: #fff;">
                        Place Order <i class="bi bi-arrow-right ms-2"></i>
                    </button>

                    <div class="mt-4 p-3 bg-white border rounded shadow-sm">
                        <x-trust-badge icon="bi-shield-check" title="Secure Checkout" text="100% safe & protected payments with SSL encryption" />
                        <x-trust-badge icon="bi-truck" title="Fast Delivery" text="Estimated: 2-5 Business Days" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.payment-card { transition: border-color 0.2s, background-color 0.2s; border: 2px solid transparent; }
.payment-card.selected { border-color: var(--sf-primary, #000); background-color: #f8f9fa; }
.sf-inline-err-text { font-size: 0.85rem; color: #dc3545; display: block; margin-top: 0.25rem; }
</style>
<script>
(function () {
    const pinInput = document.getElementById('postal_code') || document.querySelector('input[name="postal_code"]');
    const cityInput = document.getElementById('city') || document.querySelector('input[name="city"]');
    const stateInput = document.getElementById('state') || document.querySelector('input[name="state"]');
    const spinner = document.getElementById('pin_spinner');
    const phoneInput = document.getElementById('phone') || document.querySelector('input[name="phone"]');
    const nameInput = document.getElementById('customer_name') || document.querySelector('input[name="customer_name"]');
    const addressInput = document.getElementById('address_line1') || document.querySelector('input[name="address_line1"]');
    
    // Payment Card UI Sync
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    function updatePaymentCards() {
        document.querySelectorAll('.payment-card').forEach(c => c.classList.remove('selected'));
        const checked = document.querySelector('input[name="payment_method"]:checked');
        if (checked) {
            checked.closest('.payment-card').classList.add('selected');
        }
    }
    paymentRadios.forEach(r => r.addEventListener('change', updatePaymentCards));
    updatePaymentCards();

    // Billing Toggle Sync
    const sameAsShipping = document.getElementById('sameAsShipping');
    const billingForm = document.getElementById('billingAddressForm');
    if(sameAsShipping) {
        sameAsShipping.addEventListener('change', function() {
            if(this.checked) {
                billingForm.classList.add('d-none');
            } else {
                billingForm.classList.remove('d-none');
            }
        });
        // Init state
        if(!sameAsShipping.checked) billingForm.classList.remove('d-none');
    }

    // Pincode API
    if (pinInput && cityInput && stateInput) {
        pinInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                // Fetch
                spinner.classList.remove('d-none');
                pinInput.setAttribute('disabled', 'true');
                
                fetch(`https://api.postalpincode.in/pincode/${this.value}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data[0].Status === 'Success') {
                            const info = data[0].PostOffice[0];
                            cityInput.value = info.District;
                            stateInput.value = info.State;
                            if (window.Store) Store.emit('toast', {type:'success', message: `Delivering to ${info.District}, ${info.State}`});
                            
                            // Remove readonly
                            cityInput.setAttribute('readonly', 'true');
                            stateInput.setAttribute('readonly', 'true');
                            document.getElementById('pin_err')?.classList.add('d-none');
                            pinInput.classList.remove('is-invalid');
                        } else {
                            cityInput.value = '';
                            stateInput.value = '';
                            cityInput.removeAttribute('readonly');
                            stateInput.removeAttribute('readonly');
                            if (window.Store) Store.emit('toast', {type:'error', message: 'Invalid PIN code.'});
                            document.getElementById('pin_err')?.classList.remove('d-none');
                            pinInput.classList.add('is-invalid');
                        }
                    })
                    .catch(err => {
                        cityInput.removeAttribute('readonly');
                        stateInput.removeAttribute('readonly');
                    })
                    .finally(() => {
                        spinner.classList.add('d-none');
                        pinInput.removeAttribute('disabled');
                        pinInput.focus();
                    });
            } else {
                document.getElementById('pin_err')?.classList.add('d-none');
                pinInput.classList.remove('is-invalid');
            }
        });
        
        // Initial check if validation failed previously
        if(pinInput.value.length === 6 && !cityInput.value) {
            pinInput.dispatchEvent(new Event('input'));
        }
    }

    // Inline blur validations
    function showError(input, text) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        let err = input.nextElementSibling;
        if(!err || !err.classList.contains('sf-inline-err-text')) {
             err = document.createElement('span');
             err.className = 'sf-inline-err-text';
             input.parentNode.appendChild(err);
        }
        err.innerText = text;
    }
    function hideError(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        const err = input.parentNode.querySelector('.sf-inline-err-text');
        if(err) err.remove();
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            const val = this.value.trim().replace(/[^0-9]/g, '');
            if (val.length < 10) {
                showError(this, 'Enter a valid 10-digit phone number');
            } else {
                hideError(this);
            }
        });
    }

    if (nameInput) {
        nameInput.addEventListener('blur', function() {
            if (this.value.trim().length < 3) {
                showError(this, 'Please enter your full name');
            } else {
                hideError(this);
            }
        });
    }

    if (addressInput) {
        addressInput.addEventListener('blur', function() {
            if (this.value.trim().length < 5) {
                showError(this, 'Please enter a valid complete address');
            } else {
                hideError(this);
            }
        });
    }

    if (pinInput) {
        pinInput.addEventListener('blur', function() {
            if (this.value.trim().length !== 6) {
                showError(this, 'PIN code must be exactly 6 digits');
            } else if (!this.classList.contains('is-invalid')) {
                hideError(this);
            }
        });
    }

    // Analytics and Form Submit
    const form = document.getElementById('checkout-form');
    if (!form) return;
    
    form.addEventListener('submit', function (e) {
        // Run validations manually
        let hasError = false;
        
        if (phoneInput && phoneInput.value.trim().length < 10) {
            showError(phoneInput, 'Enter a valid 10-digit phone number');
            hasError = true;
        }
        if (nameInput && nameInput.value.trim().length < 3) {
            showError(nameInput, 'Please enter your full name');
            hasError = true;
        }
        if (addressInput && addressInput.value.trim().length < 5) {
            showError(addressInput, 'Please enter a valid complete address');
            hasError = true;
        }
        if (pinInput && pinInput.value.trim().length !== 6) {
            showError(pinInput, 'PIN code must be exactly 6 digits');
            hasError = true;
        }

        if(hasError) {
            e.preventDefault();
            if (window.Store) Store.emit('toast', {type:'error', message: 'Please correct the highlighted errors.'});
            return;
        }

        // Sync Billing Data
        if(sameAsShipping && sameAsShipping.checked) {
            document.getElementById('billing_customer_name').value = document.getElementById('customer_name').value;
            document.getElementById('billing_postal_code').value = document.getElementById('postal_code').value;
            document.getElementById('billing_city').value = document.getElementById('city').value;
            document.getElementById('billing_state').value = document.getElementById('state').value;
            document.getElementById('billing_address_line1').value = document.getElementById('address_line1').value;
        }
        
        const btn = document.getElementById('submitBtn');
        if(btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            btn.classList.add('disabled');
        }

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
