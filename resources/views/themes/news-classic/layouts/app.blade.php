<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', cms_setting('site_name', 'InsightPress News'))</title>
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
        :root { --brand: {{ cms_setting('primary_color', '#dc2626') }}; }
        body { font-family: 'Hind Siliguri', sans-serif; background-color: #f8fafc; color: #0f172a; }
        .bg-brand { background-color: var(--brand); }
        .text-brand { color: var(--brand); }
        .border-brand { border-color: var(--brand); }
        .hover\:text-brand:hover { color: var(--brand); }

        @keyframes tickerScroll {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track { display: inline-flex; white-space: nowrap; animation: tickerScroll 32s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }
    </style>

    @if(cms_setting('adsense_client'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ cms_setting('adsense_client') }}" crossorigin="anonymous"></script>
    @endif
    {!! cms_setting('custom_header_code') !!}
</head>
<body class="min-h-screen flex flex-col antialiased" x-data="{ mobileMenu: false, searchOpen: false }">
    @php
        $siteName = cms_setting('site_name', 'সিলেট প্রতিদিন ২৪');
        $tagline = cms_setting('site_tagline', 'সত্য ও ন্যায়ের পথে অবিচল');
        $quickBreaking = cms_addon('quick_breaking', false) ? cms_setting('quick_breaking_text', '') : '';
        $quickBreakingUrl = cms_setting('quick_breaking_url', '');
        $tickerPosts = $breakingNews ?? \App\Models\Post::published()->where('type', 'news')->where('is_breaking', true)->latest('published_at')->take(8)->get();
    @endphp

    <!-- Top Utility Date & Social Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span>📅 {{ bn_date(now()) }}</span>
                <span class="hidden sm:inline text-slate-600">|</span>
                <span class="hidden sm:inline text-slate-400">{{ $tagline }}</span>
            </div>
            <div class="flex items-center gap-3">
                @if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="hover:text-white transition">আমাদের টিম</a>@endif
                @auth
                    <span class="text-slate-600">|</span>
                    <a href="{{ route('admin.dashboard') }}" class="text-emerald-400 hover:underline font-semibold">ড্যাশবোর্ড ↗</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Logo & Header Banner Section -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if(cms_setting('logo'))
                    <img src="{{ asset('storage/' . cms_setting('logo')) }}" alt="{{ $siteName }}" class="h-14 sm:h-16 object-contain">
                @else
                    <div class="w-12 h-12 rounded-xl bg-brand text-white flex items-center justify-center font-bold text-2xl shadow-sm">
                        {{ mb_substr($siteName, 0, 1) }}
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-bold text-slate-900 leading-none">{{ $siteName }}</div>
                        <div class="text-xs text-slate-500 mt-1">{{ $tagline }}</div>
                    </div>
                @endif
            </a>

            <!-- Header Ad Slot (if Ad Slots Addon Active) -->
            @if(cms_addon('ad_slots', true) && !empty($ads['header-banner']))
                <div class="max-w-full overflow-hidden">
                    {!! $ads['header-banner']['body'] ?? '' !!}
                </div>
            @endif
        </div>
    </div>

    <!-- Sticky Navigation Bar -->
    <header class="sticky top-0 z-40 bg-brand text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between">
            <!-- Desktop Menu -->
            <nav class="hidden lg:flex items-center gap-1 text-sm font-semibold overflow-x-auto">
                <a href="{{ route('home') }}" class="px-3.5 py-2.5 rounded hover:bg-black/15 transition {{ request()->routeIs('home') ? 'bg-black/20' : '' }}">
                    হোম
                </a>
                @foreach($menuCategories ?? [] as $cat)
                    @if($cat->children && $cat->children->count() > 0)
                        <div class="relative" x-data="{ drop: false }" @mouseenter="drop = true" @mouseleave="drop = false">
                            <a href="{{ route('category.show', $cat->slug) }}" class="px-3.5 py-2.5 rounded hover:bg-black/15 transition flex items-center gap-1">
                                <span>{{ $cat->name }}</span>
                                <span class="text-[10px]">▼</span>
                            </a>
                            <div x-show="drop" style="display: none;" class="absolute left-0 top-full w-44 bg-white text-slate-800 rounded-b-xl shadow-lg border border-slate-200 py-1.5 z-50">
                                @foreach($cat->children as $child)
                                    <a href="{{ route('category.show', $child->slug) }}" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 hover:text-brand">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('category.show', $cat->slug) }}" class="px-3.5 py-2.5 rounded hover:bg-black/15 transition whitespace-nowrap">
                            {{ $cat->name }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Mobile Menu Button & Search Toggle -->
            <div class="flex items-center justify-between w-full lg:w-auto gap-2">
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden flex items-center gap-2 px-3 py-1.5 rounded bg-black/15 text-sm font-semibold">
                    <span x-show="!mobileMenu">☰ মেনু</span>
                    <span x-show="mobileMenu">✕ বন্ধ করুন</span>
                </button>

                <button @click="searchOpen = !searchOpen" class="px-3 py-1.5 rounded bg-black/15 hover:bg-black/25 text-xs font-semibold flex items-center gap-1.5 transition">
                    <span>🔍</span>
                    <span class="hidden sm:inline">খুঁজুন</span>
                </button>
            </div>
        </div>

        <!-- Expandable Search Bar -->
        <div x-show="searchOpen" x-transition style="display: none;" class="bg-slate-900 border-t border-slate-800 py-3 px-4">
            <form action="{{ route('home') }}" method="GET" class="max-w-2xl mx-auto flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="সংবাদের শিরোনাম বা বিষয় লিখে খুঁজুন..." class="flex-1 px-4 py-2 rounded-lg text-slate-900 text-sm focus:outline-none">
                <button type="submit" class="px-5 py-2 bg-brand text-white font-bold rounded-lg text-sm">সার্চ</button>
            </form>
        </div>

        <!-- Mobile Slide-Down Drawer -->
        <div x-show="mobileMenu" x-transition style="display: none;" class="lg:hidden bg-white text-slate-800 border-b border-slate-200 px-4 py-3 space-y-1 text-sm font-semibold max-h-[75vh] overflow-y-auto">
            <a href="{{ route('home') }}" class="block py-2 px-2 rounded hover:bg-slate-100 text-brand">হোম পেজ</a>
            @foreach($menuCategories ?? [] as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="block py-2 px-2 rounded hover:bg-slate-100 border-t border-slate-100">
                    {{ $cat->name }}
                </a>
                @foreach($cat->children ?? [] as $child)
                    <a href="{{ route('category.show', $child->slug) }}" class="block py-1.5 pl-6 text-xs text-slate-600 hover:text-brand">
                        ↳ {{ $child->name }}
                    </a>
                @endforeach
            @endforeach
            @if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="block py-2 px-2 rounded hover:bg-slate-100 border-t border-slate-100">আমাদের টিম</a>@endif
        </div>
    </header>

    <!-- Breaking News Ticker Bar -->
    @if($quickBreaking || $tickerPosts->count() > 0)
    <div class="bg-white border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center overflow-hidden">
            <span class="bg-brand text-white text-xs font-bold px-3 py-1 rounded-sm shrink-0 mr-3 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                ব্রেকিং নিউজ
            </span>
            <div class="overflow-hidden flex-1 text-sm font-semibold text-slate-800">
                <div class="ticker-track">
                    @for($loopCopy = 0; $loopCopy < 2; $loopCopy++)
                        @if($quickBreaking)
                            <a href="{{ $quickBreakingUrl ?: '#' }}" class="mx-6 text-brand hover:underline">
                                ⚡ {{ $quickBreaking }}
                            </a>
                        @endif
                        @foreach($tickerPosts as $bPost)
                            <a href="{{ route('post.show', $bPost->slug) }}" class="mx-6 hover:text-brand transition">
                                • {{ $bPost->title }}
                            </a>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Page Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Editorial & Portal Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-14 border-t-4 border-brand">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold text-white mb-2">{{ $siteName }}</h3>
                <p class="text-xs text-slate-400 leading-relaxed mb-3">{{ $tagline }}</p>
                @if(cms_setting('editor_name'))
                    <p class="text-sm font-semibold text-white">সম্পাদক ও প্রকাশক: {{ cms_setting('editor_name') }}</p>
                @endif
            </div>

            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-3">বিভাগসমূহ</h4>
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-400">
                    @foreach(($menuCategories ?? collect())->take(8) as $cat)
                        <a href="{{ route('category.show', $cat->slug) }}" class="hover:text-white transition">▸ {{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-3">যোগাযোগ ও ঠিকানা</h4>
                <ul class="space-y-2 text-xs text-slate-400">
                    <li>📍 {{ cms_setting('office_address', 'সিলেট, বাংলাদেশ') }}</li>
                    <li>📞 {{ cms_setting('contact_phone', '+8801700000000') }}</li>
                    <li>@if(cms_addon('team_page', true))<a href="{{ route('team.index') }}" class="text-slate-300 hover:text-white underline">আমাদের প্রতিনিধি ও টিম তালিকা</a>@endif</li>
                </ul>
            </div>
        </div>

        <div class="bg-slate-950 py-3.5 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ $siteName }} — সর্বস্বত্ব সংরক্ষিত।
        </div>
    </footer>

    {!! cms_setting('custom_footer_code') !!}
</body>
</html>
