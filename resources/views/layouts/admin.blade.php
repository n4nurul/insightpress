<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — InsightPress CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Hind Siliguri', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-800" x-data="{ sidebarOpen: false }">
    @php $mode = cms_setting('site_mode', 'news'); @endphp
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-0 flex flex-col">
            <div class="h-16 flex items-center justify-between px-5 bg-slate-950 border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-white tracking-wide">
                    Insight<span class="text-emerald-400">Press</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">✕</button>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-sm">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                    <span>📊</span> ড্যাশবোর্ড
                </a>

                @if($mode === 'news')
                    <div class="pt-3 pb-1 px-3 text-xs font-semibold uppercase text-slate-500">নিউজ পোর্টাল</div>
                    <a href="{{ route('admin.posts.index', ['type' => 'news']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'news' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>📰</span> সকল সংবাদ
                    </a>
                    <a href="{{ route('admin.posts.create', ['type' => 'news']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800">
                        <span>✍️</span> নতুন সংবাদ লিখুন
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>🗂️</span> ক্যাটাগরি ও উপজেলা
                    </a>
                    <a href="{{ route('admin.posts.index', ['type' => 'team']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'team' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>👥</span> সম্পাদকীয় ও প্রতিনিধি
                    </a>
                    <a href="{{ route('admin.posts.index', ['type' => 'ad']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'ad' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>📢</span> বিজ্ঞাপন স্লট (Ads)
                    </a>
                @else
                    <div class="pt-3 pb-1 px-3 text-xs font-semibold uppercase text-slate-500">বিজনেস ওয়েবসাইট</div>
                    <a href="{{ route('admin.posts.index', ['type' => 'service']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'service' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>💼</span> সার্ভিস ও প্রোজেক্ট
                    </a>
                    <a href="{{ route('admin.posts.index', ['type' => 'news']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'news' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>📝</span> ব্লগ ও আপডেট
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>🗂️</span> সার্ভিস ক্যাটাগরি
                    </a>
                    <a href="{{ route('admin.posts.index', ['type' => 'team']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'team' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                        <span>👥</span> টিম মেম্বার
                    </a>
                @endif

                <div class="pt-3 pb-1 px-3 text-xs font-semibold uppercase text-slate-500">সিস্টেম</div>
                <a href="{{ route('admin.posts.index', ['type' => 'page']) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request('type') === 'page' ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                    <span>📄</span> পেজসমূহ (About/Contact)
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-600 text-white font-semibold' : 'hover:bg-slate-800' }}">
                    <span>⚙️</span> থিম ও গ্লোবাল সেটিংস
                </a>
            </nav>

            <div class="p-4 border-t border-slate-800">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 bg-rose-600/20 text-rose-400 hover:bg-rose-600 hover:text-white rounded-lg text-sm font-medium transition">
                        লগআউট করুন
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-200 px-4 lg:px-8 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-slate-100 text-slate-700">☰</button>
                    <h1 class="text-lg font-bold text-slate-800">@yield('title', 'ড্যাশবোর্ড')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold uppercase">
                        Mode: {{ $mode }}
                    </span>
                    <a href="/" target="_blank" class="text-sm px-3 py-1.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700">
                        সাইট দেখুন ↗
                    </a>
                </div>
            </header>

            <main class="p-4 lg:p-8 flex-1">
                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
                        ✅ {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
