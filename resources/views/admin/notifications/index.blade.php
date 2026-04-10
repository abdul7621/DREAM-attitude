@extends('layouts.admin')
@section('title', 'Notification Logs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><i class="bi bi-bell me-1"></i> Notification Logs</h1>
</div>

{{-- Filters --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="channel" class="form-select form-select-sm">
                    <option value="">All Channels</option>
                    <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="type" class="form-control form-control-sm" placeholder="Event type…" value="{{ request('type') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
<div class="table-responsive">
<table class="table table-sm table-hover mb-0 align-middle">
    <thead class="table-light"><tr>
        <th>ID</th><th>Channel</th><th>Event</th><th>To</th><th>Status</th><th>Time</th>
    </tr></thead>
    <tbody>
    @forelse ($logs as $log)
        <tr>
            <td class="small">{{ $log->id }}</td>
            <td>
                @if($log->channel === 'whatsapp')
                    <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                @else
                    <span class="badge bg-primary"><i class="bi bi-envelope"></i> Email</span>
                @endif
            </td>
            <td class="small fw-semibold">{{ $log->event }}</td>
            <td class="small text-muted">{{ $log->to_address ?? '—' }}</td>
            <td>
                <span class="badge bg-{{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                    {{ ucfirst($log->status) }}
                </span>
            </td>
            <td class="small text-muted">{{ $log->created_at?->format('d M Y H:i') ?? '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No notification logs yet.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
