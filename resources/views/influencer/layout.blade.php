<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <title>@yield('title', 'Influencer Portal') — {{ config('app.name', 'Dream Attitude') }}</title>
    
    @php $ss = app(\App\Services\SettingsService::class); @endphp
    @if($ss->get('theme.favicon'))
        <link rel="icon" href="{{ asset('storage/' . $ss->get('theme.favicon')) }}">
    @endif

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg-dark: #0A0A0A;
            --color-bg-surface: #141414;
            --color-bg-elevated: #1F1F1F;
            --color-gold: #C9963A;
            --color-gold-light: #E8C97A;
            --color-gold-dark: #A07830;
            --color-gold-muted: rgba(201, 150, 58, 0.12);
            --color-border: rgba(255, 255, 255, 0.1);
            --color-border-gold: rgba(201, 150, 58, 0.35);
            --color-text-light: #F8F8F8;
            --color-text-muted: #A0A0A0;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        * { font-family: 'DM Sans', system-ui, sans-serif; box-sizing: border-box; }

        body {
            background: #0A0A0A;
            color: var(--color-text-light);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        /* ── Header / Navbar ── */
        .inf-header {
            background: rgba(14, 14, 14, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--color-border-gold);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 14px 0;
        }

        .inf-brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-gold-light);
            letter-spacing: 1px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inf-nav-pill {
            color: var(--color-text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }
        .inf-nav-pill:hover, .inf-nav-pill.active {
            color: #0A0A0A;
            background: var(--color-gold);
            font-weight: 600;
        }

        /* ── Cards & Surfaces ── */
        .inf-card {
            background: var(--color-bg-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: border-color 0.2s ease;
        }
        .inf-card:hover {
            border-color: var(--color-border-gold);
        }

        .inf-stat-card {
            background: linear-gradient(145deg, #161616, #111111);
            border: 1px solid rgba(201, 150, 58, 0.2);
            border-radius: var(--radius-lg);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .inf-stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, rgba(201, 150, 58, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .inf-stat-val {
            font-size: 1.85rem;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: -0.5px;
            margin: 4px 0;
        }
        .inf-stat-label {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--color-gold);
            font-weight: 600;
        }

        /* ── Buttons ── */
        .inf-btn-gold {
            background: linear-gradient(135deg, #D4AF37, #AA7C11);
            color: #0A0A0A;
            font-weight: 700;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .inf-btn-gold:hover {
            background: linear-gradient(135deg, #E8C97A, #C9963A);
            color: #000;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(201, 150, 58, 0.35);
        }

        .inf-btn-outline {
            background: transparent;
            color: var(--color-gold-light);
            border: 1px solid var(--color-border-gold);
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .inf-btn-outline:hover {
            background: var(--color-gold-muted);
            color: #FFFFFF;
            border-color: var(--color-gold);
        }

        /* ── Table Styling ── */
        .inf-table {
            width: 100%;
            border-collapse: collapse;
        }
        .inf-table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--color-text-muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--color-border);
            font-weight: 600;
        }
        .inf-table td {
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.88rem;
            color: var(--color-text-light);
            vertical-align: middle;
        }
        .inf-table tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        /* ── Badges ── */
        .inf-badge-paid {
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .inf-badge-approved {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .inf-badge-pending {
            background: rgba(234, 179, 8, 0.15);
            color: #facc15;
            border: 1px solid rgba(234, 179, 8, 0.3);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .inf-badge-cancelled {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .modal-content {
            background: #141414 !important;
            border: 1px solid var(--color-border-gold) !important;
            color: #FFF !important;
        }
        .form-control, .form-select {
            background: #0A0A0A !important;
            border: 1px solid var(--color-border) !important;
            color: #FFF !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--color-gold) !important;
            box-shadow: 0 0 0 3px rgba(201, 150, 58, 0.2) !important;
        }
    </style>
</head>
<body>

{{-- Header --}}
<header class="inf-header">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="{{ route('influencer.dashboard') }}" class="inf-brand-title">
            <i class="bi bi-stars text-warning"></i> {{ config('app.name', 'DREAM ATTITUDE') }} <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fs-6 fw-normal ms-1 px-2 py-0">PARTNER</span>
        </a>

        @auth
        <nav class="d-none d-md-flex align-items-center gap-2">
            <a href="{{ route('influencer.dashboard') }}" class="inf-nav-pill {{ request()->routeIs('influencer.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('influencer.orders') }}" class="inf-nav-pill {{ request()->routeIs('influencer.orders') ? 'active' : '' }}">Referral Orders</a>
            <a href="{{ route('influencer.payouts') }}" class="inf-nav-pill {{ request()->routeIs('influencer.payouts') ? 'active' : '' }}">Payouts & History</a>
        </nav>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#payoutSettingsModal">
                <i class="bi bi-wallet2 text-warning me-1"></i> Payout Settings
            </button>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm text-danger border-0"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
        @endauth
    </div>
</header>

{{-- Main Container --}}
<main class="container my-4 flex-grow-1">
    @if (session('success'))
        <div class="alert alert-success border-0 bg-success bg-opacity-25 text-white d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-white d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 bg-danger bg-opacity-25 text-white mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

{{-- Footer --}}
<footer class="text-center py-4 border-top border-secondary border-opacity-25 text-muted small">
    &copy; {{ date('Y') }} {{ config('app.name') }} Influencer Partner Network · All rights reserved.
</footer>

{{-- Payout Settings Modal --}}
@auth
@php $infModel = Auth::user()->influencer; @endphp
@if($infModel)
<div class="modal fade" id="payoutSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('influencer.payout.settings') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary border-opacity-25">
                    <h5 class="modal-title text-warning fw-bold"><i class="bi bi-wallet-fill me-2"></i>Bank & UPI Payout Settings</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Provide your UPI ID or Bank account details where you want your earned referral commissions transferred.</p>

                    <div class="mb-3">
                        <label class="form-label text-warning fw-semibold">UPI ID (Google Pay / PhonePe / Paytm / BHIM) *</label>
                        <input type="text" name="upi_id" class="form-control" value="{{ old('upi_id', $infModel->upi_id) }}" placeholder="e.g. yourname@okhdfcbank">
                    </div>

                    <div class="text-center my-3 text-muted small">— OR DIRECT BANK TRANSFER —</div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Account Holder Name</label>
                        <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $infModel->bank_details['account_name'] ?? '') }}" placeholder="Full Name as on Bank Passbook">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label text-muted small">Account Number</label>
                            <input type="text" name="bank_account_no" class="form-control" value="{{ old('bank_account_no', $infModel->bank_details['account_no'] ?? '') }}" placeholder="Bank Account Number">
                        </div>
                        <div class="col-5">
                            <label class="form-label text-muted small">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control text-uppercase" value="{{ old('bank_ifsc', $infModel->bank_details['ifsc'] ?? '') }}" placeholder="HDFC0001234">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-muted small">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $infModel->bank_details['bank_name'] ?? '') }}" placeholder="e.g. HDFC Bank">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="inf-btn-gold btn-sm"><i class="bi bi-save me-1"></i> Save Details</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyToClipboard(text, btnId, successMsg) {
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.getElementById(btnId);
        if (btn) {
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            setTimeout(function() { btn.innerHTML = orig; }, 2000);
        }
    });
}
</script>
</body>
</html>
