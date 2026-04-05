@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Audit Logs</h1>
</div>

<div class="card mb-4">
    <div class="card-body bg-light rounded">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($adminUsers as $admin)
                        <option value="{{ $admin->id }}" @selected(request('user_id') == $admin->id)>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" @selected(request('action') == $act)>{{ ucfirst(str_replace('_', ' ', $act)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Date</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter Logs</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Model Affected</th>
                    <th>Changes Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="small">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                    <td class="small fw-semibold">{{ $log->user->name ?? 'System/Guest' }}</td>
                    <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                    <td class="small">
                        @if($log->model_type)
                            <div>{{ class_basename($log->model_type) }} #{{ $log->model_id }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small">
                        @if($log->old_values || $log->new_values)
                            <button class="btn btn-xs btn-outline-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                View Diff
                            </button>
                            
                            <!-- Modal -->
                            <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title h6">Log Record Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 border-end">
                                                    <h6 class="text-danger small fw-bold mb-2">OLD VALUES</h6>
                                                    <pre class="bg-light p-2 rounded" style="font-size:0.75rem;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-success small fw-bold mb-2">NEW VALUES</h6>
                                                    <pre class="bg-light p-2 rounded" style="font-size:0.75rem;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">No audit logs found matching criteria.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer py-2">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
