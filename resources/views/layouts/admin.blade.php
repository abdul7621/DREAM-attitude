<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #f1f5f9;
            --sidebar-section: #64748b;
            --topbar-height: 56px;
        }

        * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

        body { margin: 0; background: #f1f5f9; min-height: 100vh; }

        /* ── Sidebar ─────────────────────────────────────────── */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            overflow-y: auto; overflow-x: hidden;
            z-index: 1040;
            display: flex; flex-direction: column;
            transition: transform .25s ease;
        }

        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .sidebar-brand a {
            color: #fff; text-decoration: none; font-weight: 700; font-size: 1.1rem;
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-brand .brand-icon {
            width: 32px; height: 32px; background: var(--sidebar-active);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            font-size: .85rem; color: #fff; flex-shrink: 0;
        }

        .sidebar-nav { flex: 1; padding: 8px 0; }

        .sidebar-section-label {
            padding: 18px 20px 6px;
            font-size: .65rem; font-weight: 600; letter-spacing: .08em;
            text-transform: uppercase; color: var(--sidebar-section);
            user-select: none;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 9px 20px; margin: 1px 8px; border-radius: 8px;
            color: var(--sidebar-text); text-decoration: none;
            font-size: .85rem; font-weight: 500;
            transition: all .15s ease;
        }
        .sidebar-link i { font-size: 1.05rem; width: 20px; text-align: center; flex-shrink: 0; }
        .sidebar-link:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .sidebar-link.active { background: var(--sidebar-active); color: #fff; }
        .sidebar-link .badge {
            margin-left: auto; font-size: .65rem; padding: 3px 7px;
        }

        .sidebar-footer {
            padding: 12px 16px; border-top: 1px solid rgba(255,255,255,.06);
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-footer .avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: #334155; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .8rem; font-weight: 600; flex-shrink: 0;
        }
        .sidebar-footer .user-info { flex: 1; min-width: 0; }
        .sidebar-footer .user-name {
            font-size: .8rem; font-weight: 600; color: #e2e8f0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-footer .user-role { font-size: .65rem; color: var(--sidebar-section); }

        /* ── Topbar (mobile + main content header) ───────────── */
        .admin-topbar {
            position: fixed; top: 0; right: 0;
            left: var(--sidebar-width); height: var(--topbar-height);
            background: #fff; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; padding: 0 24px;
            z-index: 1030; gap: 12px;
        }
        .admin-topbar .topbar-title { font-weight: 600; font-size: 1rem; color: #1e293b; }
        .admin-topbar .btn-sidebar-toggle { display: none; }

        /* ── Main content ─────────────────────────────────────── */
        .admin-main {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--topbar-height) + 24px);
            padding-bottom: 32px;
            padding-left: 24px; padding-right: 24px;
            min-height: 100vh;
        }

        /* ── Mobile ────────────────────────────────────────────── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 1039;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .admin-topbar { left: 0; }
            .admin-topbar .btn-sidebar-toggle { display: flex; }
            .admin-main { margin-left: 0; }
        }

        /* ── Shared card style ─────────────────────────────────── */
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.06); border-radius: 10px; }
        .card-header { background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; }

        /* ── Alerts ─────────────────────────────────────────────── */
        .alert { border-radius: 10px; border: none; }

        /* ── Storefront link ────────────────────────────────────── */
        .storefront-link {
            display: flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: 8px;
            background: rgba(37,99,235,.1); color: var(--sidebar-active);
            text-decoration: none; font-size: .78rem; font-weight: 600;
            margin: 4px 12px 8px; transition: all .15s;
        }
        .storefront-link:hover { background: rgba(37,99,235,.2); }

        @stack('admin-styles')
    </style>
</head>
<body>

