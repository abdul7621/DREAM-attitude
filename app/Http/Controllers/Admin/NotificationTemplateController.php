<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationTemplateController extends Controller
{
    public function index(): View
    {
        $templates = NotificationTemplate::orderBy('name')->get();
        return view('admin.notification_templates.index', compact('templates'));
    }

    public function edit(NotificationTemplate $notificationTemplate): View
    {
        return view('admin.notification_templates.edit', compact('notificationTemplate'));
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate): RedirectResponse
    {
        $data = $request->validate([
            'subject'   => 'nullable|string|max:255',
            'body'      => 'required|string',
            'is_active' => 'boolean',
        ]);

        $notificationTemplate->update([
            'subject'   => $data['subject'] ?? null,
            'body'      => $data['body'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.notification-templates.index')
                         ->with('success', 'Notification template updated successfully.');
    }
}
