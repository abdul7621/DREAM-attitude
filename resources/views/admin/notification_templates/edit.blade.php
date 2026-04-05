@extends('layouts.admin')
@section('title', 'Edit Notification Template')
@section('content')

<div class="mb-4">
    <a href="{{ route('admin.notification-templates.index') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Back to Templates
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Edit {{ ucfirst(str_replace('_', ' ', $notificationTemplate->name)) }}</span>
                @if($notificationTemplate->channel === 'email')
                    <span class="badge bg-secondary"><i class="bi bi-envelope"></i> Email</span>
                @elseif($notificationTemplate->channel === 'whatsapp')
                    <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                @else
                    <span class="badge bg-dark">{{ strtoupper($notificationTemplate->channel) }}</span>
                @endif
            </div>
            <div class="card-body">
                <form action="{{ route('admin.notification-templates.update', $notificationTemplate) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $notificationTemplate->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Enable this notification</label>
                    </div>

                    @if($notificationTemplate->channel === 'email')
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Subject</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject', $notificationTemplate->subject) }}" required>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message Body</label>
                        <textarea name="body" class="form-control font-monospace text-sm" rows="12" required style="font-size: 0.85rem;">{{ old('body', $notificationTemplate->body) }}</textarea>
                        <div class="form-text text-muted mt-2">
                            Use variables from the reference guide on the right. E.g. Hello {customer_name}!
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary"></i> Available Variables</h6>
                <p class="small text-muted mb-3">You can dynamically insert this data by placing the text exactly as shown, wrapped in {braces}.</p>
                
                @if($notificationTemplate->variables_guide && is_array($notificationTemplate->variables_guide))
                    <ul class="list-group list-group-flush rounded border mb-0">
                        @foreach($notificationTemplate->variables_guide as $var => $desc)
                            <li class="list-group-item bg-white px-3 py-2 small d-flex flex-column gap-1">
                                <code class="bg-light p-1 rounded d-inline-block text-dark fw-semibold">{!! $var !!}</code>
                                <span class="text-muted" style="font-size:0.75rem;">{{ $desc }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="alert alert-secondary py-2 small mb-0">
                        No variable documentation available for this template.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