<!-- Overlay for mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ─── Sidebar ─────────────────────────────────────────────── -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="{{ route('admin.dashboard') }}">
            <span class="brand-icon"><i class="bi bi-lightning-charge-fill"></i></span>
            {{ config('app.name', 'Commerce') }}
        </a>
    </div>

    <nav class="sidebar-nav">
        {{-- ─── CORE ──────────────────────────────────────── --}}
        <div class="sidebar-section-label">Core</div>
        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="{{ route('admin.orders.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> Orders
            @if(isset($badgeCountPendingOrders) && $badgeCountPendingOrders > 0)
                <span class="badge bg-danger rounded-pill">{{ $badgeCountPendingOrders }}</span>
            @endif
        </a>
        <a href="{{ route('admin.products.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Products
            @if(isset($badgeCountLowStock) && $badgeCountLowStock > 0)
                <span class="badge bg-warning text-dark rounded-pill">{{ $badgeCountLowStock }}</span>
            @endif
        </a>
        <a href="{{ route('admin.categories.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> Categories
        </a>
        <a href="{{ route('admin.customers.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Customers
        </a>

        {{-- ─── GROWTH ────────────────────────────────────── --}}
        <div class="sidebar-section-label">Growth</div>
        <a href="{{ route('admin.coupons.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
            <i class="bi bi-ticket-perforated"></i> Coupons & Discounts
        </a>
        <a href="{{ route('admin.reviews.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <i class="bi bi-star-half"></i> Reviews
            @if(isset($badgeCountPendingReviews) && $badgeCountPendingReviews > 0)
                <span class="badge bg-primary rounded-pill">{{ $badgeCountPendingReviews }}</span>
            @endif
        </a>
        <a href="{{ route('admin.notifications.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Notifications
        </a>

        {{-- ─── REPORTS ───────────────────────────────────── --}}
        <div class="sidebar-section-label">Reports</div>
        <a href="{{ route('admin.reports.sales') }}"
           class="sidebar-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> Sales
        </a>
        <a href="{{ route('admin.reports.products') }}"
           class="sidebar-link {{ request()->routeIs('admin.reports.products') ? 'active' : '' }}">
            <i class="bi bi-box"></i> Products
        </a>
        <a href="{{ route('admin.reports.customers') }}"
           class="sidebar-link {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Customers
        </a>
        <a href="{{ route('admin.reports.coupons') }}"
           class="sidebar-link {{ request()->routeIs('admin.reports.coupons') ? 'active' : '' }}">
            <i class="bi bi-tag"></i> Coupons
        </a>
        <a href="{{ route('admin.reports.inventory') }}"
           class="sidebar-link {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i> Inventory
        </a>

        {{-- ─── STORE ─────────────────────────────────────── --}}
        <div class="sidebar-section-label">Store</div>
        <a href="{{ route('admin.theme.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.theme.*') ? 'active' : '' }}">
            <i class="bi bi-palette"></i> Theme Builder
        </a>
        <a href="{{ route('admin.menus.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}">
            <i class="bi bi-list-nested"></i> Navigation
        </a>
        <a href="{{ route('admin.pages.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Pages
        </a>
        <a href="{{ route('admin.redirects.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.redirects.*') ? 'active' : '' }}">
            <i class="bi bi-signpost-split"></i> Redirects
        </a>

        {{-- ─── OPERATIONS ────────────────────────────────── --}}
        <div class="sidebar-section-label">Operations</div>
        <a href="{{ route('admin.shipping-rules.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.shipping-rules.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> Shipping Rules
        </a>
        <a href="{{ route('admin.returns.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-return-left"></i> Returns
            @if(isset($badgeCountPendingReturns) && $badgeCountPendingReturns > 0)
                <span class="badge bg-danger rounded-pill">{{ $badgeCountPendingReturns }}</span>
            @endif
        </a>
        <a href="{{ route('admin.audit-logs.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock"></i> Audit Logs
        </a>

        {{-- ─── TOOLS ─────────────────────────────────────── --}}
        <div class="sidebar-section-label">Tools</div>
        <a href="{{ route('admin.import.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.import.*') ? 'active' : '' }}">
            <i class="bi bi-cloud-arrow-up"></i> Import Wizard
        </a>

        {{-- ─── SETTINGS ──────────────────────────────────── --}}
        <div class="sidebar-section-label">Settings</div>
        <a href="{{ route('admin.settings.edit') }}"
           class="sidebar-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i> Settings
        </a>
        <a href="{{ route('admin.notification-templates.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.notification-templates.*') ? 'active' : '' }}">
            <i class="bi bi-bell"></i> Message Templates
        </a>
    </nav>

    <!-- Storefront link -->
    <a href="{{ route('home') }}" target="_blank" class="storefront-link">
        <i class="bi bi-shop-window"></i> View Storefront
        <i class="bi bi-arrow-up-right" style="margin-left:auto;font-size:.7rem;"></i>
    </a>

    <!-- Sidebar footer with user info -->
    <div class="sidebar-footer">
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        <div class="user-info">
            <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
            <div class="user-role">Administrator</div>
        </div>
        <form action="{{ route('logout') }}" method="post" class="d-inline ms-auto">@csrf
            <button type="submit" class="btn btn-sm p-0 border-0" style="color:var(--sidebar-section);"
                    title="Logout"><i class="bi bi-box-arrow-right" style="font-size:1.1rem;"></i></button>
        </form>
    </div>
</aside>

<!-- ─── Topbar ──────────────────────────────────────────────── -->
<header class="admin-topbar">
    <button class="btn btn-sm btn-outline-secondary btn-sidebar-toggle" id="btnSidebarToggle" type="button">
        <i class="bi bi-list" style="font-size:1.2rem;"></i>
    </button>
    <span class="topbar-title">@yield('title', 'Dashboard')</span>
    <div class="ms-auto d-flex align-items-center gap-2">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1" style="font-size:.78rem;">
            <i class="bi bi-shop-window"></i> Storefront
        </a>
    </div>
</header>

<!-- ─── Main Content ───────────────────────────────────────── -->
<main class="admin-main">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
(function () {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggle  = document.getElementById('btnSidebarToggle');

    function open()  { sidebar.classList.add('show'); overlay.classList.add('show'); }
    function close() { sidebar.classList.remove('show'); overlay.classList.remove('show'); }

    if (toggle) toggle.addEventListener('click', function () {
        sidebar.classList.contains('show') ? close() : open();
    });
    if (overlay) overlay.addEventListener('click', close);
})();
</script>
@stack('scripts')
</body>
</html>
