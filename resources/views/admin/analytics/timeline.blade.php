@extends('layouts.admin')
@section('title', 'Session Timeline — Decision Engine')

@push('admin-styles')
<style>
    .timeline { position: relative; padding-left: 30px; margin-top: 30px; }
    .timeline::before {
        content: ''; position: absolute; left: 11px; top: 0; bottom: 0;
        width: 2px; background: #e5e7eb;
    }
    .timeline-item { position: relative; margin-bottom: 24px; }
    .timeline-icon {
        position: absolute; left: -30px; top: 0;
        width: 24px; height: 24px; border-radius: 50%;
        background: #fff; border: 2px solid #3b82f6;
        display: flex; align-items: center; justify-content: center;
        z-index: 1; font-size: 10px; color: #3b82f6;
    }
    .timeline-item.purchase .timeline-icon { border-color: #10b981; color: #10b981; background: #ecfdf5; }
    .timeline-item.cart .timeline-icon { border-color: #f59e0b; color: #f59e0b; background: #fffbeb; }
    .timeline-item.search .timeline-icon { border-color: #8b5cf6; color: #8b5cf6; background: #f5f3ff; }
    
    .timeline-content {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
        padding: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .timeline-time { font-size: 0.75rem; color: #6b7280; font-weight: 500; margin-bottom: 4px; }
    .timeline-event-name { font-weight: 600; color: #111827; font-size: 0.95rem; }
    .timeline-meta { font-size: 0.85rem; color: #4b5563; margin-top: 8px; background: #f9fafb; padding: 10px; border-radius: 6px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.analytics.index') }}">Decision Engine</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.analytics.sessions') }}">Session Explorer</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $session->session_uuid }}</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0" style="font-weight: 700; color: #111827;">User Journey Replay</h1>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">Session Context</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Started At</span>
                        <strong>{{ $session->started_at->format('M d, Y h:i A') }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Duration</span>
                        <strong>{{ gmdate("i:s", $session->duration_seconds) }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Device / OS</span>
                        <strong>{{ ucfirst($session->device_type) }} / {{ $session->visitor->os ?? 'Unknown' }}</strong>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Source</span>
                        <strong>{{ $session->source ?? 'Direct' }}</strong>
                    </li>
                    @if($session->visitor->user)
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Customer</span>
                        <strong><a href="{{ route('admin.customers.show', $session->visitor->user_id) }}">{{ $session->visitor->user->name }}</a></strong>
                    </li>
                    @endif
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        @if($session->reached_purchase)
                            <span class="badge bg-success">Purchased (₹{{ $session->revenue }})</span>
                        @elseif($session->reached_cart)
                            <span class="badge bg-warning text-dark">Abandoned Cart</span>
                        @elseif($session->is_bounce)
                            <span class="badge bg-danger">Bounced</span>
                        @else
                            <span class="badge bg-secondary">Browsed</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Event Timeline</div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($session->events as $event)
                        @php
                            $cssClass = '';
                            $icon = 'bi-record-circle';
                            if ($event->event_name === 'purchase') { $cssClass = 'purchase'; $icon = 'bi-check-circle-fill'; }
                            elseif ($event->event_name === 'add_to_cart') { $cssClass = 'cart'; $icon = 'bi-cart-plus-fill'; }
                            elseif ($event->event_name === 'search') { $cssClass = 'search'; $icon = 'bi-search'; }
                        @endphp
                        <div class="timeline-item {{ $cssClass }}">
                            <div class="timeline-icon"><i class="bi {{ $icon }}"></i></div>
                            <div class="timeline-content">
                                <div class="timeline-time">
                                    {{ $event->created_at->format('h:i:s A') }} 
                                    <span class="text-muted fw-normal ms-2">(+{{ $event->created_at->diffInSeconds($session->started_at) }}s from start)</span>
                                </div>
                                <div class="timeline-event-name">{{ $event->event_name }}</div>
                                
                                @if($event->page_url)
                                    <div class="small text-muted mt-1 text-truncate" title="{{ $event->page_url }}">
                                        <i class="bi bi-link-45deg"></i> {{ parse_url($event->page_url, PHP_URL_PATH) ?? '/' }}
                                    </div>
                                @endif

                                @if($event->product)
                                    <div class="small fw-medium text-primary mt-1">
                                        <i class="bi bi-box-seam"></i> {{ $event->product->name }}
                                    </div>
                                @endif

                                @if($event->meta && count($event->meta) > 0)
                                    <div class="timeline-meta">
                                        <pre class="mb-0" style="font-size: 0.75rem;">{{ json_encode($event->meta, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
