<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = NotificationLog::query()->orderByDesc('id');

        if ($type = $request->get('type')) {
            $query->where('event', $type);
        }

        if ($channel = $request->get('channel')) {
            $query->where('channel', $channel);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('admin.notifications.index', compact('logs'));
    }
}
