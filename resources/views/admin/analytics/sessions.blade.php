@extends('layouts.admin')
@section('title', 'Session Explorer — Decision Engine')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Decision Engine</a></li>
                <li class="breadcrumb-item active" aria-current="page">Session Explorer</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0" style="font-weight: 700; color: #111827;">User Sessions</h1>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.analytics.sessions') }}" class="btn btn-outline-secondary btn-sm">All</a>
        <a href="{{ route('admin.analytics.sessions', ['purchase' => 1]) }}" class="btn btn-outline-success btn-sm">Purchased</a>
        <a href="{{ route('admin.analytics.sessions', ['cart' => 1, 'purchase' => 0]) }}" class="btn btn-outline-warning btn-sm">Abandoned Cart</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Session & Visitor</th>
                    <th>Source / Campaign</th>
                    <th>Duration</th>
                    <th>Events</th>
                    <th>Funnel Progress</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                <tr>
                    <td class="ps-4">
                        <div class="fw-medium text-dark">{{ $session->visitor->country ?? 'Unknown' }} • {{ $session->visitor->os ?? 'Unknown OS' }} ({{ $session->visitor->browser ?? 'Unknown Browser' }})</div>
                        <div class="text-muted small" title="{{ $session->session_uuid }}">{{ $session->started_at->format('M d, H:i') }} ({{ $session->started_at->diffForHumans() }})</div>
                    </td>
                    <td>
                        <div><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">{{ $session->source ?? 'Direct' }}</span></div>
                        @if($session->campaign) <div class="small text-muted mt-1">{{ Str::limit($session->campaign, 20) }}</div> @endif
                    </td>
                    <td>
                        {{ gmdate("i:s", $session->duration_seconds) }}
                        @if($session->is_bounce) <span class="badge bg-danger ms-1">Bounce</span> @endif
                    </td>
                    <td>{{ $session->event_count }} events<br><span class="small text-muted">{{ $session->page_count }} pages</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($session->reached_purchase)
                                <span class="badge bg-success" title="Purchased"><i class="bi bi-check-circle-fill"></i> Purchased</span>
                            @elseif($session->reached_checkout)
                                <span class="badge bg-info" title="Reached Checkout"><i class="bi bi-credit-card"></i> Checkout</span>
                            @elseif($session->reached_cart)
                                <span class="badge bg-warning text-dark" title="Added to Cart"><i class="bi bi-cart-fill"></i> Cart</span>
                            @elseif($session->reached_product)
                                <span class="badge bg-primary" title="Viewed Product"><i class="bi bi-eye-fill"></i> Product</span>
                            @else
                                <span class="badge bg-light text-muted" title="Just Browsed">Browsed</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('admin.analytics.sessions.show', $session->id) }}" class="btn btn-sm btn-primary">View Timeline</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">No sessions found matching your criteria.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">
    {{ $sessions->links() }}
</div>
@endsection
