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

if (!function_exists('bn_datetime')) {
    function bn_datetime($date): string
    {
        if (!$date) return '';
        $formatted = \Carbon\Carbon::parse($date)->format('d F Y, h:i A');
        
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December','AM','PM'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর','পূর্বাহ্ণ','অপরাহ্ণ'];
        
        return str_replace($en, $bn, $formatted);
    }
}

if (!function_exists('cms_addon')) {
    function cms_addon(string $key, bool $default = false): bool
    {
        $addons = cms_setting('enabled_addons', []);
        return isset($addons[$key]) ? (bool) $addons[$key] : $default;
    }
}

if (!function_exists('bn_number')) {
    function bn_number($number): string
    {
        return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], (string) $number);
    }
}


if (!function_exists('available_themes')) {
    function available_themes(): array
    {
        return [
            'sylhet-view' => 'Sylhet View (Classic Red & Navy)',
            'sylhet-modern' => 'Sylhet Modern (Emerald Portal)',
            'news-classic' => 'News Classic (Standard News)',
            'corporate-clean' => 'Corporate Clean (Business & Agency)',
        ];
    }
}

if (!function_exists('cms_theme_menu')) {
    function cms_theme_menu(string $location = 'header', ?string $theme = null)
    {
        $theme = $theme ?: cms_setting('active_theme', 'news-classic');
        $cacheKey = "theme_menu_{$theme}_{$location}";

        return Cache::rememberForever($cacheKey, function () use ($theme, $location) {
            return \App\Models\ThemeMenu::with('children')
                ->where('theme', $theme)
                ->where('location', $location)
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }
}