<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SearchSynonym;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchSynonymController extends Controller
{
    public function index(): View
    {
        $synonyms = SearchSynonym::query()->orderByDesc('id')->paginate(50);

        return view('admin.search-synonyms.index', compact('synonyms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'term'         => 'required|string|max:255|unique:search_synonyms,term',
            'replace_with' => 'required|string|max:255',
        ]);

        SearchSynonym::query()->create([
            'term'         => strtolower(trim($data['term'])),
            'replace_with' => strtolower(trim($data['replace_with'])),
        ]);

        return redirect()->route('admin.search-synonyms.index')->with('success', 'Synonym added successfully.');
    }

    public function destroy(SearchSynonym $searchSynonym): RedirectResponse
    {
        $searchSynonym->delete();

        return redirect()->route('admin.search-synonyms.index')->with('success', 'Synonym deleted successfully.');
    }
}
