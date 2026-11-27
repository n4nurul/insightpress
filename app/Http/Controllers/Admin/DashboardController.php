<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_news' => Post::where('type', 'news')->count(),
            'total_views' => Post::where('type', 'news')->sum('views_count'),
            'total_categories' => Category::count(),
            'total_team' => Post::where('type', 'team')->count(),
        ];

        $recentPosts = Post::with('category')
            ->whereIn('type', ['news', 'service'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPosts'));
    }
}
