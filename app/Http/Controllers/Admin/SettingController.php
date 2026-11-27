<?php

namespace App\Http\Controllers\Admin;

use App\Core\Services\MediaService;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected MediaService $media) {}

    public function index()
    {
        $settingPost = Post::firstOrCreate(
            ['type' => 'setting', 'slug' => 'global-settings'],
            ['title' => 'Global Settings', 'status' => 'published', 'meta_data' => []]
        );

        $settings = $settingPost->meta_data ?? [];
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settingPost = Post::firstOrCreate(
            ['type' => 'setting', 'slug' => 'global-settings'],
            ['title' => 'Global Settings', 'status' => 'published', 'meta_data' => []]
        );

        $meta = $settingPost->meta_data ?? [];
        $inputs = $request->except(['_token', 'logo', 'favicon']);

        if ($request->hasFile('logo')) {
            $this->media->delete($meta['logo'] ?? null);
            $inputs['logo'] = $this->media->uploadImage($request->file('logo'), 'branding', 600);
        }

        if ($request->hasFile('favicon')) {
            $this->media->delete($meta['favicon'] ?? null);
            $inputs['favicon'] = $this->media->uploadImage($request->file('favicon'), 'branding', 128);
        }

        $settingPost->update([
            'meta_data' => array_merge($meta, $inputs),
        ]);

        return back()->with('success', 'সেটিংস সফলভাবে আপডেট হয়েছে।');
    }
}
