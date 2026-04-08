@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Orders</h1>
        <div>
            <form action="{{ route('admin.orders.export-csv') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-earmark-spreadsheet text-success"></i> Export CSV</button>
            </form>
        </div>
    </div>

    {{-- ── Filters ──────────────────────────────────────────── --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search order, name, phone…" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach (\App\Models\Order::STATUS_LABELS as $key => $meta)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment" class="form-select form-select-sm">
                        <option value="">All Payment</option>
                        <option value="cod" {{ request('payment') === 'cod' ? 'selected' : '' }}>COD</option>
                        <option value="razorpay" {{ request('payment') === 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Table ─────────────────────────────────────────────── --}}
    <form action="{{ route('admin.orders.bulk') }}" method="POST">
    @csrf
    <div class="d-flex mb-3 align-items-center gap-2 bg-light p-2 rounded border">
        <select name="action" class="form-select form-select-sm w-auto" required>
            <option value="">-- Apply Bulk Action --</option>
            <option value="confirmed">Mark as Confirmed</option>
            <option value="packed">Mark as Packed</option>
        </select>
        <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to apply this bulk action?')">Apply</button>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="selectAll"></th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th class="text-end">Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $o)
                        <tr>
                            <td><input class="form-check-input order-checkbox" type="checkbox" name="order_ids[]" value="{{ $o->id }}"></td>
                            <td class="fw-semibold">{{ $o->order_number }}</td>
                            <td>
                                {{ $o->customer_name }}
                                <br><span class="small text-muted">{{ $o->phone }}</span>
                            </td>
                            <td class="text-end fw-semibold">₹{{ number_format((float) $o->grand_total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $o->payment_method === 'cod' ? 'warning text-dark' : 'primary' }}" style="font-size:.7rem;">{{ strtoupper($o->payment_method) }}</span>
                                <span class="badge bg-{{ $o->paymentColor() }}" style="font-size:.65rem;">{{ $o->paymentLabel() }}</span>
                            </td>
                            <td><span class="badge bg-{{ $o->statusColor() }}">{{ $o->statusLabel() }}</span></td>
                            <td class="small text-muted">{{ $o->placed_at?->format('d M Y, h:i A') ?? '—' }}</td>
                            <td><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </form>
    <div class="mt-3">{{ $orders->links() }}</div>

    <script>
        document.getElementById('selectAll')?.addEventListener('change', function() {
            document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
        });
    </script>
@endsection
