@php
    $engine = config('commerce.conversion_engine.capture_offer', []);
    $enabled = $engine['enabled'] ?? false;
@endphp

@if($enabled)
<div id="conversionCaptureModal" class="sf-modal" style="display: none;">
    <div class="sf-modal-overlay"></div>
    <div class="sf-modal-content" style="max-width: 400px; padding: 0; overflow: hidden;">
        <div style="background: var(--color-bg-elevated); padding: 32px 24px; text-align: center;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(201,168,76,0.1); color: var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 16px;">
                <i class="bi bi-truck"></i>
            </div>
            <h3 style="font-size: 20px; color: var(--color-text-primary); margin-bottom: 8px;">{{ $engine['ui_headline'] ?? 'Unlock Free Priority Shipping' }}</h3>
            <p style="color: var(--color-text-secondary); font-size: 14px; margin-bottom: 24px; line-height: 1.5;">
                {{ $engine['ui_subtext'] ?? 'Save your mobile number to get free shipping and save your cart.' }}
            </p>
            
            <form id="captureOfferForm" onsubmit="handleCaptureSubmit(event)">
                @csrf
                <div style="position: relative; margin-bottom: 16px;">
                    <div style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 600; color: var(--color-text-primary);">+91</div>
                    <input type="tel" id="capture_guest_phone" name="guest_phone" required pattern="[0-9]{10}" maxlength="10" placeholder="Enter mobile number" class="sf-input" style="padding-left: 54px; font-weight: 600; letter-spacing: 1px;" autocomplete="tel">
                </div>
                
                <button type="submit" class="sf-btn-primary" style="width: 100%; margin-bottom: 12px; height: 50px;">
                    {{ $engine['ui_button_text'] ?? 'Unlock Free Shipping' }}
                </button>
                
                <button type="button" onclick="continueAsGuest()" style="background: none; border: none; color: var(--color-text-muted); font-size: 13px; text-decoration: underline; cursor: pointer;">
                    No thanks, continue as guest
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.sf-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; }
.sf-modal-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); }
.sf-modal-content { position: relative; background: var(--color-bg-surface); border-radius: var(--radius-lg); width: 90%; z-index: 10; box-shadow: 0 20px 40px rgba(0,0,0,0.2); transform: translateY(20px); opacity: 0; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.sf-modal.show .sf-modal-content { transform: translateY(0); opacity: 1; }
</style>

<script>
    const captureConfig = @json($engine);
    const checkoutUrl = '{{ route("checkout.create") }}';
    
    function initCaptureModal(triggerSelector) {
        if (!captureConfig.enabled) return;

        const splitPercent = captureConfig.traffic_split_percent || 100;
        const cooldownDays = captureConfig.cooldown_days || 14;
        
        // Fatigue check
        const lastSeen = localStorage.getItem('da_capture_seen_at');
        if (lastSeen) {
            const daysSince = (new Date() - new Date(parseInt(lastSeen))) / (1000 * 60 * 60 * 24);
            if (daysSince < cooldownDays) return; // In cooldown
        }

        // Sticky Cohort logic
        let cohort = localStorage.getItem('da_capture_cohort');
        if (!cohort) {
            cohort = (Math.random() * 100 <= splitPercent) ? 'variant_a' : 'control';
            localStorage.setItem('da_capture_cohort', cohort);
        }

        const btn = document.querySelector(triggerSelector);
        if (btn) {
            btn.addEventListener('click', function(e) {
                if (cohort === 'control') {
                    // Control group goes straight to checkout
                    return; 
                }
                
                // Show modal, prevent default
                e.preventDefault();
                showCaptureModal();
            });
        }
    }

    function showCaptureModal() {
        const modal = document.getElementById('conversionCaptureModal');
        modal.style.display = 'flex';
        // Small delay to allow display:flex to apply before adding class for animation
        setTimeout(() => modal.classList.add('show'), 10);
        localStorage.setItem('da_capture_seen_at', Date.now());
    }

    function hideCaptureModal() {
        const modal = document.getElementById('conversionCaptureModal');
        modal.classList.remove('show');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    function continueAsGuest() {
        window.location.href = checkoutUrl;
    }

    function handleCaptureSubmit(e) {
        e.preventDefault();
        const phone = document.getElementById('capture_guest_phone').value;
        if (!phone || phone.length < 10) return;

        const btn = e.target.querySelector('button[type="submit"]');
        btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Processing...';
        btn.disabled = true;

        fetch('{{ route("cart.capture") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                guest_phone: phone,
                lead_source: 'checkout_start_modal'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.href = checkoutUrl;
            }
        })
        .catch(err => {
            console.error('Capture Error:', err);
            window.location.href = checkoutUrl; // Failsafe
        });
    }

    // Initialize if on cart page
    document.addEventListener('DOMContentLoaded', function() {
        initCaptureModal('.btn-checkout');
    });
</script>
@endif
