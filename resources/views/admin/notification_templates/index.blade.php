@extends('layouts.admin')
@section('title', 'Notification Templates')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">Notification Templates</h1>
    <p class="text-muted small mb-0">Manage automated email & WhatsApp messages</p>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Template Name</th>
                    <th>Channel</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $template->name)) }}</div>
                            <small class="text-muted">{{ $template->name }}</small>
                        </td>
                        <td>
                            @if($template->channel === 'email')
                                <span class="badge bg-secondary"><i class="bi bi-envelope"></i> Email</span>
                            @elseif($template->channel === 'whatsapp')
                                <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                            @else
                                <span class="badge bg-dark">{{ strtoupper($template->channel) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Disabled</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.notification-templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No templates found. Run database seeder to initialize core templates.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
