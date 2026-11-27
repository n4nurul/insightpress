<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AddonController extends Controller
{
    public static function registry(): array
    {
        return [
            'quick_breaking' => [
                'name' => 'Quick Breaking News Ticker',
                'description' => 'Adds an instant urgent breaking news scroll bar widget directly on the admin dashboard and frontend ticker.',
                'category' => 'Newsroom',
                'default' => false,
            ],
            'ad_slots' => [
                'name' => 'Ad Slots & AdSense Manager',
                'description' => 'Enables banner ad placements (Header, Sidebar, Inside News) and Google AdSense slots across the portal.',
                'category' => 'Monetization',
                'default' => true,
            ],
            'photocard' => [
                'name' => 'Social Photocard Generator',
                'description' => 'Allows logged-in authors, editors, and admins to generate & download 1080x1080 branded Facebook news photocards.',
                'category' => 'Social Media',
                'default' => true,
            ],
            'google_news_sitemap' => [
                'name' => 'Google News Sitemap (48h XML)',
                'description' => 'Generates a dedicated Google News Publisher Center XML feed (/news-sitemap.xml) for articles published in the last 48 hours.',
                'category' => 'Premium SEO',
                'default' => false,
            ],
            'schema_seo' => [
                'name' => 'NewsArticle JSON-LD Schema',
                'description' => 'Injects rich NewsArticle structured data markup for Google Discover and Top Stories eligibility.',
                'category' => 'Premium SEO',
                'default' => true,
            ],
            'team_page' => [
                'name' => 'Reporters & Editorial Team Directory',
                'description' => 'Enables the public /team directory page showcasing editors, staff reporters, and district correspondents.',
                'category' => 'Newsroom',
                'default' => true,
            ],
            'rss_importer' => [
                'name' => 'RSS / Demo News Importer',
                'description' => 'Import demo or syndicated news articles with images directly from any external portal RSS feed URL in one click.',
                'category' => 'Tools & Demo',
                'default' => true,
            ],
        ];
    }

    public function index()
    {
        $addons = self::registry();
        return view('admin.addons.index', compact('addons'));
    }

    public function toggle(Request $request, string $key)
    {
        $registry = self::registry();
        if (!array_key_exists($key, $registry)) {
            abort(404);
        }

        $settingPost = Post::firstOrCreate(
            ['type' => 'setting', 'slug' => 'global-settings'],
            ['title' => 'Global Settings', 'status' => 'published', 'meta_data' => []]
        );

        $meta = $settingPost->meta_data ?? [];
        $enabled = $meta['enabled_addons'] ?? [];

        $current = isset($enabled[$key]) ? (bool) $enabled[$key] : $registry[$key]['default'];
        $enabled[$key] = !$current;

        $meta['enabled_addons'] = $enabled;
        $settingPost->update(['meta_data' => $meta]);
        Cache::forget('cms_global_settings');

        $statusText = $enabled[$key] ? 'activated' : 'deactivated';
        return back()->with('success', $registry[$key]['name'] . " has been {$statusText}.");
    }
}
