<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminLandingPageController extends Controller
{
    public function index()
    {
        $pages = LandingPage::latest()->get();
        return view('admin.landing-pages.index', compact('pages'));
    }

    public function create()
    {
        $products = Product::with('variants')->where('status', 'active')->orderBy('name')->get();
        return view('admin.landing-pages.form', ['page' => null, 'products' => $products]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePage($request);
        $data = $this->processUploads($request, $data);

        LandingPage::create($data);

        return redirect()->route('admin.landing-pages.index')
            ->with('status', 'Landing page created!');
    }

    public function edit(LandingPage $landing_page)
    {
        $products = Product::with('variants')->where('status', 'active')->orderBy('name')->get();
        return view('admin.landing-pages.form', ['page' => $landing_page, 'products' => $products]);
    }

    public function update(Request $request, LandingPage $landing_page)
    {
        $data = $this->validatePage($request);
        $data = $this->processUploads($request, $data, $landing_page);

        $landing_page->update($data);

        return redirect()->route('admin.landing-pages.index')
            ->with('status', 'Landing page updated!');
    }

    public function destroy(LandingPage $landing_page)
    {
        $landing_page->delete();
        return redirect()->route('admin.landing-pages.index')
            ->with('status', 'Landing page deleted.');
    }

    private function validatePage(Request $request): array
    {
        $rules = [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:255'],
            'is_active'        => ['boolean'],
            'seo_title'        => ['nullable', 'string', 'max:255'],
            'seo_description'  => ['nullable', 'string', 'max:500'],
            'hero_headline'    => ['required', 'string', 'max:255'],
            'hero_subheadline' => ['nullable', 'string', 'max:255'],
            'hero_cta_text'    => ['nullable', 'string', 'max:255'],
            'offer_price'      => ['required', 'numeric', 'min:0'],
            'original_price'   => ['nullable', 'numeric', 'min:0'],
            'offer_badge'      => ['nullable', 'string', 'max:100'],
            'show_cod_badge'   => ['boolean'],
            'show_free_ship'   => ['boolean'],
            'whatsapp_number'  => ['nullable', 'string', 'max:15'],
            'trust_description'=> ['nullable', 'string', 'max:1000'],
        ];

        $data = $request->validate($rules);

        // Ensure boolean defaults
        $data['is_active']     = $request->boolean('is_active');
        $data['show_cod_badge'] = $request->boolean('show_cod_badge');
        $data['show_free_ship'] = $request->boolean('show_free_ship');

        // Slug
        $data['slug'] = Str::slug($data['slug']);

        // JSON fields from textarea (one per line or JSON)
        $data['problem_points'] = $this->parseJsonOrLines($request->input('problem_points_raw'));
        $data['trust_points']   = $this->parseJsonOrLines($request->input('trust_points_raw'));
        $data['steps']          = json_decode($request->input('steps_json', '[]'), true) ?: [];
        $data['products']       = json_decode($request->input('products_json', '[]'), true) ?: [];
        $data['reviews']        = json_decode($request->input('reviews_json', '[]'), true) ?: [];
        $data['faq']            = json_decode($request->input('faq_json', '[]'), true) ?: [];

        return $data;
    }

    private function processUploads(Request $request, array $data, ?LandingPage $existing = null): array
    {
        if ($request->hasFile('hero_image_file')) {
            $data['hero_image'] = $request->file('hero_image_file')->store('landing-pages', 'public');
        } elseif ($existing) {
            $data['hero_image'] = $existing->hero_image;
        }

        return $data;
    }

    /**
     * Parse input as JSON array or newline-separated lines.
     */
    private function parseJsonOrLines(?string $input): array
    {
        if (empty($input)) return [];

        $decoded = json_decode($input, true);
        if (is_array($decoded)) return $decoded;

        // Treat as newline-separated
        return array_values(array_filter(array_map('trim', explode("\n", $input))));
    }
}
