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
        $all = $request->except(['_token', '_method']);

        // Default toggles to false if unchecked
        $all['commerce__conversion_engine__capture_offer__engine_enabled'] = isset($all['commerce__conversion_engine__capture_offer__engine_enabled']) ? '1' : '0';
        $all['commerce__conversion_engine__capture_offer__recovery_enabled'] = isset($all['commerce__conversion_engine__capture_offer__recovery_enabled']) ? '1' : '0';
        $all['commerce__conversion_engine__capture_offer__recovery_dry_run'] = isset($all['commerce__conversion_engine__capture_offer__recovery_dry_run']) ? '1' : '0';

        // Handle the abandonment sequence array structure
        if (isset($all['abandonment_sequence'])) {
            $sequence = array_values($all['abandonment_sequence']);
            $settingsService->set('commerce.conversion_engine.abandonment_sequence', $sequence);
            unset($all['abandonment_sequence']);
        }

        foreach ($all as $key => $value) {
            $dotKey = str_replace('__', '.', (string) $key);
            $settingsService->set($dotKey, $value);
        }

        return redirect()->back()->with('success', 'Conversion Engine settings updated successfully.');
    }
}
