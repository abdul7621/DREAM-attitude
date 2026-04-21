@extends('layouts.admin')
@section('title', 'Importing...')
@section('content')
@php
    $type = $stats['type'] ?? 'products';
    $totalCount = $stats[$type] ?? ($stats['products'] ?? 0);
    if ($type === 'customers' && empty($totalCount)) {
        $totalCount = $stats['customers'] ?? 0;
    }
    if ($type === 'orders' && empty($totalCount)) {
        $totalCount = $stats['orders'] ?? 0;
    }
    
    $title = ucfirst($type);
@endphp

<h1 class="h4 mb-1">Importing {{ $title }}</h1>
<p class="text-muted small mb-4">Processing {{ $totalCount }} {{ $type }} in small batches. Do not close this page.</p>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold" id="statusText">Starting import...</span>
            <span class="badge bg-primary" id="progressBadge">0 / {{ $totalCount }}</span>
        </div>
        <div class="progress mb-3" style="height: 24px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" id="progressBar">0%</div>
        </div>
        <div class="row g-3 text-center" id="counters">
            @if($type === 'products')
                <div class="col-3"><div class="h4 fw-bold text-primary mb-0" id="cntProducts">0</div><div class="text-muted small">Products</div></div>
                <div class="col-3"><div class="h4 fw-bold text-primary mb-0" id="cntVariants">0</div><div class="text-muted small">Variants</div></div>
                <div class="col-3"><div class="h4 fw-bold text-primary mb-0" id="cntImages">0</div><div class="text-muted small">Images</div></div>
            @else
                <div class="col-4"><div class="h4 fw-bold text-primary mb-0" id="cntProcessed">0</div><div class="text-muted small">{{ $title }}</div></div>
                <div class="col-4"><div class="h4 fw-bold text-primary mb-0" id="cntPending">0</div><div class="text-muted small">Pending</div></div>
                <div class="col-4"><div class="h4 fw-bold text-danger mb-0" id="cntErrors">0</div><div class="text-muted small">Errors</div></div>
            @endif
            @if($type === 'products')
            <div class="col-3"><div class="h4 fw-bold text-danger mb-0" id="cntErrors">0</div><div class="text-muted small">Errors</div></div>
            @endif
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4 d-none" id="errorCard">
    <div class="card-header fw-semibold text-danger bg-danger bg-opacity-10"><i class="bi bi-exclamation-triangle me-2"></i>Errors</div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-sm table-striped mb-0 small">
                <tbody id="errorList"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-none" id="doneActions">
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> Import completed successfully!</div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.import.show', $importJob) }}" class="btn btn-primary"><i class="bi bi-eye me-1"></i> View Details</a>
        <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary">← Back to Imports</a>
    </div>
</div>

<script>
(function() {
    var chunkUrl = "{{ route('admin.import.chunk', $importJob) }}";
    var csrfToken = "{{ csrf_token() }}";
    var offset = 0;
    var limit = 5;
    var totalEstimate = {{ $totalProducts }};
    var errIdx = 0;

    function updateUI(data) {
        var pct = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('progressBar').textContent = pct + '%';
        document.getElementById('progressBadge').textContent = data.processed + ' / ' + data.total;
        if (document.getElementById('cntProducts')) {
            document.getElementById('cntProducts').textContent = data.products || 0;
            document.getElementById('cntVariants').textContent = data.variants || 0;
            document.getElementById('cntImages').textContent = data.images || 0;
            document.getElementById('cntErrors').textContent = data.errors || 0;
        } else {
            var typ = "{{ $type }}";
            document.getElementById('cntProcessed').textContent = data[typ] || 0;
            document.getElementById('cntPending').textContent = Math.max(0, data.total - (data[typ] || 0));
            document.getElementById('cntErrors').textContent = data.errors || 0;
        }
        document.getElementById('statusText').textContent = 'Processing batch... (' + data.processed + ' of ' + data.total + ')';

        if (data.chunk_errors && data.chunk_errors.length > 0) {
            document.getElementById('errorCard').classList.remove('d-none');
            var list = document.getElementById('errorList');
            data.chunk_errors.forEach(function(err) {
                errIdx++;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td class="text-muted" style="width:40px">' + errIdx + '</td><td class="text-danger">' + (typeof err === 'string' ? err : JSON.stringify(err)) + '</td>';
                list.appendChild(tr);
            });
        }
    }

    function sendChunk() {
        var formData = new FormData();
        formData.append('_token', csrfToken);
        formData.append('offset', offset);
        formData.append('limit', limit);

        fetch(chunkUrl, { method: 'POST', body: formData })
            .then(function(res) {
                if (!res.ok) return res.json().then(function(d) { throw new Error(d.error || 'Server error ' + res.status); });
                return res.json();
            })
            .then(function(data) {
                updateUI(data);
                if (data.done) {
                    document.getElementById('statusText').textContent = 'Import completed!';
                    document.getElementById('progressBar').classList.remove('progress-bar-animated');
                    document.getElementById('doneActions').classList.remove('d-none');
                } else {
                    offset = data.offset;
                    setTimeout(sendChunk, 300);
                }
            })
            .catch(function(err) {
                document.getElementById('statusText').textContent = 'Error: ' + err.message;
                document.getElementById('progressBar').classList.remove('bg-success');
                document.getElementById('progressBar').classList.add('bg-danger');
                document.getElementById('progressBar').classList.remove('progress-bar-animated');
                var retry = document.createElement('div');
                retry.className = 'mt-3';
                retry.innerHTML = '<button class="btn btn-warning" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i> Retry</button> <a href="{{ route('admin.import.index') }}" class="btn btn-outline-secondary ms-2">← Back</a>';
                document.getElementById('doneActions').classList.remove('d-none');
                document.getElementById('doneActions').innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i> ' + err.message + '</div>' + retry.outerHTML;
            });
    }

    // Start immediately
    sendChunk();
})();
</script>
@endsection
