<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', cms_setting('site_name', 'সিলেট টুডে ২৪'))</title>
    <meta name="description" content="@yield('meta_description', cms_setting('default_meta_desc', cms_setting('site_tagline')))">
    @if(cms_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . cms_setting('favicon')) }}">
    @endif
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="robots" content="index, follow, max-image-preview:large">
    @stack('meta')

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --brand: {{ cms_setting('primary_color', '#007a5e') }}; }
        body { font-family: 'Hind Siliguri', sans-serif; background-color: #f4f6f8; color: #111827; }
        .bg-brand { background-color: var(--brand); }
        .text-brand { color: var(--brand); }
        .border-brand { border-color: var(--brand); }
        .hover\:text-brand:hover { color: var(--brand); }

        @keyframes tickerSlide {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .ticker-run { display: inline-flex; white-space: nowrap; animation: tickerSlide 34s linear infinite; }
        .ticker-run:hover { animation-play-state: paused; }
    </style>

    @if(cms_setting('adsense_client'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ cms_setting('adsense_client') }}" crossorigin="anonymous"></script>
    @endif
    {!! cms_setting('custom_header_code') !!}

    

    
    <style id="prothomalo-typography">
        /* Scale up root rem so Noto Serif Bengali matches Hind Siliguri visual size */
        html {
            font-size: 17px;
        }
        @media (min-width: 1024px) {
            html {
                font-size: 17.5px;
            }
        }
        body, nav, button, input, select {
            font-family: 'Hind Siliguri', sans-serif;
        }
        h1, h2, h3, h4, .article-content, .article-content p {
            font-family: 'Noto Serif Bengali', 'Hind Siliguri', serif;
            letter-spacing: -0.01em;
        }
        h1, h2, h3, h4 {
            line-height: 1.42 !important;
        }
        /* Single article body inherits alpine fontSize (A- / A+ compatible) */
        .article-content {
            font-size: 19px;
            line-height: 1.8;
            color: #1e293b;
        }
        .article-content p {
            font-size: inherit;
            line-height: 1.8;
            margin-bottom: 1.25rem;
        }
        @media (min-width: 1024px) {
            .article-content {
                font-size: 20px;
            }
        }
    </style>
</head>
@php
    $themeHeaderMenu = cms_theme_menu('header', 'sylhet-modern');
    $themeFooterMenu = cms_theme_menu('footer', 'sylhet-modern');
@endphp
<body class="min-h-screen flex flex-col antialiased" x-data="{ mobileOpen: false, searchBar: false }">
    @php
        $siteName = cms_setting('site_name', 'সিলেট টুডে ২৪');
        $tagline = cms_setting('site_tagline', 'সিলেটের সর্বশেষ সংবাদ ও বিশ্লেষণ');
        $quickBreaking = cms_addon('quick_breaking', false) ? cms_setting('quick_breaking_text', '') : '';
        $quickBreakingUrl = cms_setting('quick_breaking_url', '');
        $tickerPosts = $breakingNews ?? \App\Models\Post::published()->where('type', 'news')->where('is_breaking', true)->latest('published_at')->take(8)->get();
    @endphp

    <!-- Top Date & Quick Links Strip -->
    <div class="bg-slate-50 border-b border-slate-200 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
            <div class="flex items-center gap-3 font-medium">
                <span>📅 {{ bn_date(now()) }}</span>
                <span class="hidden md:inline text-slate-300">|</span>
                <span class="hidden md:inline text-slate-500">{{ $tagline }}</span>
            </div>
            <div class="flex items-center gap-3">
                @if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="hover:text-brand font-medium">প্রতিনিধি তালিকা</a>@endif
                @auth
                    <span class="text-slate-300">|</span>
                    <a href="{{ route('admin.dashboard') }}" class="text-brand font-bold hover:underline">অ্যাডমিন প্যানেল ↗</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Clean White Logo & Leaderboard Header -->
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if(cms_setting('logo'))
                    <img src="{{ asset('storage/' . cms_setting('logo')) }}" alt="{{ $siteName }}" class="h-14 sm:h-16 object-contain">
                @else
                    <div class="flex items-center gap-2.5">
                        <span class="w-11 h-11 rounded-lg bg-brand text-white flex items-center justify-center font-bold text-2xl">
                            {{ mb_substr($siteName, 0, 1) }}
                        </span>
                        <div>
                            <div class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight leading-none">{{ $siteName }}</div>
                            <div class="text-xs text-brand font-semibold mt-1">{{ $tagline }}</div>
                        </div>
                    </div>
                @endif
            </a>

            @if(cms_addon('ad_slots', true) && !empty($ads['header-banner']))
                <div class="max-w-full overflow-hidden">
                    {!! $ads['header-banner']['body'] ?? '' !!}
                </div>
            @endif
        </div>
    </div>

    <!-- Editorial White Sticky Navigation Bar with Bottom Border -->
    <header class="sticky top-0 z-40 bg-white border-y border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between">
            <nav class="hidden lg:flex items-center gap-1 text-[15px] font-bold text-slate-800">
@if($themeHeaderMenu->count() > 0)
    @foreach($themeHeaderMenu as $mItem)
        @if($mItem->children->count() > 0)
            <div class="relative" x-data="{ sub: false }" @mouseenter="sub = true" @mouseleave="sub = false">
                <a href="{{ url($mItem->url) }}" {{ $mItem->open_new_tab ? 'target="_blank"' : '' }} class="px-3.5 py-3 border-b-2 border-transparent hover:text-brand transition flex items-center gap-1">
                    <span>{{ $mItem->label }}</span>
                    <span class="text-[10px] text-slate-400">▼</span>
                </a>
                <div x-show="sub" style="display: none;" class="absolute left-0 top-full w-44 bg-white rounded-b-xl shadow-lg border border-slate-200 py-1.5 z-50">
                    @foreach($mItem->children as $mChild)
                        <a href="{{ url($mChild->url) }}" {{ $mChild->open_new_tab ? 'target="_blank"' : '' }} class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand">
                            {{ $mChild->label }}
                        </a>
                    @endforeach
                </div>
            </div>
        @else
            <a href="{{ url($mItem->url) }}" {{ $mItem->open_new_tab ? 'target="_blank"' : '' }} class="px-3.5 py-3 border-b-2 border-transparent hover:text-brand transition whitespace-nowrap">
                {{ $mItem->label }}
            </a>
        @endif
    @endforeach
@else
                <a href="{{ route('home') }}" class="px-3.5 py-3 border-b-2 transition {{ request()->routeIs('home') ? 'border-brand text-brand' : 'border-transparent hover:text-brand' }}">
                    প্রচ্ছদ
                </a>
                @foreach($menuCategories ?? [] as $cat)
                    @if($cat->children && $cat->children->count() > 0)
                        <div class="relative" x-data="{ sub: false }" @mouseenter="sub = true" @mouseleave="sub = false">
                            <a href="{{ route('category.show', $cat->slug) }}" class="px-3.5 py-3 border-b-2 border-transparent hover:text-brand transition flex items-center gap-1">
                                <span>{{ $cat->name }}</span>
                                <span class="text-[10px] text-slate-400">▼</span>
                            </a>
                            <div x-show="sub" style="display: none;" class="absolute left-0 top-full w-44 bg-white rounded-b-xl shadow-lg border border-slate-200 py-1.5 z-50">
                                @foreach($cat->children as $child)
                                    <a href="{{ route('category.show', $child->slug) }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('category.show', $cat->slug) }}" class="px-3.5 py-3 border-b-2 border-transparent hover:border-brand hover:text-brand transition whitespace-nowrap">
                            {{ $cat->name }}
                        </a>
                    @endif
                @endforeach
            </nav>
@endif

            <!-- Mobile Drawer Toggle & Search Icon -->
            <div class="flex items-center justify-between w-full lg:w-auto gap-2">
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden px-3 py-1.5 rounded-lg border border-slate-200 text-slate-800 text-sm font-bold">
                    <span x-show="!mobileOpen">☰ বিভাগসমূহ</span>
                    <span x-show="mobileOpen">✕ বন্ধ করুন</span>
                </button>

                <button @click="searchBar = !searchBar" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition">
                    <span>🔍</span>
                    <span>অনুসন্ধান</span>
                </button>
            </div>
        </div>

        <!-- Expandable Search -->
        <div x-show="searchBar" x-transition style="display: none;" class="bg-slate-50 border-t border-slate-200 py-3 px-4">
            <form action="{{ route('home') }}" method="GET" class="max-w-2xl mx-auto flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="সংবাদ খুঁজুন..." class="flex-1 px-4 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none">
                <button type="submit" class="px-5 py-2 bg-brand text-white font-bold rounded-lg text-sm">খুঁজুন</button>
            </form>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" x-transition style="display: none;" class="lg:hidden bg-white border-t border-slate-200 px-4 py-3 space-y-1 text-sm font-bold max-h-[75vh] overflow-y-auto">
@if($themeHeaderMenu->count() > 0)
    @foreach($themeHeaderMenu as $mItem)
        <a href="{{ url($mItem->url) }}" class="block py-2 border-t border-slate-100 text-slate-800 hover:text-brand">{{ $mItem->label }}</a>
        @foreach($mItem->children as $mChild)
            <a href="{{ url($mChild->url) }}" class="block py-1.5 pl-5 text-xs text-slate-600">• {{ $mChild->label }}</a>
        @endforeach
    @endforeach
@else
            <a href="{{ route('home') }}" class="block py-2 text-brand">প্রচ্ছদ</a>
            @foreach($menuCategories ?? [] as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="block py-2 border-t border-slate-100 text-slate-800 hover:text-brand">
                    {{ $cat->name }}
                </a>
                @foreach($cat->children ?? [] as $child)
                    <a href="{{ route('category.show', $child->slug) }}" class="block py-1.5 pl-5 text-xs text-slate-600">
                        • {{ $child->name }}
                    </a>
                @endforeach
            @endforeach
@endif
        </div>
    </header>

    <!-- Breaking News Ticker -->
    @if($quickBreaking || $tickerPosts->count() > 0)
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center overflow-hidden">
            <span class="bg-rose-600 text-white text-xs font-bold px-3 py-1 rounded mr-3 shrink-0">
                শিরোনাম
            </span>
            <div class="overflow-hidden flex-1 text-sm font-semibold text-slate-800">
                <div class="ticker-run">
                    @for($i = 0; $i < 2; $i++)
                        @if($quickBreaking)
                            <a href="{{ $quickBreakingUrl ?: '#' }}" class="mx-6 text-rose-600 hover:underline">⚡ {{ $quickBreaking }}</a>
                        @endif
                        @foreach($tickerPosts as $bPost)
                            <a href="{{ route('post.show', $bPost->slug) }}" class="mx-6 hover:text-brand transition">▪ {{ $bPost->title }}</a>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>
    </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Clean Editorial Footer -->
    <footer class="bg-white border-t-2 border-brand mt-14 text-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="text-2xl font-bold text-slate-900 mb-2">{{ $siteName }}</div>
                <p class="text-xs text-slate-500 leading-relaxed mb-3">{{ $tagline }}</p>
                @if(cms_setting('editor_name'))
                    <p class="text-sm font-bold text-slate-900">সম্পাদক: {{ cms_setting('editor_name') }}</p>
                @endif
            </div>

            <div>
                <h4 class="text-sm font-bold text-slate-900 mb-3 border-b pb-1.5">গুরুত্বপূর্ণ বিভাগ</h4>
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-600">
                    @if($themeFooterMenu->count() > 0)
    @foreach($themeFooterMenu as $fItem)
        <a href="{{ url($fItem->url) }}" {{ $fItem->open_new_tab ? 'target="_blank"' : '' }} class="hover:text-brand">{{ $fItem->label }}</a>
    @endforeach
@else
    @foreach(($menuCategories ?? collect())->take(8) as $cat)
                        <a href="{{ route('category.show', $cat->slug) }}" class="hover:text-brand">{{ $cat->name }}</a>
                    @endforeach
@endif
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold text-slate-900 mb-3 border-b pb-1.5">বার্তা ও বাণিজ্যিক কার্যালয়</h4>
                <ul class="space-y-2 text-xs text-slate-600">
                    <li>📍 {{ cms_setting('office_address', 'জিন্দাবাজার, সিলেট') }}</li>
                    <li>📞 {{ cms_setting('contact_phone', '+8801700000000') }}</li>
                    <li>@if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="text-brand font-bold hover:underline">আমাদের টিম ও সংবাদদাতাগণ</a>@endif</li>
                </ul>
            </div>
        </div>
        <div class="bg-slate-900 text-slate-400 py-3.5 text-center text-xs">
            &copy; {{ date('Y') }} {{ $siteName }} | সর্বস্বত্ব সংরক্ষিত।
        </div>
    </footer>

    {!! cms_setting('custom_footer_code') !!}
</body>
</html>
