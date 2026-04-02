<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function edit(): View
    {
        $groups = [
            'store'    => $this->settings->group('store'),
            'seo'      => $this->settings->group('seo'),
            'tracking' => $this->settings->group('tracking'),
            'payment'  => $this->settings->group('payment'),
            'notify'   => $this->settings->group('notify'),
            'shipping' => $this->settings->group('shipping'),
        ];

        return view('admin.settings.edit', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $all = $request->except(['_token', '_method']);

        foreach ($all as $key => $value) {
            // Convert dot notation keys (group_key → group.key)
            $dotKey = str_replace('__', '.', (string) $key);
            $this->settings->set($dotKey, (string) $value);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved.');
    }
}
