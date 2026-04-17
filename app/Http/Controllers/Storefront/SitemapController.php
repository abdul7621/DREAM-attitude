<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = [];
        $urls[] = ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => now()->toAtomString()];

        foreach (Category::query()->where('is_active', true)->orderBy('id')->cursor() as $cat) {
            $urls[] = ['loc' => route('category.show', $cat, true), 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => $cat->updated_at?->toAtomString()];
        }

        foreach (Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('id')->cursor() as $p) {
            $urls[] = ['loc' => route('product.show', $p, true), 'changefreq' => 'weekly', 'priority' => '0.7', 'lastmod' => $p->updated_at?->toAtomString()];
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('pages')) {
            foreach (Page::query()->where('is_active', true)->orderBy('id')->cursor() as $page) {
                // Ignore pages if they have a noindex column (Phase 1 task)
                if (isset($page->noindex) && $page->noindex) continue;
                $urls[] = ['loc' => route('page.show', $page, true), 'changefreq' => 'monthly', 'priority' => '0.5', 'lastmod' => $page->updated_at?->toAtomString()];
            }
        }

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach ($urls as $u) {
            $lastmodStr = !empty($u['lastmod']) ? "<lastmod>{$u['lastmod']}</lastmod>" : '';
            $xml[] = '<url><loc>'.e($u['loc']).'</loc>'.$lastmodStr.'<changefreq>'.$u['changefreq'].'</changefreq><priority>'.$u['priority'].'</priority></url>';
        }
        $xml[] = '</urlset>';

        return response(implode("\n", $xml), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
