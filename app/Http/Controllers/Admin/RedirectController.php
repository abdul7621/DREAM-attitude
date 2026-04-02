<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(): View
    {
        $redirects = Redirect::query()->orderByDesc('id')->paginate(30);

        return view('admin.redirects.index', compact('redirects'));
    }

    public function create(): View
    {
        return view('admin.redirects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => 'required|string|max:512|unique:redirects,from_path',
            'to_path'   => 'required|string|max:512',
            'http_code' => 'required|in:301,302',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Redirect::query()->create($data);

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect created.');
    }

    public function edit(Redirect $redirect): View
    {
        return view('admin.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        $data = $request->validate([
            'from_path' => 'required|string|max:512|unique:redirects,from_path,'.$redirect->id,
            'to_path'   => 'required|string|max:512',
            'http_code' => 'required|in:301,302',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $redirect->update($data);

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect updated.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();

        return redirect()->route('admin.redirects.index')->with('success', 'Redirect deleted.');
    }
}
