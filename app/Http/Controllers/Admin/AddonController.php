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
                'description' => 'Allows logged-in authors, editors, and admins to generate & download 1080x1080 branded Facebook news photocards from the backend post menu.',
                'category' => 'Social Media',
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
