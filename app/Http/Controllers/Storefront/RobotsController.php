<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings
    ) {}

    public function index(): Response
    {
        $body = $this->settings->get('seo.robots_body');
        if (! is_string($body) || $body === '') {
            $body = "User-agent: *\nDisallow: /admin\nDisallow: /login\nAllow: /\n\nSitemap: ".url('/sitemap.xml');
        }

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
