@extends('layouts.account')
@section('title', 'My Addresses')
@section('account-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Addresses</h1>
    @if ($addresses->count() < 5)
        <a href="{{ route('account.addresses.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Address</a>
    @endif
</div>

@if ($addresses->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-geo-alt fs-1 d-block mb-2"></i>
            No saved addresses. <a href="{{ route('account.addresses.create') }}">Add your first address →</a>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach ($addresses as $address)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 {{ $address->is_default ? 'border-primary border-2' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-secondary">{{ $address->label }}</span>
                                @if ($address->is_default)
                                    <span class="badge bg-primary ms-1">Default</span>
                                @endif
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a href="{{ route('account.addresses.edit', $address) }}" class="dropdown-item"><i class="bi bi-pencil me-1"></i>Edit</a></li>
                                    @if (!$address->is_default)
                                        <li>
                                            <form action="{{ route('account.addresses.default', $address) }}" method="post">
                                                @csrf
                                                <button type="submit" class="dropdown-item"><i class="bi bi-star me-1"></i>Set Default</button>
                                            </form>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('account.addresses.destroy', $address) }}" method="post" onsubmit="return confirm('Delete this address?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="fw-semibold mb-1">{{ $address->name }}</p>
                        <p class="small text-muted mb-1">{{ $address->address_line1 }}@if($address->address_line2), {{ $address->address_line2 }}@endif</p>
                        <p class="small text-muted mb-1">{{ $address->city }}, {{ $address->state }} — {{ $address->postal_code }}</p>
                        <p class="small text-muted mb-0"><i class="bi bi-telephone me-1"></i>{{ $address->phone }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
