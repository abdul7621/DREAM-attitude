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
            'checkout' => $this->settings->group('checkout'),
            'policies' => $this->settings->group('policies'),
        ];

        return view('admin.settings.edit', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->input('_tab', 'store');
        $all = $request->except(['_token', '_method', '_tab']);

        foreach ($all as $key => $value) {
            $dotKey = str_replace('__', '.', (string) $key);
            $this->settings->set($dotKey, (string) $value);
        }

        return redirect()->route('admin.settings.edit', ['tab' => $tab])->with('success', 'Settings saved.');
    }
}
