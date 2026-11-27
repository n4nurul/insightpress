<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', cms_setting('site_name', 'সিলেটভিউ২৪'))</title>
    <meta name="description" content="@yield('meta_description', cms_setting('default_meta_desc', cms_setting('site_tagline')))">
    @if(cms_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . cms_setting('favicon')) }}">
    @endif
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta name="robots" content="index, follow, max-image-preview:large">
    @stack('meta')

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sv-red: {{ cms_setting('primary_color', '#c8102e') }};
            --sv-navy: #0b2239;
        }
        body { font-family: 'Hind Siliguri', sans-serif; background-color: #eef1f5; color: #111827; }
        .bg-sv-red { background-color: var(--sv-red); }
        .text-sv-red { color: var(--sv-red); }
        .border-sv-red { border-color: var(--sv-red); }
        .hover\:text-sv-red:hover { color: var(--sv-red); }
        .bg-sv-navy { background-color: var(--sv-navy); }

        @keyframes svMarquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .sv-marquee { display: inline-flex; white-space: nowrap; animation: svMarquee 30s linear infinite; }
        .sv-marquee:hover { animation-play-state: paused; }
    </style>

    @if(cms_setting('adsense_client'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ cms_setting('adsense_client') }}" crossorigin="anonymous"></script>
    @endif
    {!! cms_setting('custom_header_code') !!}
</head>
<body class="min-h-screen flex flex-col antialiased" x-data="{ mobNav: false, searchBox: false }">
    @php
        $siteName = cms_setting('site_name', 'সিলেটভিউ২৪');
        $tagline = cms_setting('site_tagline', 'সত্য প্রকাশে আপসহীন');
        $quickBreaking = cms_addon('quick_breaking', false) ? cms_setting('quick_breaking_text', '') : '';
        $quickBreakingUrl = cms_setting('quick_breaking_url', '');
        $tickerPosts = $breakingNews ?? \App\Models\Post::published()->where('type', 'news')->where('is_breaking', true)->latest('published_at')->take(8)->get();
    @endphp

    <!-- Top Dark Navy Utility Strip -->
    <div class="bg-sv-navy text-slate-200 text-xs border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="font-medium">📅 {{ bn_date(now()) }}</span>
                <span class="hidden sm:inline text-slate-500">|</span>
                <span class="hidden sm:inline text-slate-300">সিলেট, বাংলাদেশ</span>
            </div>
            <div class="flex items-center gap-3">
                @if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="hover:text-amber-300 transition">প্রতিনিধি পরিষদ</a>@endif
                @auth
                    <span class="text-slate-500">|</span>
                    <a href="{{ route('admin.dashboard') }}" class="px-2 py-0.5 bg-sv-red text-white rounded text-[11px] font-bold">ড্যাশবোর্ড</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Logo & Leaderboard Ad Header -->
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if(cms_setting('logo'))
                    <img src="{{ asset('storage/' . cms_setting('logo')) }}" alt="{{ $siteName }}" class="h-14 sm:h-16 object-contain">
                @else
                    <div class="flex items-center gap-2.5">
                        <div class="w-12 h-12 bg-sv-red text-white font-bold text-2xl flex items-center justify-center rounded-sm shadow-xs">
                            {{ mb_substr($siteName, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-3xl font-bold text-slate-900 tracking-tight leading-none">{{ $siteName }}</div>
                            <div class="text-xs font-semibold text-sv-red mt-1">{{ $tagline }}</div>
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

    <!-- Bold Red Navigation Bar with Dark Bottom Border -->
    <header class="sticky top-0 z-40 bg-sv-red text-white shadow-md border-b-2 border-[#0b2239]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-11 flex items-center justify-between">
            <nav class="hidden lg:flex items-center text-[15px] font-bold h-full">
                <a href="{{ route('home') }}" class="h-full px-4 flex items-center hover:bg-sv-navy transition {{ request()->routeIs('home') ? 'bg-sv-navy' : '' }}">
                    প্রচ্ছদ
                </a>
                @foreach($menuCategories ?? [] as $cat)
                    @if($cat->children && $cat->children->count() > 0)
                        <div class="relative h-full" x-data="{ openSub: false }" @mouseenter="openSub = true" @mouseleave="openSub = false">
                            <a href="{{ route('category.show', $cat->slug) }}" class="h-full px-3.5 flex items-center gap-1 hover:bg-sv-navy transition">
                                <span>{{ $cat->name }}</span>
                                <span class="text-[10px]">▼</span>
                            </a>
                            <div x-show="openSub" style="display: none;" class="absolute left-0 top-full w-48 bg-white text-slate-900 shadow-xl border-t-2 border-sv-red py-1 z-50">
                                @foreach($cat->children as $child)
                                    <a href="{{ route('category.show', $child->slug) }}" class="block px-4 py-2 text-xs font-bold border-b border-slate-100 last:border-0 hover:bg-red-50 hover:text-sv-red">
                                        » {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('category.show', $cat->slug) }}" class="h-full px-3.5 flex items-center hover:bg-sv-navy transition whitespace-nowrap">
                            {{ $cat->name }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="flex items-center justify-between w-full lg:w-auto gap-2">
                <button @click="mobNav = !mobNav" class="lg:hidden px-3 py-1 bg-sv-navy text-white text-xs font-bold rounded-sm">
                    <span x-show="!mobNav">☰ সব বিভাগ</span>
                    <span x-show="mobNav">✕ বন্ধ করুন</span>
                </button>
                <button @click="searchBox = !searchBox" class="px-3 py-1 bg-black/20 hover:bg-sv-navy text-white text-xs font-bold rounded-sm transition">
                    🔍 অনুসন্ধান
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div x-show="searchBox" x-transition style="display: none;" class="bg-sv-navy py-2.5 px-4 border-t border-white/15">
            <form action="{{ route('home') }}" method="GET" class="max-w-xl mx-auto flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="সংবাদ খুঁজুন..." class="flex-1 px-3.5 py-1.5 rounded-sm text-slate-900 text-sm focus:outline-none">
                <button type="submit" class="px-4 py-1.5 bg-sv-red text-white font-bold rounded-sm text-sm">খুঁজুন</button>
            </form>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobNav" x-transition style="display: none;" class="lg:hidden bg-white text-slate-900 border-b border-slate-300 px-4 py-3 space-y-1 text-sm font-bold max-h-[75vh] overflow-y-auto">
            <a href="{{ route('home') }}" class="block py-2 text-sv-red">প্রচ্ছদ</a>
            @foreach($menuCategories ?? [] as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="block py-2 border-t border-slate-100">{{ $cat->name }}</a>
            @endforeach
        </div>
    </header>

    <!-- Breaking / শিরোনাম Ticker -->
    @if($quickBreaking || $tickerPosts->count() > 0)
    <div class="bg-white border-b border-slate-300 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center overflow-hidden">
            <span class="bg-sv-navy text-white text-xs font-bold px-3 py-1.5 mr-3 shrink-0 flex items-center gap-1.5">
                <span class="w-2 h-2 bg-sv-red inline-block animate-ping"></span>
                এইমাত্র পাওয়া
            </span>
            <div class="overflow-hidden flex-1 text-sm font-bold text-slate-900">
                <div class="sv-marquee">
                    @for($i = 0; $i < 2; $i++)
                        @if($quickBreaking)
                            <a href="{{ $quickBreakingUrl ?: '#' }}" class="mx-6 text-sv-red hover:underline">⚡ {{ $quickBreaking }}</a>
                        @endif
                        @foreach($tickerPosts as $bPost)
                            <a href="{{ route('post.show', $bPost->slug) }}" class="mx-6 hover:text-sv-red">■ {{ $bPost->title }}</a>
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

    <!-- Dark Navy Editorial Footer -->
    <footer class="bg-sv-navy text-slate-300 mt-12 border-t-4 border-sv-red">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-9 grid grid-cols-1 md:grid-cols-3 gap-8 text-xs">
            <div>
                <div class="text-2xl font-bold text-white mb-2">{{ $siteName }}</div>
                <p class="text-slate-400 leading-relaxed mb-3">{{ $tagline }}</p>
                @if(cms_setting('editor_name'))
                    <p class="text-white font-bold text-sm">সম্পাদক ও প্রকাশক: {{ cms_setting('editor_name') }}</p>
                @endif
            </div>
            <div>
                <div class="font-bold text-white text-sm mb-3 border-b border-white/15 pb-1.5">বিভাগসমূহ</div>
                <div class="grid grid-cols-2 gap-2 text-slate-300">
                    @foreach(($menuCategories ?? collect())->take(8) as $cat)
                        <a href="{{ route('category.show', $cat->slug) }}" class="hover:text-amber-300">» {{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="font-bold text-white text-sm mb-3 border-b border-white/15 pb-1.5">বার্তা ও বাণিজ্যিক কার্যালয়</div>
                <p class="text-slate-300 mb-1.5">📍 {{ cms_setting('office_address', 'জিন্দাবাজার, সিলেট') }}</p>
                <p class="text-slate-300 mb-2.5">📞 {{ cms_setting('contact_phone', '+8801700000000') }}</p>
                @if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="inline-block px-3 py-1 bg-sv-red text-white font-bold rounded-sm">আমাদের প্রতিনিধি তালিকা</a>@endif
            </div>
        </div>
        <div class="bg-[#061423] py-3 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ $siteName }} — সর্বস্বত্ব সংরক্ষিত।
        </div>
    </footer>

    {!! cms_setting('custom_footer_code') !!}
</body>
</html>
