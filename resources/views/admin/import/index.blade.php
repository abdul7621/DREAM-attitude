@extends('layouts.admin')
@section('title', 'Import Wizard')
@section('content')
<h1 class="h4 mb-3">Import Wizard</h1>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Step 1 — Upload CSV</div>
            <div class="card-body">
                <form action="{{ route('admin.import.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Platform</label>
                    <select name="source" class="form-select" required>
                        <option value="shopify">Shopify</option>
                        <option value="woo">WooCommerce</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Data Type</label>
                    <select name="type" class="form-select" required>
                        <option value="products">Products + Variants + Images</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">CSV File (max 20MB)</label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload & Preview →</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Recent Import Jobs</div>
            <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Source</th><th>Status</th><th>Stats</th><th>Date</th></tr></thead>
                <tbody>
                @forelse ($jobs as $j)
                    <tr>
                        <td>{{ $j->id }}</td>
                        <td>{{ $j->source }}</td>
                        <td><span class="badge bg-{{ match($j->status) {'completed'=>'success','failed'=>'danger','previewed'=>'info',default=>'secondary'} }}">{{ $j->status }}</span></td>
                        <td>
                            @if($j->stats)
                                P:{{ $j->stats['products'] ?? '?' }} V:{{ $j->stats['variants'] ?? '?' }} Img:{{ $j->stats['images'] ?? '?' }}
                            @else —
                            @endif
                        </td>
                        <td>{{ $j->created_at->format('d M') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-2">No imports yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection
