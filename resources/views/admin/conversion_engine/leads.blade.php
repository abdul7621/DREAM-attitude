@extends('layouts.admin')

@section('title', 'Recovery Leads Console')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Recovery Leads Console</h4>
            <div class="text-muted small">Manage and manually recover captured checkout abandoners.</div>
        </div>
        <div class="text-end">
            <div class="text-muted small fw-bold text-uppercase">Potential Recoverable Revenue</div>
            <h3 class="mb-0 text-success fw-bold">₹{{ number_format($potentialRevenue, 0) }}</h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form action="{{ route('admin.captured-leads.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="visually-hidden" for="search">Search</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="Search phone..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" name="status">
                        <option value="">All Statuses</option>
                        <option value="New" {{ request('status') === 'New' ? 'selected' : '' }}>New</option>
                        <option value="Contacted" {{ request('status') === 'Contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="Interested" {{ request('status') === 'Interested' ? 'selected' : '' }}>Interested</option>
                        <option value="Converted" {{ request('status') === 'Converted' ? 'selected' : '' }}>Converted</option>
                        <option value="Dead" {{ request('status') === 'Dead' ? 'selected' : '' }}>Dead</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" name="filter">
                        <option value="">All Time</option>
                        <option value="fresh" {{ request('filter') === 'fresh' ? 'selected' : '' }}>Fresh Today</option>
                        <option value="abandoned_24h" {{ request('filter') === 'abandoned_24h' ? 'selected' : '' }}>Abandoned > 24h</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    <a href="{{ route('admin.captured-leads.index') }}" class="btn btn-sm btn-light">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Leads Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 text-uppercase small text-muted px-4 py-3">Lead Phone</th>
                        <th class="border-0 text-uppercase small text-muted py-3">Cart / Value</th>
                        <th class="border-0 text-uppercase small text-muted py-3">Time & Source</th>
                        <th class="border-0 text-uppercase small text-muted py-3">Status & Notes</th>
                        <th class="border-0 text-uppercase small text-muted py-3 text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $cart)
                        @php
                            $cartTotal = 0;
                            $productNames = [];
                            foreach($cart->items as $item) {
                                $cartTotal += ($item->variant->price_retail ?? 0) * $item->qty;
                                $productNames[] = $item->variant->product->name ?? 'Unknown Product';
                            }
                        @endphp
                        <tr>
                            <td class="px-4">
                                <div class="fw-bold fs-6">{{ $cart->guest_phone }}</div>
                                @if($cart->offer_claimed)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 10px;">{{ $cart->offer_claimed }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">₹{{ number_format($cartTotal, 0) }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;" title="{{ implode(', ', $productNames) }}">
                                    {{ count($productNames) }} items ({{ implode(', ', $productNames) }})
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $cart->captured_at ? \Carbon\Carbon::parse($cart->captured_at)->diffForHumans() : 'Unknown' }}</div>
                                <div class="small text-muted">
                                    {{ $cart->lead_source ?? 'Direct' }} | 
                                    Step: {{ $cart->abandoned_reminder_step }} | 
                                    Last Act: {{ $cart->last_activity_at ? \Carbon\Carbon::parse($cart->last_activity_at)->diffForHumans() : 'N/A' }}
                                </div>
                            </td>
                            <td style="min-width: 250px;">
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-sm status-select" data-id="{{ $cart->id }}" style="width: auto;">
                                        <option value="New" {{ $cart->lead_status === 'New' ? 'selected' : '' }}>New</option>
                                        <option value="Contacted" {{ $cart->lead_status === 'Contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="Interested" {{ $cart->lead_status === 'Interested' ? 'selected' : '' }}>Interested</option>
                                        <option value="Converted" {{ $cart->lead_status === 'Converted' ? 'selected' : '' }}>Converted</option>
                                        <option value="Dead" {{ $cart->lead_status === 'Dead' ? 'selected' : '' }}>Dead</option>
                                    </select>
                                    <span class="save-indicator text-success small d-none" id="save-indicator-{{ $cart->id }}"><i class="bi bi-check-circle-fill"></i> Saved</span>
                                </div>
                                <textarea class="form-control form-control-sm notes-input" data-id="{{ $cart->id }}" rows="1" placeholder="Add notes...">{{ $cart->lead_notes }}</textarea>
                            </td>
                            <td class="text-end pe-4">
                                <a href="https://wa.me/91{{ ltrim($cart->guest_phone, '+91') }}?text=Hi, I noticed you left some items in your cart..." target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-whatsapp"></i> Chat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No recovery leads found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $leads->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const updateLead = async (cartId, data, indicator) => {
            try {
                const response = await fetch(`/admin/settings/conversion-engine/leads/${cartId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                if(response.ok) {
                    indicator.classList.remove('d-none');
                    setTimeout(() => indicator.classList.add('d-none'), 2000);
                }
            } catch(e) {
                console.error(e);
                alert("Failed to save lead info.");
            }
        };

        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const id = this.getAttribute('data-id');
                const notes = document.querySelector(`.notes-input[data-id="${id}"]`).value;
                const indicator = document.getElementById(`save-indicator-${id}`);
                updateLead(id, { lead_status: this.value, lead_notes: notes }, indicator);
            });
        });

        // Debounce notes saving
        let timeout = null;
        document.querySelectorAll('.notes-input').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                const id = this.getAttribute('data-id');
                const status = document.querySelector(`.status-select[data-id="${id}"]`).value;
                const indicator = document.getElementById(`save-indicator-${id}`);
                
                timeout = setTimeout(() => {
                    updateLead(id, { lead_status: status, lead_notes: this.value }, indicator);
                }, 1000);
            });
        });
    });
</script>
@endpush
