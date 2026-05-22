@extends('layouts.storefront')

@section('title', 'Checkout')

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush
@section('content')
<div class="sf-chk-page">
    {{-- Top Header / Progress Indicator --}}
    <div class="sf-chk-header">
        <div class="sf-container">
            <div class="sf-chk-step-wrap">
                <div class="sf-chk-step-col">
                    <div class="sf-chk-step-circle-done"><i class="bi bi-check" style="font-size: 20px;"></i></div>
                    <small class="sf-chk-step-label-done">Cart</small>
                </div>
                <div class="sf-chk-step-line-done"></div>
                <div class="sf-chk-step-col">
                    <div class="sf-chk-step-circle-active">2</div>
                    <small class="sf-chk-step-label-active">Shipping</small>
                </div>
                <div class="sf-chk-step-line-active"></div>
                <div class="sf-chk-step-col">
                    <div class="sf-chk-step-circle-next">3</div>
                    <small class="sf-chk-step-label-next">Payment</small>
                </div>
            </div>
        </div>
    </div>

    <div class="sf-container">
        <div class="sf-cart-layout">
            {{-- ── Right: Order Summary ────────────────────────────────── --}}
            <div style="order: 2;">
                <div class="sf-cart-summary" style="position: sticky; top: 20px;">
                    <div class="sf-chk-section-title"><i class="bi bi-bag-check me-2"></i> Order Summary</div>
                    <div>
                        <ul class="sf-chk-summary-list">
                            @foreach ($lines as $row)
                                <li class="sf-chk-summary-item">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="sf-chk-summary-img-wrap">
                                            <img src="{{ $row['product']->primaryImage() ? asset('storage/'.$row['product']->primaryImage()->path) : 'https://placehold.co/50' }}" class="sf-chk-summary-img">
                                            <span class="sf-chk-summary-qty">{{ $row['item']->qty }}</span>
                                        </div>
                                        <span style="font-weight: 500; color: var(--color-text-primary);">{{ $row['product']->name }} <span style="display: block; color: var(--color-text-muted); font-size: 11px; margin-top: 4px;">{{ Str::limit($row['variant']->title, 20) }}</span></span>
                                    </div>
                                    <span style="text-align: right;">
                                        @php
                                            $compareAt = $row['variant']->compare_at_price;
                                            $unitPrice = (float) $row['unit_price'];
                                            $showMrp = $compareAt && (float) $compareAt > $unitPrice;
                                        @endphp
                                        @if($showMrp)
                                            <span style="text-decoration: line-through; color: var(--color-text-muted); font-size: 11px; display: block;">₹{{ number_format((float) $compareAt * $row['item']->qty, 2) }}</span>
                                        @endif
                                        <span style="font-weight: 600; color: var(--color-gold);">₹{{ number_format((float) $row['line_total'], 2) }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 13px; color: var(--color-text-secondary);">
                            <span>Subtotal</span>
                            <span style="font-weight: 500;">₹{{ number_format((float) $totals['subtotal'], 2) }}</span>
                        </div>
                        @if ((float) $totals['discount'] > 0)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 13px; color: var(--color-success);">
                                <span>Discount</span>
                                <span style="font-weight: 600;">−₹{{ number_format((float) $totals['discount'], 2) }}</span>
                            </div>
                        @endif
                        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 13px; color: var(--color-text-secondary);">
                            <span>Shipping</span>
                            <span id="summary-shipping-val">
                            @if ((float) $totals['shipping'] === 0.0)
                                <span style="color: var(--color-success); font-weight: 600;">FREE (Online Payment)</span>
                            @else
                                <span style="font-weight: 500;">₹{{ number_format((float) $totals['shipping'], 2) }}</span>
                            @endif
                            </span>
                        </div>
                        @if ((float) $totals['tax'] > 0)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 13px; color: var(--color-text-secondary);">
                                <span>Tax ({{ app(\App\Services\SettingsService::class)->get('gst.inclusive', true) ? 'Incl.' : 'Excl.' }})</span>
                                <span style="font-weight: 500;">₹{{ number_format((float) $totals['tax'], 2) }}</span>
                            </div>
                        @endif
                        <div class="sf-cart-total">
                            <span>Total</span>
                            <span id="summary-grand-val">₹{{ number_format((float) $totals['grand'], 2) }}</span>
                        </div>
                        @if ($totals['coupon'])
                            <p style="font-size: 11px; color: var(--color-text-muted); margin: 16px 0 0 0; background: var(--color-bg-elevated); padding: 8px; border-radius: var(--radius-sm);"><i class="bi bi-tag-fill text-success me-1"></i> {{ __('Coupon :code applied.', ['code' => $totals['coupon']->code]) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Left: Checkout Form ─────────────────────────────────── --}}
            <div style="order: 1;">
                <form id="checkout-form" action="{{ route('checkout.store') }}" method="post" novalidate>
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">

                    @if ($errors->any())
                        <div class="sf-checkout-errors" role="alert" id="checkoutErrors">
                            <div class="error-title"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please fix the following errors:</div>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    {{-- Contact Info --}}
                    <div class="sf-chk-section-panel">
                        <div class="sf-chk-section-title"><i class="bi bi-person-circle me-2"></i> Contact Information</div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="sf-label">Phone Number *</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" autocomplete="tel" class="sf-input @error('phone') is-invalid @enderror" required placeholder="10-digit mobile number">
                                <small class="sf-inline-err-text d-none" id="phone_err">Enter a valid phone number</small>
                            </div>
                            <div class="col-md-6">
                                <label class="sf-label">Full Name *</label>
                                <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}" autocomplete="name" class="sf-input @error('customer_name') is-invalid @enderror" required>
                            </div>
                            <div class="col-md-6">
                                <label class="sf-label">Email (Optional)</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" class="sf-input @error('email') is-invalid @enderror">
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Info --}}
                    <div class="sf-chk-section-panel">
                        <div class="sf-chk-section-title"><i class="bi bi-truck me-2"></i> Shipping Address</div>
                        @auth
                        <div style="margin-bottom: 24px;" id="saved-addr-wrap">
                            <label class="sf-label">Saved Addresses</label>
                            <select class="sf-input" id="saved_address_select">
                                <option value="">Enter new address</option>
                            </select>
                        </div>
                        @endauth
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="sf-label">PIN Code *</label>
                                <div style="position: relative;">
                                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" class="sf-input @error('postal_code') is-invalid @enderror" required maxlength="6" inputmode="numeric" autocomplete="postal-code" pattern="[0-9]{6}">
                                    <div id="pin_spinner" class="d-none" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); width:16px; height:16px; border:2px solid var(--color-border); border-top-color:var(--color-gold); border-radius:50%; animation:spin 0.8s linear infinite;"></div>
                                </div>
                                <small class="sf-inline-err-text d-none" id="pin_err">Enter a valid 6-digit PIN code</small>
                            </div>
                            <div class="col-md-4">
                                <label class="sf-label">City *</label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" class="sf-input @error('city') is-invalid @enderror" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="sf-label">State *</label>
                                <input type="text" id="state" name="state" value="{{ old('state') }}" autocomplete="address-level1" class="sf-input @error('state') is-invalid @enderror" required readonly>
                            </div>
                            <div class="col-12">
                                <label class="sf-label">House/Flat No., Building Name *</label>
                                <input type="text" name="address_line1" id="address_line1" value="{{ old('address_line1') }}" autocomplete="address-line1" class="sf-input @error('address_line1') is-invalid @enderror" required>
                            </div>
                            <div class="col-12">
                                <label class="sf-label">Street/Area/Landmark (Optional)</label>
                                <input type="text" name="address_line2" id="address_line2" value="{{ old('address_line2') }}" class="sf-input">
                            </div>
                            <div style="display: none;">
                                <input type="hidden" name="country" value="IN">
                            </div>
                        </div>
                    </div>

                    {{-- Billing Toggle --}}
                    <div class="sf-chk-section-panel">
                        <div class="sf-chk-section-title"><i class="bi bi-receipt me-2"></i> Billing Address</div>
                        <div style="display: flex; align-items: center; gap: 8px;">
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
                    @php
                        $copy = app(\App\Services\SettingsService::class)->get('conversion_copy.checkout', config('commerce.conversion_copy.checkout'));
                    @endphp
                    <div class="sf-chk-section-panel">
                        <div class="sf-chk-section-title"><i class="bi bi-credit-card me-2"></i> Payment Method</div>
                        <div>
                            @php
                                $onlineGateways = collect($activeGateways ?? [])->where('name', '!=', 'cod');
                                $codGateway = collect($activeGateways ?? [])->firstWhere('name', 'cod');
                            @endphp

                            @if($onlineGateways->isNotEmpty())
                                @foreach($onlineGateways as $gw)
                                    <div class="payment-card" style="cursor: pointer; margin-bottom: 12px; padding: 16px; border: 1px solid var(--color-border); border-radius: var(--radius-md); transition: var(--transition);" id="card_{{ $gw->name }}">
                                        <label style="display: flex; gap: 16px; align-items: flex-start; cursor: pointer; margin: 0; width: 100%;">
                                            <input class="sf-checkout-radio" type="radio" name="payment_method" value="{{ $gw->name }}" @checked(old('payment_method', ($gw->is_default ? $gw->name : '')) === $gw->name) style="margin-top: 4px;">
                                            <div style="flex: 1;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="font-weight: 600; color: var(--color-text-primary);">{{ $gw->name === 'phonepe' ? 'Online Payment' : $gw->label }}</span>
                                                    @if((float) $totals['subtotal'] >= 500)
                                                        <span style="font-size: 11px; font-weight: 600; color: var(--color-success);">FREE Shipping</span>
                                                    @else
                                                        <span style="font-size: 11px; font-weight: 600; color: var(--color-text-muted);">+ ₹69.00 Shipping</span>
                                                    @endif
                                                </div>
                                                <p style="color: var(--color-text-muted); font-size: 12px; margin: 4px 0 0 0;">{{ in_array($gw->name, ['razorpay', 'phonepe']) ? 'UPI, Cards, NetBanking' : 'Pay via ' . $gw->label }}</p>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            @endif
                            
                            @if($codGateway)
                                <div class="payment-card" style="cursor: pointer; margin-bottom: 12px; padding: 16px; border: 1px solid var(--color-border); border-radius: var(--radius-md); transition: var(--transition);" id="card_cod">
                                    <label style="display: flex; gap: 16px; align-items: flex-start; cursor: pointer; margin: 0; width: 100%;">
                                        <input class="sf-checkout-radio" type="radio" name="payment_method" value="cod" @checked(old('payment_method', ($codGateway->is_default ? 'cod' : '')) === 'cod') style="margin-top: 4px;">
                                        <div style="flex: 1;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span style="font-weight: 600; color: var(--color-text-primary);">{{ $codGateway->label ?: 'Cash on Delivery (COD)' }}</span>
                                                <span style="font-size: 11px; font-weight: 600; color: var(--color-gold);">+ ₹69.00 Shipping</span>
                                            </div>
                                            <p style="color: var(--color-text-muted); font-size: 12px; margin: 4px 0 0 0;">Pay cash when your order is delivered to you (+ ₹69.00 shipping fee applies).</p>
                                        </div>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Checkout OS: Configurable Trust Section --}}
                    @php
                        $checkoutOs = config('commerce.conversion_engine.checkout_os', []);
                        $trustBadges = $checkoutOs['trust_badges'] ?? ['Secure Checkout', 'UPI/Cards', 'Fast Delivery'];
                        $isCodEnabled = $checkoutOs['cod_badge_enabled'] ?? true;
                    @endphp
                    
                    @if(session('offer_unlocked_freeship') || auth()->user()?->cart?->offer_claimed)
                    <div class="sf-chk-free-ship-toast">
                        <i class="bi bi-gift-fill me-2"></i> Free Prepaid Shipping Unlocked!
                    </div>
                    @endif

                    <div class="sf-chk-trust-row">
                        @foreach($trustBadges as $badge)
                            <span class="sf-chk-trust-badge">
                                <i class="bi bi-check-circle-fill" style="color: var(--color-success); font-size: 14px;"></i> {{ $badge }}
                            </span>
                        @endforeach
                        @if($isCodEnabled)
                            <span class="sf-chk-trust-badge">
                                <i class="bi bi-cash-coin" style="color: var(--color-gold); font-size: 14px;"></i> Pay on Delivery
                            </span>
                        @endif
                    </div>

                    {{-- Notes --}}
                    <div class="sf-chk-section-panel">
                        <label class="sf-label"><i class="bi bi-pencil-square me-1"></i> Order Notes (Optional)</label>
                        <textarea name="notes" class="sf-input" placeholder="Any special instructions..."></textarea>
                    </div>

                    <button type="submit" class="sf-btn-primary d-none d-md-block" style="height: 56px; font-size: 15px;">Complete Order</button>
                    
                    <div class="d-none d-md-flex sf-chk-secure-footer">
                        <span><i class="bi bi-shield-check text-success"></i> 100% Secure Checkout</span>
                        <span><i class="bi bi-arrow-repeat text-success"></i> Easy Returns</span>
                    </div>

                    {{-- Sticky Mobile Place Order Button (Premium D2C Style) --}}
                    <div class="d-md-none sf-mobile-checkout-sticky">
                        <div class="sf-chk-mobile-secure-footer">
                            <span><i class="bi bi-shield-check text-success"></i> 100% Safe</span>
                            <span><i class="bi bi-truck text-success"></i> Secure</span>
                            <span><i class="bi bi-arrow-repeat text-success"></i> Easy Returns</span>
                        </div>
                        <button type="submit" class="sf-mobile-checkout-btn" onclick="this.style.opacity='0.8'; this.querySelector('.btn-text').innerText='Processing...';">
                            <span style="font-size: 16px;">₹{{ number_format((float) $totals['grand'], 0) }}</span>
                            <span style="display: flex; align-items: center; gap: 4px;"><span class="btn-text">Place Order</span> <i class="bi bi-chevron-right"></i></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
