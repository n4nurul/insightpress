<?php

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

if (!function_exists('cms_setting')) {
    function cms_setting(string $key, $default = null)
    {
        $settings = Cache::rememberForever('cms_global_settings', function () {
            $record = Post::where('type', 'setting')->where('slug', 'global-settings')->first();
            return $record?->meta_data ?? [];
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('theme_view')) {
    function theme_view(string $view, array $data = [])
    {
        $theme = cms_setting('active_theme', 'news-classic');
        $path = "themes.{$theme}.{$view}";

        if (!view()->exists($path)) {
            $path = "themes.news-classic.{$view}";
        }

        return view($path, $data);
    }
}

if (!function_exists('bn_slug')) {
    function bn_slug(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/[^\p{L}\p{N}\s\-]+/u', '', $text);
        $text = preg_replace('/[\s\-]+/u', '-', $text);
        return mb_strtolower(trim($text, '-'), 'UTF-8');
    }
}

if (!function_exists('bn_date')) {
    function bn_date($date): string
    {
        if (!$date) return '';
        $formatted = \Carbon\Carbon::parse($date)->format('d F Y');
        
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        
        return str_replace($en, $bn, $formatted);
    }
}
