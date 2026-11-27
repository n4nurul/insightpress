<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\ThemeMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $themes = available_themes();
        $activeTheme = cms_setting('active_theme', 'sylhet-view');
        $selectedTheme = $request->get('theme', array_key_exists($activeTheme, $themes) ? $activeTheme : 'sylhet-view');
        $selectedLocation = in_array($request->get('location'), ['header', 'footer']) ? $request->get('location') : 'header';

        $menuItems = ThemeMenu::with('children')
            ->where('theme', $selectedTheme)
            ->where('location', $selectedLocation)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $pages = Post::where('type', 'page')->where('status', 'published')->orderBy('title')->get();

        return view('admin.menus.index', compact(
            'themes',
            'selectedTheme',
            'selectedLocation',
            'menuItems',
            'categories',
            'pages'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'theme' => 'required|string|max:60',
            'location' => 'required|in:header,footer',
            'parent_id' => 'nullable|exists:theme_menus,id',
            'label' => 'required|string|max:120',
            'type' => 'required|in:custom,category,page',
            'url' => 'required|string|max:255',
            'open_new_tab' => 'nullable|boolean',
        ]);

        $maxOrder = ThemeMenu::where('theme', $data['theme'])
            ->where('location', $data['location'])
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order') ?? 0;

        ThemeMenu::create([
            'theme' => $data['theme'],
            'location' => $data['location'],
            'parent_id' => $data['parent_id'] ?? null,
            'label' => $data['label'],
            'type' => $data['type'],
            'url' => $data['url'],
            'sort_order' => $maxOrder + 1,
            'open_new_tab' => $request->boolean('open_new_tab'),
            'is_active' => true,
        ]);

        Cache::flush();

        return redirect()->route('admin.menus.index', [
            'theme' => $data['theme'],
            'location' => $data['location'],
        ])->with('success', 'Menu item added.');
    }

    public function bulkAddCategories(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|max:60',
            'location' => 'required|in:header,footer',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $order = ThemeMenu::where('theme', $request->theme)
            ->where('location', $request->location)
            ->whereNull('parent_id')
            ->max('sort_order') ?? 0;

        $cats = Category::whereIn('id', $request->category_ids)->orderBy('sort_order')->get();
        foreach ($cats as $cat) {
            ThemeMenu::create([
                'theme' => $request->theme,
                'location' => $request->location,
                'parent_id' => null,
                'label' => $cat->name,
                'type' => 'category',
                'url' => '/category/' . $cat->slug,
                'sort_order' => ++$order,
                'open_new_tab' => false,
                'is_active' => true,
            ]);
        }

        Cache::flush();

        return redirect()->route('admin.menus.index', [
            'theme' => $request->theme,
            'location' => $request->location,
        ])->with('success', count($cats) . ' category item(s) added.');
    }

    public function bulkAddPages(Request $request)
    {
        $request->validate([
            'theme' => 'required|string|max:60',
            'location' => 'required|in:header,footer',
            'page_ids' => 'required|array|min:1',
            'page_ids.*' => 'exists:posts,id',
        ]);

        $order = ThemeMenu::where('theme', $request->theme)
            ->where('location', $request->location)
            ->whereNull('parent_id')
            ->max('sort_order') ?? 0;

        $pages = Post::whereIn('id', $request->page_ids)->get();
        foreach ($pages as $pg) {
            ThemeMenu::create([
                'theme' => $request->theme,
                'location' => $request->location,
                'parent_id' => null,
                'label' => $pg->title,
                'type' => 'page',
                'url' => '/' . $pg->slug,
                'sort_order' => ++$order,
                'open_new_tab' => false,
                'is_active' => true,
            ]);
        }

        Cache::flush();

        return redirect()->route('admin.menus.index', [
            'theme' => $request->theme,
            'location' => $request->location,
        ])->with('success', count($pages) . ' page item(s) added.');
    }

    public function saveTree(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:theme_menus,id',
            'items.*.parent_id' => 'nullable|integer',
            'items.*.label' => 'required|string|max:120',
            'items.*.url' => 'required|string|max:255',
            'items.*.open_new_tab' => 'boolean',
        ]);

        foreach ($request->items as $index => $row) {
            ThemeMenu::where('id', $row['id'])->update([
                'parent_id' => $row['parent_id'] ?: null,
                'label' => $row['label'],
                'url' => $row['url'],
                'open_new_tab' => !empty($row['open_new_tab']),
                'sort_order' => $index + 1,
            ]);
        }

        Cache::flush();

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, ThemeMenu $menu)
    {
        $data = $request->validate([
            'parent_id' => 'nullable|exists:theme_menus,id',
            'label' => 'required|string|max:120',
            'url' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'open_new_tab' => 'nullable|boolean',
        ]);

        $menu->update([
            'parent_id' => ($data['parent_id'] ?? null) == $menu->id ? null : ($data['parent_id'] ?? null),
            'label' => $data['label'],
            'url' => $data['url'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'open_new_tab' => $request->boolean('open_new_tab'),
        ]);

        Cache::flush();
        return back()->with('success', 'Menu updated.');
    }

    public function destroy(ThemeMenu $menu)
    {
        $menu->delete();
        Cache::flush();
        return back()->with('success', 'Menu item removed.');
    }
}
