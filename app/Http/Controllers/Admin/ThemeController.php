<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'theme');
        $settings = Setting::where('key', 'like', 'theme.%')->pluck('value', 'key')->toArray();

        $defaults = [
            'theme.primary_color' => '#0d6efd',
            'theme.secondary_color' => '#6c757d',
            'theme.font_family' => 'Inter, sans-serif',
            'theme.logo' => '',
            'theme.favicon' => '',
            'theme.border_radius' => '0.375rem',
            'theme.button_style' => 'rounded',
            'theme.card_shadow' => 'shadow-sm',
            'theme.spacing_scale' => '1',
            'theme.home_sections' => json_encode(['hero', 'categories', 'featured', 'bestsellers', 'trust', 'reviews']),
            'theme.hero_title' => 'Premium Quality Products',
            'theme.hero_subtitle' => 'Handcrafted specifically for you.',
            'theme.hero_cta_text' => 'Shop Now',
            'theme.hero_cta_link' => '/search',
            'theme.hero_image' => '',
            'theme.trust_text' => 'Authentic | Secure Checkout | Easy Returns',
        ];

        $theme = array_merge($defaults, $settings);
        $theme['theme.home_sections'] = json_decode($theme['theme.home_sections'], true) ?? [];

        return view('admin.theme.index', compact('theme', 'tab'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->input('_tab', 'theme');

        if ($tab === 'homepage') {
            $data = $request->validate([
                'home_sections' => ['nullable', 'array'],
                'theme_hero_title' => ['nullable', 'string', 'max:255'],
                'theme_hero_subtitle' => ['nullable', 'string', 'max:255'],
                'theme_hero_cta_text' => ['nullable', 'string', 'max:100'],
                'theme_hero_cta_link' => ['nullable', 'string', 'max:255'],
                'theme_hero_image' => ['nullable', 'image', 'max:4096'],
                'theme_trust_text' => ['nullable', 'string', 'max:255'],
                'theme_announcement_active' => ['nullable', 'boolean'],
                'theme_announcement_text' => ['nullable', 'string', 'max:255'],
                'theme_offers_banner_text' => ['nullable', 'string', 'max:255'],
                'theme_offers_banner_link' => ['nullable', 'string', 'max:255'],
                'theme_offers_banner_image' => ['nullable', 'image', 'max:4096'],
            ]);

            // Save ordered sections
            $sections = $data['home_sections'] ?? [];
            // Remove duplicates and maintain order (user submitted the array in order using basic array logic from blade if possible, or hidden fields. Wait, basic list ordering can be handled by just submitting the array of enabled section keys).
            Setting::updateOrCreate(['key' => 'theme.home_sections'], ['value' => json_encode(array_values($sections))]);

            if ($request->hasFile('theme_hero_image')) {
                $path = $request->file('theme_hero_image')->store('theme', 'public');
                Setting::updateOrCreate(['key' => 'theme.hero_image'], ['value' => $path]);
            }
            if ($request->hasFile('theme_offers_banner_image')) {
                $path = $request->file('theme_offers_banner_image')->store('theme', 'public');
                Setting::updateOrCreate(['key' => 'theme.offers_banner_image'], ['value' => $path]);
            }

            $keys = ['theme_hero_title', 'theme_hero_subtitle', 'theme_hero_cta_text', 'theme_hero_cta_link', 'theme_trust_text', 'theme_announcement_text', 'theme_offers_banner_text', 'theme_offers_banner_link'];
            foreach ($keys as $key) {
                if (array_key_exists($key, $data)) {
                    Setting::updateOrCreate(['key' => str_replace('_', '.', $key)], ['value' => $data[$key]]);
                }
            }
            Setting::updateOrCreate(['key' => 'theme.announcement_active'], ['value' => $request->boolean('theme_announcement_active') ? '1' : '0']);

            Cache::forget('settings.all');
            return redirect()->route('admin.theme.index', ['tab' => 'homepage'])->with('success', 'Homepage configured successfully.');
        }

        // Theme Tab validation
        $data = $request->validate([
            'theme_primary_color' => ['nullable', 'string', 'max:20'],
            'theme_secondary_color' => ['nullable', 'string', 'max:20'],
            'theme_font_family' => ['nullable', 'string', 'max:100'],
            'theme_border_radius' => ['nullable', 'string', 'max:20'],
            'theme_button_style' => ['nullable', 'string', 'max:50'],
            'theme_card_shadow' => ['nullable', 'string', 'max:50'],
            'theme_spacing_scale' => ['nullable', 'numeric', 'min:0.5', 'max:2'],
            'theme_logo' => ['nullable', 'image', 'max:2048'],
            'theme_favicon' => ['nullable', 'image', 'max:1024', 'mimes:ico,png,jpg'],
        ]);

        // Handle Image Uploads
        if ($request->hasFile('theme_logo')) {
            $path = $request->file('theme_logo')->store('theme', 'public');
            Setting::updateOrCreate(['key' => 'theme.logo'], ['value' => $path]);
        }
        
        if ($request->hasFile('theme_favicon')) {
            $path = $request->file('theme_favicon')->store('theme', 'public');
            Setting::updateOrCreate(['key' => 'theme.favicon'], ['value' => $path]);
        }

        // Save Text Settings
        $keys = [
            'theme_primary_color', 'theme_secondary_color', 'theme_font_family',
            'theme_border_radius', 'theme_button_style', 'theme_card_shadow', 'theme_spacing_scale'
        ];

        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $dbKey = str_replace('_', '.', $key);
                Setting::updateOrCreate(['key' => $dbKey], ['value' => $data[$key]]);
            }
        }

        Cache::forget('settings.all');

        return redirect()->route('admin.theme.index')->with('success', 'Theme settings updated securely.');
    }
}
