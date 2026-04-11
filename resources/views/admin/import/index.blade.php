@extends('layouts.admin')
@section('title', 'Import Wizard')
@section('content')
<h1 class="h4 mb-1">Import Wizard</h1>
<p class="text-muted small mb-4">Migrate your data from Shopify or WooCommerce. Products, customers, and orders are supported.</p>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-cloud-arrow-up me-2"></i>Upload CSV</div>
            <div class="card-body">
                <form action="{{ route('admin.import.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-medium">Platform</label>
                    <select name="source" class="form-select" required>
                        <option value="shopify">Shopify</option>
                        <option value="woo">WooCommerce</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Data Type</label>
                    <select name="type" class="form-select" required>
                        <option value="products">Products + Variants + Images</option>
                        <option value="customers">Customers + Addresses</option>
                        <option value="orders">Orders (Historical)</option>
                    </select>
                    <div class="form-text mt-1">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Tip:</strong> Import products first, then customers, then orders for best results.
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">CSV File <span class="text-muted">(max 20MB)</span></label>
                    <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-upload me-1"></i> Upload & Preview
                </button>
                </form>
            </div>
        </div>

        {{-- Safety notice --}}
        <div class="card shadow-sm mt-3 border-warning">
            <div class="card-body small">
                <div class="fw-bold text-warning mb-1"><i class="bi bi-shield-check me-1"></i>Safe Migration</div>
                <ul class="mb-0 ps-3">
                    <li>Products: creates or updates by SKU/slug (no duplicates)</li>
                    <li>Customers: skips existing emails (no password reset)</li>
                    <li>Orders: imported as <strong>historical</strong> — no inventory deduction, no notifications, no tracking events</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold"><i class="bi bi-clock-history me-2"></i>Recent Import Jobs</div>
            <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Type</th><th>Status</th><th>Results</th><th>Date</th></tr></thead>
                <tbody>
                @forelse ($jobs as $j)
                    @php [$src, $typ] = explode('_', $j->source . '_', 2); @endphp
                    <tr>
                        <td>{{ $j->id }}</td>
                        <td>
                            <span class="badge bg-light text-dark">{{ ucfirst($src) }}</span>
                            <span class="text-muted small">{{ rtrim(ucfirst($typ), '_') }}</span>
                        </td>
                        <td><span class="badge bg-{{ match($j->status) {'completed'=>'success','failed'=>'danger','previewed'=>'info',default=>'secondary'} }}">{{ ucfirst($j->status) }}</span></td>
                        <td class="small">
                            @if($j->stats)
                                @if(isset($j->stats['products']))
                                    P:{{ $j->stats['products'] }}
                                @endif
                                @if(isset($j->stats['variants']))
                                    V:{{ $j->stats['variants'] }}
                                @endif
                                @if(isset($j->stats['images']))
                                    Img:{{ $j->stats['images'] }}
                                @endif
                                @if(isset($j->stats['customers']))
                                    C:{{ $j->stats['customers'] }}
                                @endif
                                @if(isset($j->stats['orders']))
                                    O:{{ $j->stats['orders'] }}
                                @endif
                                @if(isset($j->stats['line_items']))
                                    Items:{{ $j->stats['line_items'] }}
                                @endif
                                @if(!empty($j->stats['errors']))
                                    <span class="text-danger">{{ count($j->stats['errors']) }} err</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $j->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No imports yet. Upload a CSV to get started.</td></tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection
