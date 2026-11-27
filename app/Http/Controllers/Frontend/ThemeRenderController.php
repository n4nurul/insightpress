<?php

namespace App\Http\Controllers\Frontend;

use App\Core\Services\CmsDataService;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class ThemeRenderController extends Controller
{
    public function __construct(protected CmsDataService $cms) {}

    public function index()
    {
        $data = $this->cms->getHomeData();
        $data['menuCategories'] = $this->cms->getMenuCategories();
        return theme_view('home', $data);
    }

    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $posts = Post::published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12);

        return theme_view('category', [
            'category' => $category,
            'posts' => $posts,
            'menuCategories' => $this->cms->getMenuCategories(),
            'ads' => $this->cms->getAdsMap(),
        ]);
    }

    public function single(string $slug)
    {
        $post = Post::published()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();
        $post->increment('views_count');

        if ($post->type === 'page') {
            return theme_view('page', [
                'post' => $post,
                'menuCategories' => $this->cms->getMenuCategories(),
            ]);
        }

        $relatedPosts = Post::published()
            ->where('type', $post->type)
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')
            ->take(4)
            ->get();

        return theme_view('single', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'menuCategories' => $this->cms->getMenuCategories(),
            'ads' => $this->cms->getAdsMap(),
        ]);
    }

    public function team()
    {
        $members = Post::published()->where('type', 'team')->orderBy('id')->get();
        return theme_view('team', [
            'members' => $members,
            'menuCategories' => $this->cms->getMenuCategories(),
        ]);
    }
}
