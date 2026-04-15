<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;

class PolicyController extends Controller
{
    public function show(string $type, SettingsService $settings)
    {
        $allowed = ['privacy', 'returns', 'shipping', 'terms'];
        $key = str_replace('-', '_', $type);
        
        abort_unless(in_array($key, $allowed), 404);
        
        $content = $settings->get('policies.' . $key);
        abort_unless($content, 404);

        // If the user pasted a full HTML document (from a generator like Shopify), strip the document wrappers
        // so it can render safely inside our Blade layout containing the header and footer.
        $content = preg_replace('/<!DOCTYPE[^>]*>/i', '', $content);
        $content = preg_replace('/<html[^>]*>/i', '', $content);
        $content = preg_replace('/<\/html>/i', '', $content);
        $content = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $content);
        $content = preg_replace('/<body[^>]*>/i', '', $content);
        $content = preg_replace('/<\/body>/i', '', $content);

        $titles = [
            'privacy' => 'Privacy Policy',
            'returns' => 'Return Policy',
            'shipping' => 'Shipping Policy',
            'terms' => 'Terms & Conditions',
        ];

        return view('storefront.policy', [
            'title' => $titles[$key] ?? 'Policy',
            'content' => $content
        ]);
    }
}
