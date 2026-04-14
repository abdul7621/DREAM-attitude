@extends('layouts.admin')
@section('title', 'Import Job #' . $importJob->id)
@section('content')
@php
    $stats = $importJob->stats ?? [];
    [$source, $type] = explode('_', $importJob->source . '_', 2);
    $type = rtrim($type, '_');
    $importErrors = $stats['errors'] ?? [];
@endphp

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('admin.import.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="h4 mb-0">Import Job #{{ $importJob->id }}</h1>
        <div class="text-muted small mt-1">
            <span class="badge bg-light text-dark">{{ ucfirst($source) }}</span>
            {{ ucfirst($type) }} •
            <span class="badge bg-{{ match($importJob->status) {'completed'=>'success','failed'=>'danger','previewed'=>'info',default=>'secondary'} }}">{{ ucfirst($importJob->status) }}</span>
            • {{ $importJob->created_at->format('d M Y H:i') }}
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-bar-chart me-2"></i>Results</div>
    <div class="card-body">
        <div class="row g-3 text-center">
            @foreach($stats as $key => $val)
                @if(!in_array($key, ['errors', 'dry_run', 'type', 'file_hash', 'sample_rows']) && !is_array($val))
                    <div class="col-auto">
                        <div class="h4 fw-bold mb-0 {{ is_numeric($val) && $val > 0 ? 'text-primary' : 'text-muted' }}">{{ is_numeric($val) ? number_format($val) : $val }}</div>
                        <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

{{-- Errors --}}
@if(count($importErrors) > 0)
<div class="card shadow-sm border-danger">
    <div class="card-header fw-semibold text-danger bg-danger bg-opacity-10 d-flex justify-content-between align-items-center">
        <div><i class="bi bi-exclamation-triangle me-2"></i>{{ count($importErrors) }} Errors</div>
        <a href="{{ route('admin.import.exportErrors', $importJob) }}" class="btn btn-sm btn-outline-danger">Download CSV</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-sm table-striped mb-0 small">
                <thead class="table-light sticky-top">
                    <tr><th style="width:50px">#</th><th>Error Details</th></tr>
                </thead>
                <tbody>
                    @foreach($importErrors as $i => $err)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td class="text-danger">{{ is_string($err) ? $err : json_encode($err) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="alert alert-success">
    <i class="bi bi-check-circle me-1"></i> No errors reported for this import.
</div>
@endif

@if($importJob->error_log)
<div class="card shadow-sm mt-4 border-danger">
    <div class="card-header fw-semibold text-danger">System Error Log</div>
    <div class="card-body">
        <pre class="mb-0 small text-danger">{{ $importJob->error_log }}</pre>
    </div>
</div>
@endif
@endsection
