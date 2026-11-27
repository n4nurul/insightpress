<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')
            ->orderBy('sort_order')
            ->get()
            ->each(function ($cat) {
                $cat->total_posts_count = Post::inCategory($cat->id)->count();
            });

        $parents = Category::whereNull('parent_id')->orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories', 'parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:news,service',
            'sort_order' => 'nullable|integer',
            'show_in_menu' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $slug = bn_slug($data['name']);
        if (Category::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(10, 99);
        }

        Category::create([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'],
            'sort_order' => $data['sort_order'] ?? 0,
            'show_in_menu' => $request->boolean('show_in_menu'),
            'is_active' => true,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        $this->clearCmsCache();
        return back()->with('success', 'Category added successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:news,service',
            'sort_order' => 'nullable|integer',
            'show_in_menu' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        $parentId = ($data['parent_id'] ?? null) == $category->id ? null : ($data['parent_id'] ?? null);

        $category->update([
            'name' => $data['name'],
            'parent_id' => $parentId,
            'type' => $data['type'],
            'sort_order' => $data['sort_order'] ?? 0,
            'show_in_menu' => $request->boolean('show_in_menu'),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        $this->clearCmsCache();
        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        $this->clearCmsCache();
        return back()->with('success', 'Category deleted successfully.');
    }

    protected function clearCmsCache(): void
    {
        Cache::forget('cms_menu_categories');
        Cache::forget('cms_home_data');
    }
}
