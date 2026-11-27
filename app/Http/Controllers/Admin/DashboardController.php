<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_news' => Post::whereIn('type', ['news', 'service'])
                ->when($user->isReporter(), fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'total_views' => Post::whereIn('type', ['news', 'service'])
                ->when($user->isReporter(), fn($q) => $q->where('user_id', $user->id))
                ->sum('views_count'),
            'pending_drafts' => Post::where('type', 'news')->where('status', 'draft')
                ->when($user->isReporter(), fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'total_categories' => Category::count(),
        ];

        $pendingPosts = Post::with(['category', 'author'])
            ->where('type', 'news')
            ->where('status', 'draft')
            ->when($user->isReporter(), fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(8)
            ->get();

        $recentPosts = Post::with(['category', 'author'])
            ->whereIn('type', ['news', 'service'])
            ->where('status', 'published')
            ->when($user->isReporter(), fn($q) => $q->where('user_id', $user->id))
            ->latest('published_at')
            ->take(10)
            ->get();

        $quickBreaking = cms_setting('quick_breaking_text', '');
        $quickBreakingUrl = cms_setting('quick_breaking_url', '');

        return view('admin.dashboard', compact('stats', 'pendingPosts', 'recentPosts', 'quickBreaking', 'quickBreakingUrl'));
    }

    public function approvePost(Post $post)
    {
        if (!Auth::user()->isEditor()) {
            abort(403);
        }

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Post approved and published live!');
    }

    public function saveQuickBreaking(Request $request)
    {
        if (!Auth::user()->isEditor()) {
            abort(403);
        }

        $settingPost = Post::firstOrCreate(
            ['type' => 'setting', 'slug' => 'global-settings'],
            ['title' => 'Global Settings', 'status' => 'published', 'meta_data' => []]
        );

        $meta = $settingPost->meta_data ?? [];
        $meta['quick_breaking_text'] = $request->input('quick_breaking_text');
        $meta['quick_breaking_url'] = $request->input('quick_breaking_url');

        $settingPost->update(['meta_data' => $meta]);
        Cache::forget('cms_global_settings');

        return back()->with('success', 'Quick Breaking Ticker updated.');
    }

    public function clearCache()
    {
        Artisan::call('optimize:clear');
        Cache::forget('cms_global_settings');

        return back()->with('success', 'System & Website Cache cleared successfully!');
    }
}
