@extends('layouts.admin')
@section('title', 'Files')

@push('admin-styles')
<style>
    /* ── Shopify-style Files UI ─────────────────────────────────── */
    .files-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .files-header h4 { margin: 0; font-weight: 700; font-size: 1.25rem; color: #1e293b; }

    .files-toolbar {
        display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .files-search {
        flex: 1; min-width: 200px; max-width: 400px; position: relative;
    }
    .files-search input {
        width: 100%; padding: 7px 12px 7px 36px;
        border: 1px solid #d1d5db; border-radius: 8px;
        font-size: 0.85rem; background: #fff; transition: border 0.15s;
    }
    .files-search input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .files-search i {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: 0.9rem;
    }

    /* Folder tabs */
    .folder-tabs {
        display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb; padding-bottom: 0;
    }
    .folder-tab {
        padding: 8px 16px; border-radius: 6px 6px 0 0;
        font-size: 0.8rem; font-weight: 500; color: #64748b;
        text-decoration: none; border: 1px solid transparent;
        border-bottom: 2px solid transparent; transition: all 0.15s;
        background: none; cursor: pointer;
    }
    .folder-tab:hover { color: #1e293b; background: #f8fafc; }
    .folder-tab.active {
        color: #1e293b; font-weight: 600;
        border-bottom: 2px solid #2563eb; background: #fff;
    }

    /* Files table */
    .files-table { width: 100%; border-collapse: collapse; }
    .files-table thead th {
        padding: 10px 14px; font-size: 0.72rem; font-weight: 600;
        color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;
        border-bottom: 1px solid #e5e7eb; background: #f8fafc;
        white-space: nowrap; user-select: none;
    }
    .files-table tbody tr {
        border-bottom: 1px solid #f1f5f9; transition: background 0.1s;
    }
    .files-table tbody tr:hover { background: #f8fafc; }
    .files-table tbody td {
        padding: 10px 14px; font-size: 0.85rem; color: #334155;
        vertical-align: middle;
    }
    .files-table .cb-col { width: 40px; text-align: center; }
    .files-table .cb-col input[type="checkbox"] {
        width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;
    }
    .file-info { display: flex; align-items: center; gap: 12px; }
    .file-thumb {
        width: 40px; height: 40px; border-radius: 6px;
        object-fit: cover; border: 1px solid #e5e7eb; flex-shrink: 0;
        background: #f1f5f9;
    }
    .file-name {
        font-weight: 500; color: #1e293b; cursor: pointer;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        max-width: 280px; display: block;
    }
    .file-name:hover { color: #2563eb; }
    .file-ext { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; }
    .file-alt { color: #64748b; font-size: 0.8rem; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .file-dims { color: #64748b; font-size: 0.8rem; white-space: nowrap; }
    .file-size { color: #64748b; font-size: 0.8rem; white-space: nowrap; }
    .file-date { color: #64748b; font-size: 0.8rem; white-space: nowrap; }
    .file-actions { display: flex; gap: 4px; align-items: center; }
    .file-actions button, .file-actions a {
        padding: 4px 8px; border-radius: 6px; font-size: 0.78rem;
        border: 1px solid #e5e7eb; background: #fff; color: #475569;
        cursor: pointer; transition: all 0.15s; text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .file-actions button:hover, .file-actions a:hover {
        background: #f1f5f9; border-color: #cbd5e1;
    }
    .file-actions .btn-del:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }

    /* Bulk action bar */
    .bulk-bar {
        display: none; align-items: center; gap: 12px;
        padding: 10px 16px; background: #eff6ff; border-radius: 8px;
        margin-bottom: 1rem; font-size: 0.85rem; color: #1e40af;
    }
    .bulk-bar.show { display: flex; }
    .bulk-bar button {
        padding: 5px 14px; border-radius: 6px; font-size: 0.8rem;
        border: 1px solid #bfdbfe; background: #fff; color: #1e40af;
        cursor: pointer; transition: all 0.15s;
    }
    .bulk-bar button:hover { background: #dbeafe; }
    .bulk-bar .btn-bulk-del { border-color: #fca5a5; color: #dc2626; }
    .bulk-bar .btn-bulk-del:hover { background: #fef2f2; }

    /* Per page selector */
    .per-page-select {
        display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #64748b;
    }
    .per-page-select select {
        padding: 4px 8px; border: 1px solid #d1d5db; border-radius: 6px;
        font-size: 0.8rem; cursor: pointer;
    }

    /* Sync modal */
    .sync-result {
        padding: 16px; background: #f0fdf4; border: 1px solid #bbf7d0;
        border-radius: 8px; font-size: 0.9rem; color: #166534;
    }
    .sync-result.scanning { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

    /* Upload btn */
    .upload-btn-wrap { position: relative; }
    .upload-btn-wrap input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer;
    }

    /* Empty state */
    .files-empty {
        text-align: center; padding: 80px 20px; color: #94a3b8;
    }
    .files-empty i { font-size: 3rem; margin-bottom: 12px; display: block; }
    .files-empty h5 { color: #64748b; margin-bottom: 8px; }

    /* Toast */
    .media-toast {
        position: fixed; bottom: 24px; right: 24px;
        background: #1e293b; color: #fff; padding: 10px 20px;
        border-radius: 8px; font-size: 0.85rem; z-index: 9999;
        opacity: 0; transform: translateY(10px);
        transition: all 0.25s ease;
    }
    .media-toast.show { opacity: 1; transform: translateY(0); }

    @media (max-width: 768px) {
        .files-table .hide-mobile { display: none; }
        .file-name { max-width: 160px; }
    }
</style>
@endpush

@section('content')

{{-- ── Header ──────────────────────────────────────────────── --}}
<div class="files-header">
    <h4><i class="bi bi-folder2-open me-2"></i> Files</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnSyncStorage"
                data-bs-toggle="modal" data-bs-target="#syncModal">
            <i class="bi bi-arrow-repeat"></i> Sync Storage
        </button>
        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data"
              id="uploadForm" class="upload-btn-wrap">
            @csrf
            <input type="hidden" name="folder" value="{{ $folder === 'all' ? 'media' : $folder }}">
            <button type="button" class="btn btn-sm btn-primary">
                <i class="bi bi-cloud-arrow-up me-1"></i> Upload files
            </button>
            <input type="file" name="files[]" multiple accept="image/*" id="fileInput"
                   onchange="document.getElementById('uploadForm').submit(); this.disabled=true;">
        </form>
    </div>
</div>

{{-- ── Folder Tabs ─────────────────────────────────────────── --}}
<div class="folder-tabs">
    <a href="{{ route('admin.media.index', ['search' => $search, 'per_page' => $perPage]) }}"
       class="folder-tab {{ $folder === 'all' ? 'active' : '' }}">All</a>
    @foreach($folders->sort() as $f)
        <a href="{{ route('admin.media.index', ['folder' => $f, 'search' => $search, 'per_page' => $perPage]) }}"
           class="folder-tab {{ $folder === $f ? 'active' : '' }}">{{ ucfirst($f) }}</a>
    @endforeach
</div>

{{-- ── Search + Per Page ───────────────────────────────────── --}}
<div class="files-toolbar">
    <div class="files-search">
        <i class="bi bi-search"></i>
        <form method="GET" action="{{ route('admin.media.index') }}" id="searchForm">
            <input type="hidden" name="folder" value="{{ $folder }}">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by filename or alt text…"
                   autocomplete="off">
        </form>
    </div>
    <div class="ms-auto per-page-select">
        <span>Show</span>
        <select onchange="window.location.href='{{ route('admin.media.index') }}?folder={{ $folder }}&search={{ $search }}&per_page='+this.value">
            @foreach([50, 100, 200] as $pp)
                <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- ── Bulk Actions Bar ────────────────────────────────────── --}}
<div class="bulk-bar" id="bulkBar">
    <span><strong id="bulkCount">0</strong> selected</span>
    <button type="button" class="btn-bulk-del" onclick="bulkDeleteSelected()">
        <i class="bi bi-trash3 me-1"></i> Delete
    </button>
    <button type="button" style="margin-left:auto;" onclick="clearSelection()">Cancel</button>
</div>

{{-- ── Files Table ─────────────────────────────────────────── --}}
<div class="card">
    <div class="card-body p-0">
        @if($media->count())
        <table class="files-table">
            <thead>
                <tr>
                    <th class="cb-col"><input type="checkbox" id="selectAll" title="Select all"></th>
                    <th>File name</th>
                    <th class="hide-mobile">Alt text</th>
                    <th class="hide-mobile">Dimensions</th>
                    <th>Size</th>
                    <th class="hide-mobile">Date added</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($media as $item)
                <tr data-id="{{ $item->id }}">
                    <td class="cb-col">
                        <input type="checkbox" class="file-cb" value="{{ $item->id }}">
                    </td>
                    <td>
                        <div class="file-info">
                            @if($item->isImage())
                                <img src="{{ $item->url }}" alt="{{ $item->alt_text }}"
                                     class="file-thumb" loading="lazy"
                                     onclick="openMediaModal({{ $item->toJson() }})">
                            @else
                                <div class="file-thumb d-flex align-items-center justify-content-center">
                                    <i class="bi bi-file-earmark" style="font-size:1.2rem;color:#94a3b8;"></i>
                                </div>
                            @endif
                            <div>
                                <span class="file-name" title="{{ $item->filename }}"
                                      onclick="openMediaModal({{ $item->toJson() }})">
                                    {{ $item->filename }}
                                </span>
                                <span class="file-ext">{{ strtoupper(pathinfo($item->filename, PATHINFO_EXTENSION)) }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="hide-mobile">
                        <span class="file-alt" title="{{ $item->alt_text }}">{{ $item->alt_text ?? '—' }}</span>
                    </td>
                    <td class="hide-mobile">
                        <span class="file-dims">{{ $item->dimensions }}</span>
                    </td>
                    <td>
                        <span class="file-size">{{ number_format($item->size_bytes / 1024, 1) }} KB</span>
                    </td>
                    <td class="hide-mobile">
                        <span class="file-date">{{ $item->created_at->format('j M Y') }}</span>
                    </td>
                    <td>
                        <div class="file-actions" style="justify-content:flex-end;">
                            <button type="button" onclick="copyUrl('{{ $item->url }}')" title="Copy URL">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                            <button type="button" onclick="openMediaModal({{ $item->toJson() }})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this file permanently?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-del" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="files-empty">
            <i class="bi bi-images"></i>
            <h5>No files found{{ !empty($search) ? ' for "' . $search . '"' : '' }}</h5>
            <p>{{ !empty($search) ? 'Try a different search term.' : 'Upload files or sync storage to get started.' }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Pagination --}}
<div class="mt-3 d-flex justify-content-between align-items-center">
    <small class="text-muted">{{ $media->total() }} total files</small>
    {{ $media->links() }}
</div>

{{-- ── Sync Storage Modal ──────────────────────────────────── --}}
<div class="modal fade" id="syncModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i> Sync Storage</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Scans the server storage for images not yet indexed in the database, and removes orphaned references.</p>
                <div id="syncResult" class="sync-result scanning" style="display:none;"></div>
                <div id="syncPreviewResult" style="display:none;" class="mt-3">
                    <div class="d-flex gap-3 mb-3">
                        <div class="p-3 bg-light rounded flex-fill text-center">
                            <div class="fw-bold fs-5 text-primary" id="syncNewCount">0</div>
                            <small class="text-muted">New files found</small>
                        </div>
                        <div class="p-3 bg-light rounded flex-fill text-center">
                            <div class="fw-bold fs-5 text-warning" id="syncOrphanCount">0</div>
                            <small class="text-muted">Orphaned refs</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary w-100" id="btnDoSync">
                        <i class="bi bi-check2-circle me-1"></i> Import & Clean Up
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPreviewSync">
                    <i class="bi bi-search me-1"></i> Preview Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Details Modal ──────────────────────────────────── --}}
<div class="modal fade" id="mediaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title" id="modalFilename">File Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img src="" id="modalImage" class="img-fluid rounded shadow-sm"
                         style="max-height: 220px; display: none;">
                </div>

                <form id="updateForm" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small text-muted">Public URL</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-light" id="modalUrl" readonly>
                            <button class="btn btn-primary" type="button"
                                    onclick="copyUrl(document.getElementById('modalUrl').value)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Dimensions</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="modalDims" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Size</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="modalSize" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Alt Text (SEO)</label>
                        <input type="text" name="alt_text" class="form-control form-control-sm" id="modalAltText"
                               placeholder="Describe this image for search engines…">
                    </div>
                    <button type="submit" class="btn btn-sm btn-dark w-100">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="media-toast" id="mediaToast"></div>

@endsection

@push('scripts')
<script>
    /* ── Toast ──────────────────────────────────────────────── */
    function showToast(msg) {
        const t = document.getElementById('mediaToast');
        t.textContent = msg; t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2500);
    }

    /* ── Copy URL ──────────────────────────────────────────── */
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => showToast('✓ URL copied!'));
    }

    /* ── Media Detail Modal ────────────────────────────────── */
    const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));

    function openMediaModal(item) {
        document.getElementById('modalFilename').textContent = item.filename;
        document.getElementById('modalUrl').value = item.url;
        document.getElementById('modalAltText').value = item.alt_text || '';
        document.getElementById('modalDims').value =
            (item.width && item.height) ? item.width + ' × ' + item.height : '—';
        document.getElementById('modalSize').value =
            (item.size_bytes / 1024).toFixed(1) + ' KB';

        const form = document.getElementById('updateForm');
        form.action = `/admin/media/${item.id}`;

        const img = document.getElementById('modalImage');
        if (item.mime_type && item.mime_type.startsWith('image/')) {
            img.src = item.url;
            img.style.display = 'inline-block';
        } else {
            img.style.display = 'none';
        }
        mediaModal.show();
    }

    /* ── Checkbox / Bulk Select ─────────────────────────────── */
    const selectAll = document.getElementById('selectAll');
    const bulkBar = document.getElementById('bulkBar');
    const bulkCount = document.getElementById('bulkCount');

    function getChecked() { return document.querySelectorAll('.file-cb:checked'); }

    function updateBulkBar() {
        const n = getChecked().length;
        bulkCount.textContent = n;
        if (n > 0) { bulkBar.classList.add('show'); } else { bulkBar.classList.remove('show'); }
    }
    function clearSelection() {
        document.querySelectorAll('.file-cb').forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false;
        updateBulkBar();
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.file-cb').forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });
    }
    document.querySelectorAll('.file-cb').forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    function bulkDeleteSelected() {
        const ids = Array.from(getChecked()).map(cb => parseInt(cb.value));
        if (!ids.length) return;
        if (!confirm(`Delete ${ids.length} file(s) permanently?`)) return;

        fetch('{{ route("admin.media.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message);
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) row.remove();
                });
                clearSelection();
            }
        })
        .catch(() => alert('Bulk delete failed.'));
    }

    /* ── Sync Storage ──────────────────────────────────────── */
    const btnPreview = document.getElementById('btnPreviewSync');
    const btnDoSync  = document.getElementById('btnDoSync');
    const syncResult = document.getElementById('syncResult');
    const syncPreviewResult = document.getElementById('syncPreviewResult');

    btnPreview.addEventListener('click', function() {
        syncResult.style.display = 'block';
        syncResult.className = 'sync-result scanning';
        syncResult.textContent = 'Scanning server storage…';
        syncPreviewResult.style.display = 'none';
        this.disabled = true;

        fetch('{{ route("admin.media.sync.preview") }}')
            .then(r => r.json())
            .then(data => {
                syncResult.style.display = 'none';
                syncPreviewResult.style.display = 'block';
                document.getElementById('syncNewCount').textContent = data.new_files;
                document.getElementById('syncOrphanCount').textContent = data.orphans;
                btnPreview.disabled = false;
            })
            .catch(() => {
                syncResult.textContent = 'Scan failed. Try again.';
                syncResult.className = 'sync-result';
                syncResult.style.background = '#fef2f2';
                syncResult.style.borderColor = '#fca5a5';
                syncResult.style.color = '#dc2626';
                btnPreview.disabled = false;
            });
    });

    btnDoSync.addEventListener('click', function() {
        syncResult.style.display = 'block';
        syncResult.className = 'sync-result scanning';
        syncResult.textContent = 'Importing files and cleaning up…';
        syncPreviewResult.style.display = 'none';
        this.disabled = true;

        fetch('{{ route("admin.media.sync.execute") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                syncResult.className = 'sync-result';
                syncResult.innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + data.message;
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(() => {
            syncResult.textContent = 'Sync failed. Try again.';
            syncResult.className = 'sync-result';
            syncResult.style.background = '#fef2f2';
            btnDoSync.disabled = false;
        });
    });
</script>
@endpush
