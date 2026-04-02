<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private readonly SlugService $slugs) {}

    public function index(): View
    {
        $pages = Page::query()->orderByDesc('id')->paginate(30);

        return view('admin.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'slug'            => 'nullable|string|max:190',
            'content'         => 'nullable|string',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:512',
            'is_active'       => 'boolean',
        ]);

        $data['slug']      = $this->slugs->unique($data['slug'] ?: $data['title'], 'pages');
        $data['is_active'] = $request->boolean('is_active', true);

        Page::query()->create($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'slug'            => 'nullable|string|max:190',
            'content'         => 'nullable|string',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:512',
            'is_active'       => 'boolean',
        ]);

        if (! empty($data['slug']) && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->slugs->unique($data['slug'], 'pages', $page->id);
        } else {
            $data['slug'] = $page->slug;
        }
        $data['is_active'] = $request->boolean('is_active');

        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }
}
