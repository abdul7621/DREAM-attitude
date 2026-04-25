@extends('layouts.admin')
@section('title', 'Media Manager')

@push('admin-styles')
<style>
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    .media-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .media-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .media-thumb {
        width: 100%;
        height: 160px;
        object-fit: cover;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        cursor: pointer;
    }
    .media-thumb.not-image {
        object-fit: contain;
        padding: 2rem;
    }
    .media-body {
        padding: 0.75rem;
    }
    .media-name {
        font-size: 0.8rem;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0.25rem;
    }
    .media-meta {
        font-size: 0.7rem;
        color: #64748b;
        display: flex;
        justify-content: space-between;
    }
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8fafc;
        transition: background 0.2s;
        cursor: pointer;
        margin-bottom: 2rem;
    }
    .upload-zone:hover, .upload-zone.dragover {
        background: #f1f5f9;
        border-color: #3b82f6;
    }
    .action-btn {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Media Manager</h4>
    <div>
        <a href="{{ route('admin.media.index', ['sync' => 1]) }}" class="btn btn-sm btn-outline-secondary me-2" onclick="return confirm('Sync database with files? This might take a moment.')">
            <i class="bi bi-arrow-repeat"></i> Sync Storage
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <!-- Upload Form -->
        <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="row align-items-center mb-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Target Folder</label>
                    <select name="folder" class="form-select form-select-sm" id="folderSelect" onchange="window.location.href='?folder='+this.value">
                        @foreach($folders as $f)
                            <option value="{{ $f }}" {{ $folder === $f ? 'selected' : '' }}>{{ ucfirst($f) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-cloud-arrow-up text-primary mb-2" style="font-size: 2rem;"></i>
                <h6 class="mb-1">Click or drag images here to upload</h6>
                <p class="text-muted small mb-0">Supports JPG, PNG, WEBP (Auto-optimized to WebP)</p>
                <input type="file" name="files[]" id="fileInput" multiple accept="image/*" class="d-none" onchange="document.getElementById('uploadForm').submit(); this.disabled=true;">
            </div>
        </form>
    </div>
</div>

<!-- Media Grid -->
<div class="media-grid">
    @forelse($media as $item)
        <div class="media-card">
            @if($item->isImage())
                <img src="{{ $item->url }}" alt="{{ $item->alt_text }}" class="media-thumb" onclick="openMediaModal({{ $item->toJson() }})">
            @else
                <i class="bi bi-file-earmark-text media-thumb not-image d-block text-center" style="font-size: 3rem; color: #94a3b8;" onclick="openMediaModal({{ $item->toJson() }})"></i>
            @endif
            <div class="media-body">
                <div class="media-name" title="{{ $item->filename }}">{{ $item->filename }}</div>
                <div class="media-meta mb-2">
                    <span>{{ number_format($item->size_bytes / 1024, 1) }} KB</span>
                    <span>{{ $item->created_at->format('M d') }}</span>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-light border action-btn flex-grow-1" onclick="copyUrl('{{ $item->url }}')">
                        <i class="bi bi-link-45deg"></i> Copy Link
                    </button>
                    <form action="{{ route('admin.media.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this file permanently?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger action-btn"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 py-5 text-center text-muted w-100" style="grid-column: 1 / -1;">
            <i class="bi bi-images mb-3 d-block" style="font-size: 3rem;"></i>
            <h5>No media found in "{{ $folder }}"</h5>
            <p>Upload some files to get started.</p>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $media->links() }}
</div>

<!-- Media Details Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title" id="modalFilename">File Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid rounded mb-3 shadow-sm" style="max-height: 250px; display: none;">
                
                <form id="updateForm" method="POST">
                    @csrf @method('PUT')
                    <div class="text-start mb-3">
                        <label class="form-label small text-muted">Public URL</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control bg-light" id="modalUrl" readonly>
                            <button class="btn btn-primary" type="button" onclick="copyUrl(document.getElementById('modalUrl').value)">Copy</button>
                        </div>
                    </div>
                    <div class="text-start mb-3">
                        <label class="form-label small text-muted">Alt Text (SEO)</label>
                        <input type="text" name="alt_text" class="form-control form-control-sm" id="modalAltText">
                    </div>
                    <button type="submit" class="btn btn-sm btn-dark w-100">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('URL copied to clipboard!');
        });
    }

    const mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
    
    function openMediaModal(item) {
        document.getElementById('modalFilename').textContent = item.filename;
        document.getElementById('modalUrl').value = item.url;
        document.getElementById('modalAltText').value = item.alt_text || '';
        
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

    // Drag and drop upload logic
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            document.getElementById('uploadForm').submit();
        }
    });
</script>
@endpush
