<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::orderBy('name')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        return view('admin.menus.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255', 'unique:menus'],
            'is_active' => ['boolean']
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Menu::create($data);

        Cache::forget('menus.*');

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    public function edit(Menu $menu): View
    {
        $menu->load(['parentItems.children' => function($q) {
            $q->orderBy('sort_order');
        }]);
        return view('admin.menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255', 'unique:menus,location,' . $menu->id],
            'is_active' => ['boolean']
        ]);

        $data['is_active'] = $request->boolean('is_active', false);
        $menu->update($data);

        Cache::forget('menus.' . $menu->location);
        Cache::forget('menus.*');

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        Cache::forget('menus.' . $menu->location);
        Cache::forget('menus.*');
        
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }
}
