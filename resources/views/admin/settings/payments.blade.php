@extends('layouts.admin')

@section('title', 'Payment Settings')
@section('header', 'Payment Gateways')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 col-lg-10 col-xl-8">
            <form action="{{ route('admin.settings.payments.update') }}" method="POST">
                @csrf

                @foreach($gateways as $gateway)
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h5 class="mb-0 fw-bold">{{ $gateway->label }}</h5>
                                <small class="text-muted">Driver: {{ $gateway->driver ?? $gateway->name }}</small>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch fs-5">
                                    <input class="form-check-input mt-1" type="checkbox" name="gateways[{{ $gateway->name }}][is_active]" value="1" id="active_{{ $gateway->name }}" {{ $gateway->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label fs-6 mt-1" for="active_{{ $gateway->name }}">Enable</label>
                                </div>
                                <div class="form-check ms-3">
                                    <input class="form-check-input" type="radio" name="default_gateway" value="{{ $gateway->name }}" id="default_{{ $gateway->name }}" {{ $gateway->is_default ? 'checked' : '' }}>
                                    <label class="form-check-label" for="default_{{ $gateway->name }}">
                                        Default
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            @if($gateway->name === 'razorpay')
                                <div class="col-md-6">
                                    <label class="form-label">Key ID</label>
                                    <input type="text" class="form-control" name="gateways[{{ $gateway->name }}][config][key_id]" value="{{ $gateway->getConfigValue('key_id') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Key Secret</label>
                                    <input type="password" class="form-control" name="gateways[{{ $gateway->name }}][config][key_secret]" value="{{ $gateway->getConfigValue('key_secret') }}">
                                </div>
                            @elseif($gateway->name === 'phonepe')
                                <div class="col-md-6">
                                    <label class="form-label">Merchant ID</label>
                                    <input type="text" class="form-control" name="gateways[{{ $gateway->name }}][config][merchant_id]" value="{{ $gateway->getConfigValue('merchant_id') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Salt Key</label>
                                    <input type="password" class="form-control" name="gateways[{{ $gateway->name }}][config][salt_key]" value="{{ $gateway->getConfigValue('salt_key') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Salt Index</label>
                                    <input type="text" class="form-control" name="gateways[{{ $gateway->name }}][config][salt_index]" value="{{ $gateway->getConfigValue('salt_index', '1') }}">
                                </div>
                            @elseif($gateway->name === 'cashfree')
                                <div class="col-md-6">
                                    <label class="form-label">App ID (Client ID)</label>
                                    <input type="text" class="form-control" name="gateways[{{ $gateway->name }}][config][app_id]" value="{{ $gateway->getConfigValue('app_id') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Secret Key</label>
                                    <input type="password" class="form-control" name="gateways[{{ $gateway->name }}][config][secret_key]" value="{{ $gateway->getConfigValue('secret_key') }}">
                                </div>
                            @elseif($gateway->name === 'instamojo')
                                <div class="col-md-6">
                                    <label class="form-label">API Key</label>
                                    <input type="text" class="form-control" name="gateways[{{ $gateway->name }}][config][api_key]" value="{{ $gateway->getConfigValue('api_key') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Auth Token</label>
                                    <input type="password" class="form-control" name="gateways[{{ $gateway->name }}][config][auth_token]" value="{{ $gateway->getConfigValue('auth_token') }}">
                                </div>
                            @elseif($gateway->name === 'payu')
                                <div class="col-md-6">
                                    <label class="form-label">Merchant Key</label>
                                    <input type="text" class="form-control" name="gateways[{{ $gateway->name }}][config][merchant_key]" value="{{ $gateway->getConfigValue('merchant_key') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Merchant Salt</label>
                                    <input type="password" class="form-control" name="gateways[{{ $gateway->name }}][config][merchant_salt]" value="{{ $gateway->getConfigValue('merchant_salt') }}">
                                </div>
                            @elseif($gateway->name === 'cod')
                                <div class="col-md-6">
                                    <label class="form-label">Additional Fee (₹)</label>
                                    <input type="number" step="0.01" class="form-control" name="gateways[{{ $gateway->name }}][config][charge]" value="{{ $gateway->getConfigValue('charge', 0) }}">
                                </div>
                            @endif

                            @if(in_array($gateway->name, ['phonepe', 'cashfree', 'instamojo', 'payu']))
                            <div class="col-md-6">
                                <label class="form-label">Environment</label>
                                <select class="form-select" name="gateways[{{ $gateway->name }}][config][env]">
                                    <option value="TEST" {{ $gateway->getConfigValue('env') === 'TEST' ? 'selected' : '' }}>Sandbox/Test</option>
                                    <option value="PROD" {{ $gateway->getConfigValue('env') === 'PROD' ? 'selected' : '' }}>Production</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="d-flex justify-content-end mb-5">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm px-5">Save Payment Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