/* Hide Bottom Nav on Checkout for zero-distraction & fix sticky overlap */
.sf-bottom-nav { display: none !important; }

/* Responsive Utility Fallbacks */
@media (min-width: 768px) {
    .d-md-none { display: none !important; }
    .d-md-block { display: block !important; }
    .d-md-flex { display: flex !important; }
}
@media (max-width: 767px) {
    .d-md-block, .d-md-flex { display: none !important; }
}
</style>
<script>
(function () {
    // Fix #5: Auto-scroll to error block on page load
    var errBlock = document.getElementById('checkoutErrors');
    if (errBlock) {
        errBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // Fix #15: Fire InitiateCheckout pixel on page load (dedup guarded)
    if (!window.__checkout_tracked) {
        window.__checkout_tracked = true;
        try {
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
            if (typeof fbq === 'function') {
                @if(session('analytics_add_to_cart'))
                @php $a = session('analytics_add_to_cart'); @endphp
                fbq('track', 'AddToCart', {
                    value: {{ $a['value'] }},
                    currency: @json($a['currency']),
                    content_ids: [@json($a['items'][0]['item_id'] ?? '')],
                    content_type: 'product'
                }, { eventID: 'atc-{{ session()->getId() }}-{{ now()->timestamp }}' });
                @endif

                fbq('track', 'InitiateCheckout', {
                    value: {{ (float) $totals['grand'] }},
                    currency: '{{ config('commerce.currency', 'INR') }}',
                    num_items: {{ (int) $lines->sum(fn ($r) => $r['item']->qty) }}
                }, {
                    eventID: 'ic-{{ session()->getId() }}-{{ now()->timestamp }}'
                });
            }
            if (window.Store) {
                Store.track('checkout_start', { value: {{ (float) $totals['grand'] }} });
            }
        } catch(e) { console.error('Checkout tracking error:', e); }
    }



    // ── Saved Address Loader ─────────────────────
    const addrSelect = document.getElementById('saved_address_select');
    if (addrSelect) {
        fetch('{{ Auth::check() ? route("account.api.addresses") : "" }}')
            .then(r => r.json())
            .then(addrs => {
                addrs.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.textContent = `${a.label} — ${a.name}, ${a.address_line1}, ${a.city}`;
                    if (a.is_default) opt.selected = true;
                    addrSelect.appendChild(opt);
                });
                // Pre-fill if default exists
                if (addrSelect.value) addrSelect.dispatchEvent(new Event('change'));
            }).catch(() => {});

        addrSelect.addEventListener('change', function() {
            if (!this.value) return;
            const opt = this.options[this.selectedIndex];
            // Fetch full data from loaded options
            fetch('{{ Auth::check() ? route("account.api.addresses") : "" }}')
                .then(r => r.json())
                .then(addrs => {
                    const a = addrs.find(x => x.id == addrSelect.value);
                    if (!a) return;
                    const set = (id, val) => { const el = document.getElementById(id) || document.querySelector('input[name="'+id+'"]'); if(el) el.value = val || ''; };
                    set('customer_name', a.name);
                    set('phone', a.phone);
                    set('address_line1', a.address_line1);
                    set('address_line2', a.address_line2);
                    set('postal_code', a.postal_code);
                    set('city', a.city);
                    set('state', a.state);
                }).catch(() => {});
        });
    }
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

    // Inline blur validations
    function showError(input, text) {
        if (!input) return;
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        let err = input.nextElementSibling;
        if(!err || !err.classList.contains('sf-inline-err-text')) {
             err = document.createElement('span');
             err.className = 'sf-inline-err-text';
             if(input.parentNode) input.parentNode.appendChild(err);
        }
        err.innerText = text;
    }
    function hideError(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        if(input.parentNode) {
            const err = input.parentNode.querySelector('.sf-inline-err-text');
            if(err) err.remove();
        }
    }

    // Pincode API
    if (pinInput && cityInput && stateInput) {
        pinInput.readOnly = false;
        pinInput.removeAttribute('readonly');

        pinInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Hide error while typing
            const errEl = document.getElementById('pin_err');
            if (errEl) errEl.classList.add('d-none');
            this.classList.remove('is-invalid');
            
            if (this.value.length === 6) {
                // Fetch
                if (spinner) spinner.classList.remove('d-none');
                this.readOnly = true;
                
                var processEnd = function() {
                    pinInput.readOnly = false;
                    pinInput.removeAttribute('readonly');
                    if (spinner) spinner.classList.add('d-none');
                };

                fetch('https://api.postalpincode.in/pincode/' + this.value, {
                    method: 'GET'
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                        var info = data[0].PostOffice[0];
                        cityInput.value = info.District;
                        stateInput.value = info.State;
                        
                        cityInput.readOnly = true;
                        stateInput.readOnly = true;
                        cityInput.setAttribute('readonly', 'true');
                        stateInput.setAttribute('readonly', 'true');
                        
                        if (errEl) errEl.classList.add('d-none');
                        pinInput.classList.remove('is-invalid');
                        pinInput.classList.add('is-valid');

                        if (window.Store) {
                            Store.emit('pincode:resolved', { city: info.District, state: info.State, pincode: pinInput.value });
                        }

                        // Trigger AJAX Quote
                        if (typeof refreshShippingQuote === 'function') {
                            refreshShippingQuote();
                        }
                    } else if (data && data[0] && data[0].Status === 'Error') {
                        // Invalid PIN Case 3
                        cityInput.value = '';
                        stateInput.value = '';
                        cityInput.readOnly = true;
                        stateInput.readOnly = true;
                        cityInput.setAttribute('readonly', 'true');
                        stateInput.setAttribute('readonly', 'true');

                        pinInput.classList.add('is-invalid');
                        pinInput.classList.remove('is-valid');
                        if (errEl) {
                            errEl.textContent = 'Invalid PIN code. Please check.';
                            errEl.classList.remove('d-none');
                        }
                    } else {
                        // Valid PIN but API format unexpectedly changed / failed
                        cityInput.readOnly = false;
                        stateInput.readOnly = false;
                        cityInput.removeAttribute('readonly');
                        stateInput.removeAttribute('readonly');
                        
                        if (errEl) {
                            errEl.textContent = 'Auto-fill failed, please enter manually.';
                            errEl.classList.remove('d-none');
                        }
                    }
                    processEnd();
                })
                .catch(function(err) {
                    console.error("Postal API error: ", err);
                    // API Fail Case 2
                    cityInput.readOnly = false;
                    stateInput.readOnly = false;
                    cityInput.removeAttribute('readonly');
                    stateInput.removeAttribute('readonly');
                    
                    if (errEl) {
                        errEl.textContent = 'Auto-fill failed, please enter manually.';
                        errEl.classList.remove('d-none');
                    }
                    processEnd();
                });
            } else if (this.value.length > 0 && this.value.length < 6) {
                // Keep readonly if less than 6 digits
                cityInput.value = '';
                stateInput.value = '';
                cityInput.readOnly = true;
                stateInput.readOnly = true;
                cityInput.setAttribute('readonly', 'true');
                stateInput.setAttribute('readonly', 'true');
                this.classList.remove('is-valid');
            }
        });
        
        // Initial check if validation failed previously
        if(pinInput.value.length === 6 && !cityInput.value) {
            pinInput.dispatchEvent(new Event('input'));
        }
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', function() {
            var val = this.value.trim().replace(/[\s\-]/g, '');
            var isValid = /^(?:(?:\+|00)?91)?[6-9]\d{9}$/.test(val);
            if (!isValid) {
                showError(this, 'Enter a valid 10-digit phone number');
                var pe = document.getElementById('phone_err');
                if(pe) { pe.classList.remove('d-none'); pe.textContent = 'Enter a valid 10-digit phone number'; }
            } else {
                hideError(this);
                var pe = document.getElementById('phone_err');
                if(pe) pe.classList.add('d-none');
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
            if (this.readOnly) return; // API in progress
            var val = this.value.trim();
            var errEl = document.getElementById('pin_err');
            if (val.length > 0 && val.length < 6) {
                showError(this, 'PIN code must be exactly 6 digits');
                if (errEl) {
                    errEl.textContent = 'PIN code must be exactly 6 digits';
                    errEl.classList.remove('d-none');
                }
            } else if (val.length === 6) {
                if (errEl) errEl.classList.add('d-none');
                if (!this.classList.contains('is-invalid')) {
                    hideError(this);
                }
            }
        });
    }

    // ── Global Shipping Quote JS ──
    window.refreshShippingQuote = function() {
        var pin = (pinInput && pinInput.value) ? pinInput.value.trim() : '';
        if (pin.length > 0 && pin.length !== 6) return; // Only block if partially typed

        var method = document.querySelector('input[name="payment_method"]:checked');
        var methodVal = method ? method.value : '';

        var url = '{{ route("checkout.shipping.quote") }}?postal_code=' + encodeURIComponent(pin) + '&payment_method=' + encodeURIComponent(methodVal);

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(q) {
            var shippingEl = document.getElementById('summary-shipping-val');
            var grandEl    = document.getElementById('summary-grand-val');
            if (shippingEl) {
                if (q.is_free) {
                    shippingEl.innerHTML = '<span style="color: var(--color-success); font-weight: 600;">FREE (Online Payment)</span>';
                } else {
                    shippingEl.innerHTML = '<span style="font-weight: 500;">₹' + parseFloat(q.shipping).toFixed(2) + '</span>';
                }
            }
            if (grandEl && q.grand) {
                grandEl.textContent = '₹' + parseFloat(q.grand).toFixed(2);
                var stickyGrandEl = document.querySelector('.sf-mobile-checkout-btn span:first-child');
                if (stickyGrandEl) {
                    stickyGrandEl.textContent = '₹' + Math.round(parseFloat(q.grand));
                }
            }
        })
        .catch(function(e) { console.warn('Shipping quote fetch failed', e); });
    };

    // Listen to Payment Method changes
    paymentRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            refreshShippingQuote();
        });
    });

    // Analytics and Form Submit
    var form = document.getElementById('checkout-form');
    if (!form) return;
    
    form.addEventListener('submit', function (e) {
        // Run validations manually
        var hasError = false;
        var phoneVal = phoneInput ? phoneInput.value.trim().replace(/[\s\-]/g, '') : '';
        if (phoneInput && !(/^(?:(?:\+|00)?91)?[6-9]\d{9}$/.test(phoneVal))) {
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
        
        var btn = document.getElementById('submitBtn');
        if(btn) {
            if(btn.dataset.submitting === 'true') { 
                e.preventDefault(); 
                return; 
            }
            btn.dataset.submitting = 'true';
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            btn.classList.add('disabled');
            // Async disable to allow form to actually submit before disabling
            setTimeout(function() { btn.disabled = true; }, 50);
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
        // InitiateCheckout already fires on page load (Fix #15 — dedup guarded)
        // Do NOT fire again on submit to avoid double events
    });
})();
</script>
@endpush
