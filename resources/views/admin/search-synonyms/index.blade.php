@extends('layouts.admin')

@section('title', 'Search Synonyms')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Search Synonyms</h1>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="table-responsive bg-white shadow-sm rounded">
                <table class="table table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Search Term</th>
                            <th>Maps To (Replace With)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($synonyms as $s)
                        <tr>
                            <td class="font-monospace fw-semibold">{{ $s->term }}</td>
                            <td class="font-monospace text-primary fw-semibold">{{ $s->replace_with }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.search-synonyms.destroy', $s) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this synonym mapping?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No synonyms mapped yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $synonyms->links() }}</div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title h6 mb-3">Add Search Synonym</h5>
                    <form action="{{ route('admin.search-synonyms.store') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Search Term *</label>
                            <input type="text" name="term" value="{{ old('term') }}" class="form-control form-control-sm @error('term') is-invalid @enderror" placeholder="e.g. bal jhadna" required>
                            @error('term')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">The word or phrase the customer searches for (converted to lowercase).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Maps To *</label>
                            <input type="text" name="replace_with" value="{{ old('replace_with') }}" class="form-control form-control-sm @error('replace_with') is-invalid @enderror" placeholder="e.g. hair fall" required>
                            @error('replace_with')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">The standardized word/term to search the database with.</div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100">Add Mapping</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
