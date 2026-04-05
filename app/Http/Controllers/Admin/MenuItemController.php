<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuItemController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'label' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:255'],
            'is_external' => ['boolean'],
            'sort_order' => ['nullable', 'integer']
        ]);

        $data['is_external'] = $request->boolean('is_external');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        MenuItem::create($data);

        $menu = Menu::find($data['menu_id']);
        if ($menu) {
            Cache::forget('menus.' . $menu->location);
            Cache::forget('menus.*');
        }

        return redirect()->back()->with('success', 'Menu item added.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'string', 'max:255'],
            'is_external' => ['boolean'],
            'sort_order' => ['nullable', 'integer']
        ]);

        $data['is_external'] = $request->boolean('is_external');
        $menuItem->update($data);

        if ($menuItem->menu) {
            Cache::forget('menus.' . $menuItem->menu->location);
            Cache::forget('menus.*');
        }

        return redirect()->back()->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->menu) {
            Cache::forget('menus.' . $menuItem->menu->location);
            Cache::forget('menus.*');
        }

        $menuItem->delete();
        return redirect()->back()->with('success', 'Menu item deleted.');
    }
}
