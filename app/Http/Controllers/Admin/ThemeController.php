<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\ImageOptimizerService;
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
            'theme.hero_slides' => [],
            'theme.trust_text' => 'Authentic | Secure Checkout | Easy Returns',
            'theme.announcement_active' => '0',
            'theme.announcement_text' => '',
            'theme.offers_banner_text' => '',
            'theme.offers_banner_link' => '',
            'theme.offers_banner_image' => '',
        ];

        $theme = array_merge($defaults, $settings);
        
        // Extract only the string keys for the view so isset($available[$key]) doesn't crash.
        $sections = json_decode($theme['theme.home_sections'], true) ?? [];
        $activeKeys = [];
        foreach ($sections as $sec) {
            if (is_string($sec)) {
                $activeKeys[] = $sec;
            } elseif (is_array($sec) && isset($sec['key'])) {
                $activeKeys[] = $sec['key'];
            }
        }
        $theme['theme.home_sections'] = $activeKeys;

        $productsList = \App\Models\Product::select('id', 'name', 'sku')->orderBy('name')->get();

        return view('admin.theme.index', compact('theme', 'tab', 'productsList'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tab = $request->input('_tab', 'theme');

        if ($tab === 'homepage') {
            try {
                \Illuminate\Support\Facades\Log::info('Theme Save Request Data', $request->all());

                $data = $request->validate([
                    'home_sections'                => ['nullable', 'array'],
                    'theme_hero_title'             => ['nullable', 'string', 'max:255'],
                    'theme_hero_title_suffix'      => ['nullable', 'string', 'max:255'],
                    'theme_hero_eyebrow'           => ['nullable', 'string', 'max:255'],
                    'theme_hero_award_text'        => ['nullable', 'string', 'max:255'],
                    'theme_hero_subtitle'          => ['nullable', 'string', 'max:500'],
                    'theme_hero_cta_text'          => ['nullable', 'string', 'max:100'],
                    'theme_hero_cta_link'          => ['nullable', 'string', 'max:255'],
                    'theme_hero_cta2_text'         => ['nullable', 'string', 'max:100'],
                    'theme_hero_cta2_link'         => ['nullable', 'string', 'max:255'],
                    'theme_hero_image'             => ['nullable', 'image', 'max:4096'],
                    'theme_trust_text'             => ['nullable', 'string', 'max:255'],
                    'theme_brand_story_title'      => ['nullable', 'string', 'max:255'],
                    'theme_brand_story_text'       => ['nullable', 'string', 'max:2000'],
                    'theme_brand_story_link'       => ['nullable', 'string', 'max:255'],
                    'theme_announcement_active'    => ['nullable', 'boolean'],
                    'theme_announcement_text'      => ['nullable', 'string', 'max:255'],
                    'theme_offers_banner_text'     => ['nullable', 'string', 'max:255'],
                    'theme_offers_banner_link'     => ['nullable', 'string', 'max:255'],
                    'theme_offers_banner_image'    => ['nullable', 'image', 'max:4096'],
                    'slide_links'                  => ['nullable', 'array', 'max:10'],
                    'slide_links.*'                => ['nullable', 'string', 'max:255'],
                    'slide_alts'                   => ['nullable', 'array', 'max:10'],
                    'slide_alts.*'                 => ['nullable', 'string', 'max:255'],
                    'slide_existing'               => ['nullable', 'array', 'max:10'],
                    'slide_existing.*'             => ['nullable', 'string', 'max:255'],
                    'slide_images'                 => ['nullable', 'array', 'max:10'],
                    'slide_images.*'               => ['nullable', 'image', 'max:4096'],
                    'trust_strip'                  => ['nullable', 'array', 'max:8'],
                    'trust_strip.*.val'            => ['nullable', 'string', 'max:100'],
                    'trust_strip.*.label'          => ['nullable', 'string', 'max:100'],
                    'usp_strip'                    => ['nullable', 'array', 'max:8'],
                    'usp_strip.*.icon'             => ['nullable', 'string', 'max:50'],
                    'usp_strip.*.title'            => ['nullable', 'string', 'max:150'],
                    'usp_strip.*.desc'             => ['nullable', 'string', 'max:255'],
                    'benefits_items'               => ['nullable', 'array', 'max:6'],
                    'benefits_items.*.icon'         => ['nullable', 'string', 'max:50'],
                    'benefits_items.*.label'        => ['nullable', 'string', 'max:100'],
                    'award_stats'                  => ['nullable', 'array', 'max:3'],
                    'award_stats.*.num'            => ['nullable', 'string', 'max:50'],
                    'award_stats.*.label'          => ['nullable', 'string', 'max:50'],
                    'award_images'                 => ['nullable', 'array', 'max:4'],
                    'award_images.*'               => ['nullable', 'image', 'max:3072'],
                    'award_images_existing'        => ['nullable', 'array', 'max:4'],
                    'award_images_existing.*'      => ['nullable', 'string', 'max:255'],
                    'award_images_remove'          => ['nullable', 'array', 'max:4'],
                    'problem_matrix'               => ['nullable', 'array', 'max:6'],
                    'problem_matrix.*.problem'     => ['nullable', 'string', 'max:255'],
                    'problem_matrix.*.products'    => ['nullable', 'string', 'max:100'],
                ]);

                // Normalize sections
                $sections = $request->input('home_sections');
                
                if (is_string($sections)) {
                    $sections = json_decode($sections, true);
                }

                if (!is_array($sections)) {
                    $sections = [];
                }

                $normalized = [];
                foreach ($sections as $section) {
                    // OLD FORMAT (Or form checkbox slice)
                    if (is_string($section)) {
                        $normalized[] = [
                            'key' => $section,
                            'enabled' => true
                        ];
                    }
                    // NEW FORMAT
                    elseif (is_array($section) && isset($section['key'])) {
                        $normalized[] = [
                            'key' => $section['key'],
                            'enabled' => $section['enabled'] ?? true
                        ];
                    }
                }

                // FINAL SAFETY
                if (empty($normalized)) {
                    $normalized = [
                        ['key' => 'hero', 'enabled' => true],
                        ['key' => 'categories', 'enabled' => true],
                    ];
                }

                Setting::updateOrCreate(
                    ['key' => 'theme.home_sections'], 
                    ['value' => json_encode($normalized)]
                );

                if ($request->hasFile('theme_hero_image')) {
                    $path = $request->file('theme_hero_image')->store('theme', 'public');
                    $path = app(ImageOptimizerService::class)->optimize($path, ImageOptimizerService::MAX_HERO);
                    Setting::updateOrCreate(['key' => 'theme.hero_image'], ['value' => $path]);
                }

                // ── Process Hero Slides ──────────────────────────────
                $slideLinks = $request->input('slide_links', []);
                $slideExisting = $request->input('slide_existing', []);
                $slideExistingMobile = $request->input('slide_existing_mobile', []);
                $slideAlts = $request->input('slide_alts', []);
                $slides = [];
                foreach ($slideLinks as $i => $link) {
                    $imagePath = null;
                    if ($request->hasFile("slide_images.$i")) {
                        $file = $request->file("slide_images.$i");
                        if ($file->isValid()) {
                            $imagePath = $file->store('theme', 'public');
                            $imagePath = app(ImageOptimizerService::class)->optimize($imagePath, ImageOptimizerService::MAX_HERO);
                        }
                    }
                    if (!$imagePath && !empty($slideExisting[$i])) {
                        $imagePath = $slideExisting[$i];
                    }

                    $imageMobilePath = null;
                    if ($request->hasFile("slide_images_mobile.$i")) {
                        $file = $request->file("slide_images_mobile.$i");
                        if ($file->isValid()) {
                            $imageMobilePath = $file->store('theme', 'public');
                            $imageMobilePath = app(ImageOptimizerService::class)->optimize($imageMobilePath, ImageOptimizerService::MAX_HERO);
                        }
                    }
                    if (!$imageMobilePath && !empty($slideExistingMobile[$i])) {
                        $imageMobilePath = $slideExistingMobile[$i];
                    }

                    // Strict requirement: System ignores incomplete slides
                    if ($imagePath && $imageMobilePath) {
                        $slides[] = [
                            'image' => $imagePath,
                            'image_mobile' => $imageMobilePath,
                            'link' => $link ?: '',
                            'alt' => $slideAlts[$i] ?? '',
                        ];
                    }
                }
                Setting::updateOrCreate(
                    ['key' => 'theme.hero_slides'],
                    ['value' => $slides]
                );

                if ($request->hasFile('theme_offers_banner_image')) {
                    $path = $request->file('theme_offers_banner_image')->store('theme', 'public');
                    $path = app(ImageOptimizerService::class)->optimize($path, ImageOptimizerService::MAX_HERO);
                    Setting::updateOrCreate(['key' => 'theme.offers_banner_image'], ['value' => $path]);
                }

                // Save all simple string settings
                $keys = [
                    'theme_hero_title', 'theme_hero_title_suffix', 'theme_hero_eyebrow', 'theme_hero_award_text',
                    'theme_hero_subtitle', 'theme_hero_cta_text', 'theme_hero_cta_link',
                    'theme_hero_cta2_text', 'theme_hero_cta2_link',
                    'theme_trust_text', 'theme_announcement_text',
                    'theme_brand_story_title', 'theme_brand_story_text', 'theme_brand_story_link',
                    'theme_offers_banner_text', 'theme_offers_banner_link',
                    'theme_home_seo_title', 'theme_home_seo_description', 'theme_home_seo_content',
                    'theme_instagram_handle',
                ];
                foreach ($keys as $key) {
                    if (array_key_exists($key, $data)) {
                        $val = $data[$key];
                        $val = ($val === null) ? '' : (string) $val;
                        Setting::updateOrCreate(['key' => str_replace('theme_', 'theme.', $key)], ['value' => $val]);
                    }
                }
                Setting::updateOrCreate(['key' => 'theme.announcement_active'], ['value' => $request->boolean('theme_announcement_active') ? '1' : '0']);
                Setting::updateOrCreate(['key' => 'theme.hero_overlay_enabled'], ['value' => $request->boolean('theme_hero_overlay_enabled') ? '1' : '0']);

                // Save Trust Strip items (array)
                $trustStrip = $request->input('trust_strip', []);
                if (!empty($trustStrip)) {
                    Setting::updateOrCreate(
                        ['key' => 'theme.trust_strip_items'],
                        ['value' => json_encode(array_values($trustStrip))]
                    );
                }

                // Save USP Strip items (array)
                $uspStrip = $request->input('usp_strip', []);
                if (!empty($uspStrip)) {
                    Setting::updateOrCreate(
                        ['key' => 'theme.usp_strip_items'],
                        ['value' => json_encode(array_values($uspStrip))]
                    );
                }

                // Save Problem-Solution Matrix items (array)
                $problemMatrix = $request->input('problem_matrix', []);
                if (!empty($problemMatrix)) {
                    foreach ($problemMatrix as &$pm) {
                        if (isset($pm['products']) && is_array($pm['products'])) {
                            $pm['products'] = implode(',', $pm['products']);
                        }
                    }
                    Setting::updateOrCreate(
                        ['key' => 'theme.problem_matrix'],
                        ['value' => json_encode(array_values($problemMatrix))]
                    );
                }


                // Save Benefits Strip items (array)
                $benefitsItems = $request->input('benefits_items', []);
                if (!empty($benefitsItems)) {
                    Setting::updateOrCreate(
                        ['key' => 'theme.benefits_items'],
                        ['value' => json_encode(array_values($benefitsItems))]
                    );
                }

                // Save Award Stats (array)
                $awardStats = $request->input('award_stats', []);
                if (!empty($awardStats)) {
                    Setting::updateOrCreate(
                        ['key' => 'theme.award_stats'],
                        ['value' => json_encode(array_values($awardStats))]
                    );
                }

                // ── Process Award Section Images (4 slots) ──────────────
                $awardImagesExisting = $request->input('award_images_existing', []);
                $awardImagesRemove   = $request->input('award_images_remove', []);
                $finalAwardImages    = [];
                for ($ai = 0; $ai < 4; $ai++) {
                    // If admin checked "Remove" for this slot, skip it
                    if (!empty($awardImagesRemove[$ai])) {
                        continue;
                    }
                    // New upload takes priority
                    if ($request->hasFile("award_images.$ai")) {
                        $file = $request->file("award_images.$ai");
                        if ($file->isValid()) {
                            $path = $file->store('theme/award', 'public');
                            $path = app(ImageOptimizerService::class)->optimize($path, 800);
                            $finalAwardImages[] = $path;
                            continue;
                        }
                    }
                    // Keep existing if no new upload
                    if (!empty($awardImagesExisting[$ai])) {
                        $finalAwardImages[] = $awardImagesExisting[$ai];
                    }
                }
                Setting::updateOrCreate(
                    ['key' => 'theme.award_images'],
                    ['value' => json_encode($finalAwardImages)]
                );

                Cache::forget('commerce.settings.array');
                return redirect()->route('admin.theme.index', ['tab' => 'homepage'])->with('success', 'Homepage configured successfully.');

            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Theme save error: ' . $e->getMessage());
                return back()->with('error', 'Save failed: ' . $e->getMessage());
            }
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
            if (array_key_exists($key, $data)) {
                $val = $data[$key];
                $val = ($val === null) ? '' : (string) $val;
                $dbKey = str_replace('theme_', 'theme.', $key);
                Setting::updateOrCreate(['key' => $dbKey], ['value' => $val]);
            }
        }

        Cache::forget('commerce.settings.array');

        return redirect()->route('admin.theme.index')->with('success', 'Theme settings updated securely.');
    }
}
