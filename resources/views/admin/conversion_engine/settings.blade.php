@extends('admin.layout')

@section('title', 'Conversion Engine OS Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Conversion Engine OS</h4>
            <div class="text-muted small">Configure capture modal, recovery sequence, and intelligence flags.</div>
        </div>
        <a href="{{ route('admin.analytics.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-graph-up me-1"></i> View Analytics
        </a>
    </div>

    <form action="{{ route('admin.settings.conversion-engine.store') }}" method="POST">
        @csrf
        
        {{-- ── FEATURE FLAGS ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.75rem;">Feature Flags</h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" id="engine_enabled" name="settings[commerce.conversion_engine.capture_offer.engine_enabled]" value="1" {{ config('commerce.conversion_engine.capture_offer.engine_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label fs-6 fw-bold ms-2" for="engine_enabled">Capture Engine (Modal)</label>
                        </div>
                        <div class="text-muted small ms-5 mt-1">Enable the ATC capture modal across the store.</div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" id="recovery_enabled" name="settings[commerce.conversion_engine.capture_offer.recovery_enabled]" value="1" {{ config('commerce.conversion_engine.capture_offer.recovery_enabled') ? 'checked' : '' }}>
                            <label class="form-check-label fs-6 fw-bold ms-2" for="recovery_enabled">Recovery Engine</label>
                        </div>
                        <div class="text-muted small ms-5 mt-1">Enable automated WhatsApp reminders for abandoned carts.</div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" id="recovery_dry_run" name="settings[commerce.conversion_engine.capture_offer.recovery_dry_run]" value="1" {{ config('commerce.conversion_engine.capture_offer.recovery_dry_run') ? 'checked' : '' }}>
                            <label class="form-check-label fs-6 fw-bold ms-2" for="recovery_dry_run">Recovery Dry Run Mode</label>
                        </div>
                        <div class="text-muted small ms-5 mt-1 text-warning"><i class="bi bi-exclamation-triangle-fill"></i> Safe mode. Will only write to logs, will NOT send real messages or increment steps.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CAPTURE MODAL CONFIG ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.75rem;">Capture Modal Algorithm</h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Traffic Split (Variant A %)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="settings[commerce.conversion_engine.capture_offer.traffic_split_percent]" value="{{ config('commerce.conversion_engine.capture_offer.traffic_split_percent', 80) }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Percentage of visitors who will see the capture offer. The rest act as the Control group.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Fatigue Cooldown</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="settings[commerce.conversion_engine.capture_offer.cooldown_days]" value="{{ config('commerce.conversion_engine.capture_offer.cooldown_days', 14) }}" min="1">
                            <span class="input-group-text">Days</span>
                        </div>
                        <div class="form-text">If a user closes the modal without submitting, how long before we ask again?</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Offer Coupon Code</label>
                        <input type="text" class="form-control" name="settings[commerce.conversion_engine.capture_offer.offer_coupon_code]" value="{{ config('commerce.conversion_engine.capture_offer.offer_coupon_code') }}">
                        <div class="form-text">The coupon automatically applied when they submit their number.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── UI COPY ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="mb-0 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.75rem;">Modal UI Copy</h6>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Headline</label>
                        <input type="text" class="form-control" name="settings[commerce.conversion_engine.capture_offer.ui_headline]" value="{{ config('commerce.conversion_engine.capture_offer.ui_headline') }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Subtext</label>
                        <input type="text" class="form-control" name="settings[commerce.conversion_engine.capture_offer.ui_subtext]" value="{{ config('commerce.conversion_engine.capture_offer.ui_subtext') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Button Text</label>
                        <input type="text" class="form-control" name="settings[commerce.conversion_engine.capture_offer.ui_button_text]" value="{{ config('commerce.conversion_engine.capture_offer.ui_button_text') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RECOVERY SEQUENCE ── --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-uppercase fw-bold text-muted" style="letter-spacing: 1px; font-size: 0.75rem;">Abandonment Sequence (WhatsApp)</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex gap-3 mb-4">
                    <i class="bi bi-info-circle fs-4"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Sequence Engine</h6>
                        <p class="mb-0 small">Use <code>{name}</code> for the customer's name and <code>{link}</code> for their direct cart recovery URL.</p>
                    </div>
                </div>

                @php
                    $sequence = config('commerce.conversion_engine.abandonment_sequence', []);
                @endphp
                
                @foreach($sequence as $index => $step)
                    <div class="border rounded p-3 mb-3 position-relative">
                        <h6 class="fw-bold mb-3">Step {{ $index + 1 }}</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Delay (Minutes)</label>
                                <input type="number" class="form-control" name="settings[commerce.conversion_engine.abandonment_sequence][{{ $index }}][delay_minutes]" value="{{ $step['delay_minutes'] }}">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label small text-muted">Message Template</label>
                                <textarea class="form-control" rows="2" name="settings[commerce.conversion_engine.abandonment_sequence][{{ $index }}][template]">{{ $step['template'] }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">Save Configuration</button>
        </div>
    </form>
</div>
@endsection
