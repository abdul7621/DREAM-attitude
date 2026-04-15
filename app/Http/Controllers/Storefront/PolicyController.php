<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;

class PolicyController extends Controller
{
    public function show(string $type, SettingsService $settings)
    {
        $allowed = ['privacy_policy', 'return_policy', 'shipping_policy', 'terms_conditions'];
        $key = str_replace('-', '_', $type);
        
        abort_unless(in_array($key, $allowed), 404);
        
        $content = $settings->get('policies.' . $key);
        abort_unless($content, 404);

        // If the user pasted a full HTML document (from a generator like Shopify), return it raw
        if (str_contains(strtolower($content), '<html') || str_contains(strtolower($content), '<!doctype html>')) {
            return response($content);
        }

        $titles = [
            'privacy_policy' => 'Privacy Policy',
            'return_policy' => 'Return Policy',
            'shipping_policy' => 'Shipping Policy',
            'terms_conditions' => 'Terms & Conditions',
        ];

        return view('storefront.policy', [
            'title' => $titles[$key] ?? 'Policy',
            'content' => $content
        ]);
    }
}
