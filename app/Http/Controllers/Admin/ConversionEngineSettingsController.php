<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SettingsService;

class ConversionEngineSettingsController extends Controller
{
    public function index()
    {
        return view('admin.conversion_engine.settings');
    }

    public function store(Request $request, SettingsService $settingsService)
    {
        $settings = $request->input('settings', []);

        // Default toggles to false if unchecked
        $settings['commerce.conversion_engine.capture_offer.engine_enabled'] = isset($settings['commerce.conversion_engine.capture_offer.engine_enabled']);
        $settings['commerce.conversion_engine.capture_offer.recovery_enabled'] = isset($settings['commerce.conversion_engine.capture_offer.recovery_enabled']);
        $settings['commerce.conversion_engine.capture_offer.recovery_dry_run'] = isset($settings['commerce.conversion_engine.capture_offer.recovery_dry_run']);

        // Handle the abandonment sequence array structure
        if (isset($settings['commerce.conversion_engine.abandonment_sequence'])) {
            // It comes in as an array of steps. We need to save the whole array.
            $sequence = array_values($settings['commerce.conversion_engine.abandonment_sequence']);
            $settingsService->set('commerce.conversion_engine.abandonment_sequence', $sequence);
            unset($settings['commerce.conversion_engine.abandonment_sequence']);
        }

        // Save flat settings
        foreach ($settings as $key => $value) {
            $settingsService->set($key, $value);
        }

        return redirect()->back()->with('success', 'Conversion Engine settings updated successfully.');
    }
}
