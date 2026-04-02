@extends('layouts.admin')
@section('title', 'Return #{{ $returnRequest->id }}')
@section('content')
<h1 class="h4 mb-3">Return Request #{{ $returnRequest->id }}</h1>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header">Details</div>
            <div class="card-body">
                <p><strong>Order:</strong> <a href="{{ route('admin.orders.show', $returnRequest->order) }}">{{ $returnRequest->order->order_number }}</a></p>
                <p><strong>Customer:</strong> {{ $returnRequest->order->customer_name }}</p>
                <p><strong>Reason:</strong> {{ $returnRequest->reason }}</p>
                <p><strong>Status:</strong> {{ $returnRequest->status }}</p>
                <p><strong>Resolution:</strong> {{ $returnRequest->resolution ?? 'Pending' }}</p>
                <p><strong>Store Credit:</strong> {{ $returnRequest->store_credit_amount ? '₹'.$returnRequest->store_credit_amount : '—' }}</p>
                @if($returnRequest->admin_notes)
                    <p><strong>Notes:</strong> {{ $returnRequest->admin_notes }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header">Update Status</div>
            <div class="card-body">
                <form action="{{ route('admin.returns.update', $returnRequest) }}" method="post">
                @csrf @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['approved','rejected','received','closed'] as $s)
                            <option value="{{ $s }}" {{ $returnRequest->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Resolution</label>
                    <select name="resolution" class="form-select">
                        <option value="">— None —</option>
                        <option value="refund" {{ $returnRequest->resolution === 'refund' ? 'selected' : '' }}>Cash Refund</option>
                        <option value="store_credit" {{ $returnRequest->resolution === 'store_credit' ? 'selected' : '' }}>Store Credit</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Store Credit Amount (₹)</label>
                    <input type="number" name="store_credit_amount" class="form-control" value="{{ $returnRequest->store_credit_amount }}" step="0.01" min="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Notes</label>
                    <textarea name="admin_notes" class="form-control" rows="3">{{ $returnRequest->admin_notes }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Return</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
