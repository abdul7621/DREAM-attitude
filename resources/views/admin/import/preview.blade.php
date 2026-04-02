@extends('layouts.admin')
@section('title', 'Import Preview')
@section('content')
<h1 class="h4 mb-3">Import Preview — Dry Run</h1>

@php $stats = $importJob->stats ?? []; @endphp

<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">What will be imported</div>
    <div class="card-body">
        <div class="row g-3 text-center">
            <div class="col-3"><div class="h3 fw-bold">{{ $stats['products'] ?? '?' }}</div><div class="text-muted small">Products</div></div>
            <div class="col-3"><div class="h3 fw-bold">{{ $stats['variants'] ?? '?' }}</div><div class="text-muted small">Variants</div></div>
            <div class="col-3"><div class="h3 fw-bold">{{ $stats['categories'] ?? '?' }}</div><div class="text-muted small">Categories</div></div>
            <div class="col-3"><div class="h3 fw-bold">{{ $stats['images'] ?? '?' }}</div><div class="text-muted small">Images</div></div>
        </div>
        <p class="text-muted mt-3 mb-0"><small>Note: Images are downloaded from source URLs after confirmation. This may take a few minutes.</small></p>
    </div>
</div>

<div class="d-flex gap-2">
    <form action="{{ route('admin.import.confirm', $importJob) }}" method="post">
        @csrf
        <button type="submit" class="btn btn-success" onclick="return confirm('Start real import? This will write to your catalog.')">
            ✓ Confirm & Import
        </button>
    </form>
    <a href="{{ route('admin.import.index') }}" class="btn btn-secondary">← Cancel</a>
</div>
@endsection
