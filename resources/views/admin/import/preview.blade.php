@extends('layouts.admin')
@section('title', 'Import Preview')
@section('content')
@php
    $stats = $importJob->stats ?? [];
    [$source, $type] = explode('_', $importJob->source . '_', 2);
    $type = rtrim($type, '_');
@endphp

<h1 class="h4 mb-1">Import Preview — Dry Run</h1>
<p class="text-muted small mb-4">
    <span class="badge bg-light text-dark">{{ ucfirst($source) }}</span>
    {{ ucfirst($type) }} import
</p>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold"><i class="bi bi-eye me-2"></i>What will be imported</div>
    <div class="card-body">
        <div class="row g-3 text-center">
            @if($type === 'products')
                <div class="col-3"><div class="h3 fw-bold text-primary">{{ $stats['products'] ?? 0 }}</div><div class="text-muted small">Products</div></div>
                <div class="col-3"><div class="h3 fw-bold text-primary">{{ $stats['variants'] ?? 0 }}</div><div class="text-muted small">Variants</div></div>
                <div class="col-3"><div class="h3 fw-bold text-primary">{{ $stats['categories'] ?? 0 }}</div><div class="text-muted small">Categories</div></div>
                <div class="col-3"><div class="h3 fw-bold text-primary">{{ $stats['images'] ?? 0 }}</div><div class="text-muted small">Images</div></div>
            @elseif($type === 'customers')
                <div class="col-6"><div class="h3 fw-bold text-primary">{{ $stats['customers'] ?? 0 }}</div><div class="text-muted small">Customers</div></div>
                <div class="col-6"><div class="h3 fw-bold text-muted">—</div><div class="text-muted small">Addresses will be imported if available</div></div>
            @elseif($type === 'orders')
                <div class="col-6"><div class="h3 fw-bold text-primary">{{ $stats['orders'] ?? 0 }}</div><div class="text-muted small">Orders</div></div>
                <div class="col-6"><div class="h3 fw-bold text-primary">{{ $stats['line_items'] ?? 0 }}</div><div class="text-muted small">Line Items</div></div>
            @endif
        </div>

        @if($type === 'products')
            <p class="text-muted mt-3 mb-0"><small><i class="bi bi-info-circle me-1"></i>Images are downloaded from source URLs after confirmation. This may take a few minutes.</small></p>
        @elseif($type === 'customers')
            <p class="text-muted mt-3 mb-0"><small><i class="bi bi-info-circle me-1"></i>Existing customers (matched by email) will be skipped. No passwords will be reset.</small></p>
        @elseif($type === 'orders')
            <div class="alert alert-warning mt-3 mb-0 small">
                <i class="bi bi-shield-exclamation me-1"></i>
                <strong>Historical Import:</strong> Orders will be created as records only. No inventory will be deducted, no notifications will be sent, no tracking events will fire.
            </div>
        @endif

        @if(!empty($stats['sample_rows']))
            <div class="mt-3">
                <div class="fw-semibold small mb-2">Sample Data:</div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small mb-0">
                        <thead class="table-light">
                            <tr>
                                @foreach(array_keys($stats['sample_rows'][0] ?? []) as $key)
                                    <th>{{ ucfirst($key) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['sample_rows'] as $row)
                                <tr>
                                    @foreach($row as $val)
                                        <td>{{ is_array($val) ? json_encode($val) : \Illuminate\Support\Str::limit($val, 50) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <form action="{{ route('admin.import.confirm', $importJob) }}" method="post">
        @csrf
        <button type="submit" class="btn btn-success" onclick="return confirm('Start real import? This will write to your database.')">
            <i class="bi bi-check-circle me-1"></i> Confirm & Import
        </button>
    </form>
    <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary">← Cancel</a>
</div>
@endsection
