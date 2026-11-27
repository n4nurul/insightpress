<?php

namespace App\Core\Services;

use App\Models\Category;
use App\Models\Post;

class CmsDataService
{
    public function getMenuCategories()
    {
        return Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('show_in_menu', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getHomeData(): array
    {
        $siteMode = cms_setting('site_mode', 'news');

        if ($siteMode === 'business') {
            return [
                'services' => Post::published()->where('type', 'service')->latest('published_at')->take(8)->get(),
                'blogs' => Post::published()->where('type', 'news')->latest('published_at')->take(3)->get(),
                'team' => Post::published()->where('type', 'team')->orderBy('id')->get(),
                'ads' => $this->getAdsMap(),
            ];
        }

        $breakingNews = cms_addon('quick_breaking', false)
            ? Post::published()->where('type', 'news')->where('is_breaking', true)->latest('published_at')->take(10)->get()
            : collect();

        $leadNews = Post::published()->with('category')->where('type', 'news')->where('is_featured', true)->latest('published_at')->take(5)->get();
        $latestNews = Post::published()->with('category')->where('type', 'news')->latest('published_at')->take(12)->get();
        $popularNews = Post::published()->with('category')->where('type', 'news')->orderByDesc('views_count')->take(8)->get();

        $categoryBlocks = Category::whereNull('parent_id')
            ->where('type', 'news')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                $cat->setRelation('latest_posts', Post::published()->where('category_id', $cat->id)->latest('published_at')->take(5)->get());
                return $cat;
            });

        return [
            'breakingNews' => $breakingNews,
            'leadNews' => $leadNews,
            'latestNews' => $latestNews,
            'popularNews' => $popularNews,
            'categoryBlocks' => $categoryBlocks,
            'ads' => $this->getAdsMap(),
        ];
    }

    public function getAdsMap(): array
    {
        if (!cms_addon('ad_slots', true)) {
            return [];
        }

        return Post::published()->where('type', 'ad')->get()->keyBy('slug')->toArray();
    }
}
