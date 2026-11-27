<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — InsightPress CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #334155 transparent; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800" x-data="{ sidebarOpen: false }">
    @php
        $mode = cms_setting('site_mode', 'news');
        $user = auth()->user();
    @endphp
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-60 bg-slate-900 text-slate-300 transition-transform duration-200 lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen shrink-0 flex flex-col border-r border-slate-800">
            <div class="h-14 flex items-center justify-between px-5 bg-slate-950 border-b border-slate-800 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-white tracking-wide">
                    Insight<span class="text-emerald-400">Press</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">✕</button>
            </div>

            <nav class="flex-1 px-2.5 py-3 space-y-0.5 overflow-y-auto sidebar-scroll text-[13px]">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                    <span>📊</span> Dashboard
                </a>

                @if($mode === 'news')
                    <a href="{{ route('admin.posts.index', ['type' => 'news']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'news' && request()->routeIs('admin.posts.index') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                        <span>📰</span> {{ $user->isReporter() ? 'My News' : 'All News' }}
                    </a>
                    <a href="{{ route('admin.posts.create', ['type' => 'news']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'news' && request()->routeIs('admin.posts.create') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                        <span>✍️</span> Write News
                    </a>
                    @if($user->isEditor())
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                            <span>🗂️</span> Categories & Areas
                        </a>
                        <a href="{{ route('admin.posts.index', ['type' => 'team']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'team' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                            <span>👥</span> Editorial & Reporters
                        </a>
                        @if(cms_addon('ad_slots', true))
                        <a href="{{ route('admin.posts.index', ['type' => 'ad']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'ad' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                            <span>📢</span> Ad Slots
                        </a>
                        @endif
                    @endif
                @else
                    <a href="{{ route('admin.posts.index', ['type' => 'service']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'service' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                        <span>💼</span> Services & Projects
                    </a>
                    <a href="{{ route('admin.posts.index', ['type' => 'news']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'news' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                        <span>📝</span> Blog & Updates
                    </a>
                    @if($user->isEditor())
                        <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.categories.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                            <span>🗂️</span> Categories
                        </a>
                        <a href="{{ route('admin.posts.index', ['type' => 'team']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'team' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                            <span>👥</span> Team Members
                        </a>
                    @endif
                @endif

                <a href="{{ route('admin.media.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.media.*') ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                    <span>🖼️</span> Media Library
                </a>

                @if($user->isAdmin())
                    <a href="{{ route('admin.posts.index', ['type' => 'page']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request('type') === 'page' ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'hover:bg-slate-800/80 hover:text-white' }}">
                        <span>📄</span> Pages
                    </a>

                    <!-- Collapsible Settings Group -->
                    <div class="pt-1" x-data="{ openSettings: false }">
                        <button type="button" @click="openSettings = !openSettings"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition cursor-pointer hover:bg-slate-800/80 hover:text-white text-slate-300">
                            <span class="flex items-center gap-2.5">
                                <span>⚙️</span> Settings
                            </span>
                            <span class="text-[10px] text-slate-400 transition-transform duration-200" :class="openSettings ? 'rotate-180' : ''">▼</span>
                        </button>

                        <div x-show="openSettings" x-transition style="display: none;"
                             class="mt-1 ml-2.5 pl-2.5 border-l border-slate-700/80 space-y-0.5">
                            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs {{ request()->routeIs('admin.settings.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>🎨</span> Theme & General
                            </a>
                            <a href="{{ route('admin.menus.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs {{ request()->routeIs('admin.menus.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>🧭</span> Theme Menus
                            </a>
                            <a href="{{ route('admin.addons.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs {{ request()->routeIs('admin.addons.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>🧩</span> Addons
                            </a>
                            @if(cms_addon('rss_importer', true))
                            <a href="{{ route('admin.rss-importer.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs {{ request()->routeIs('admin.rss-importer.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>📡</span> RSS Importer
                            </a>
                            @endif
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-xs {{ request()->routeIs('admin.users.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>🔐</span> Users & Roles
                            </a>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="p-3 border-t border-slate-800 shrink-0">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 bg-rose-600/15 text-rose-400 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-semibold transition cursor-pointer">
                        Logout ({{ $user->name }})
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-14 bg-white border-b border-slate-200 px-4 lg:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-slate-100 text-slate-700">☰</button>
                    <h1 class="text-base font-bold text-slate-800">@yield('title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="text-[11px] px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 font-bold uppercase">
                        Role: {{ $user->role }}
                    </span>
                    <form action="{{ route('admin.clear-cache') }}" method="POST" class="inline">@csrf<button type="submit" class="text-xs px-2.5 py-1.5 bg-amber-100 text-amber-800 hover:bg-amber-200 rounded-lg font-semibold cursor-pointer">⚡ Clear Cache</button></form>
                    <a href="/" target="_blank" class="text-xs px-3 py-1.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700 font-semibold">
                        Visit Site ↗
                    </a>
                </div>
            </header>

            <main class="p-4 lg:p-6 flex-1">
                @if(session('success'))
                    <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
                        ✅ {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
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
