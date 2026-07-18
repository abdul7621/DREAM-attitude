@extends('layouts.admin')
@section('title', 'Country Shipping Rates')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Country Shipping Rates (Table Rates)</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#importCollapse">
            <i class="bi bi-cloud-arrow-up"></i> Import CSV
        </button>
        <a href="{{ route('admin.shipping-rates.create') }}" class="btn btn-primary btn-sm">+ New Rate</a>
    </div>
</div>

<!-- Import CSV Section -->
<div class="collapse mb-4" id="importCollapse">
    <div class="card card-body shadow-sm">
        <h5 class="card-title h6">Import shipping rates via CSV</h5>
        <p class="text-muted small">CSV format must have headers: <code>Country,Region/State,Zip/Postal Code,Weight (and above),Shipping Price</code>. Columns must represent: 3-letter Country code, State, ZIP, Weight (kg), and Price. Importing will overwrite existing rates.</p>
        <form action="{{ route('admin.shipping-rates.import') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <input type="file" name="file" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-primary w-100" type="submit">Upload and Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Filter/Search Bar -->
<div class="card mb-3 shadow-sm p-3">
    <form action="{{ route('admin.shipping-rates.index') }}" method="get" class="row g-2">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by country code, state, or ZIP..." value="{{ $search }}">
        </div>
        <div class="col-md-3">
            <button class="btn btn-sm btn-secondary w-100" type="submit">Search</button>
        </div>
        @if($search !== '')
            <div class="col-md-3">
                <a href="{{ route('admin.shipping-rates.index') }}" class="btn btn-sm btn-outline-secondary w-100">Clear Search</a>
            </div>
        @endif
    </form>
</div>

<!-- Table list -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Country (ISO3)</th>
                    <th>Region/State</th>
                    <th>Zip/Postal Code</th>
                    <th>Weight (and above)</th>
                    <th>Shipping Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rates as $r)
                <tr>
                    <td><strong>{{ $r->country_code }}</strong></td>
                    <td>{{ $r->region_state }}</td>
                    <td>{{ $r->zip_postal_code }}</td>
                    <td>{{ number_format($r->weight, 4) }} kg</td>
                    <td><strong>₹{{ number_format($r->price, 2) }}</strong></td>
                    <td class="text-end">
                        <a href="{{ route('admin.shipping-rates.edit', $r) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('admin.shipping-rates.destroy', $r) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this rate?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Del</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">No country shipping rates found. Upload tablerates.csv or create a rate to begin.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $rates->links() }}</div>
@endsection
