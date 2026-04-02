@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <h1 class="h4 mb-3">Orders</h1>
    <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($orders as $o)
                    <tr>
                        <td>{{ $o->id }}</td>
                        <td>{{ $o->order_number }}</td>
                        <td>{{ $o->customer_name }}<br><span class="small text-muted">{{ $o->phone }}</span></td>
                        <td>₹{{ number_format((float) $o->grand_total, 2) }}</td>
                        <td>{{ $o->payment_method }} / {{ $o->payment_status }}</td>
                        <td>{{ $o->order_status }}</td>
                        <td><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endsection
