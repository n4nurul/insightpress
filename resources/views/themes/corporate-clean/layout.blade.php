<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', cms_setting('site_name', 'WebsInsight Solutions'))</title>
    <meta name="description" content="@yield('meta_description', cms_setting('default_meta_desc', cms_setting('site_tagline')))">
    @if(cms_setting('favicon'))
        <link rel="icon" href="{{ asset('storage/' . cms_setting('favicon')) }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --brand: {{ cms_setting('primary_color', '#059669') }}; }
        body { font-family: 'Hind Siliguri', 'Inter', sans-serif; }
        .bg-brand { background-color: var(--brand); }
        .text-brand { color: var(--brand); }
        .border-brand { border-color: var(--brand); }
    </style>
    @stack('head')
    {!! cms_setting('custom_header_code') !!}
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen" x-data="{ mobileMenu: false }">
    @php
        $siteName = cms_setting('site_name', 'WebsInsight Solutions');
        $phone = cms_setting('contact_phone', '+8801700000000');
        $waNumber = preg_replace('/[^0-9]/', '', $phone);
    @endphp

    <!-- Sticky Header -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                @if(cms_setting('logo'))
                    <img src="{{ asset('storage/' . cms_setting('logo')) }}" alt="{{ $siteName }}" class="h-10 object-contain">
                @else
                    <span class="w-9 h-9 rounded-lg bg-brand text-white flex items-center justify-center font-bold text-lg">
                        {{ mb_substr($siteName, 0, 1) }}
                    </span>
                    <span class="text-xl font-bold text-slate-900">{{ $siteName }}</span>
                @endif
            </a>

            <!-- Desktop Menu -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-semibold text-slate-700">
                <a href="{{ route('home') }}" class="hover:text-brand transition">হোম</a>
                @foreach($menuCategories ?? [] as $cat)
                    <a href="{{ route('category.show', $cat->slug) }}" class="hover:text-brand transition">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('team.index') }}" class="hover:text-brand transition">আমাদের টিম</a>
                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="px-4 py-2 rounded-lg bg-brand text-white hover:opacity-90 transition shadow-sm">
                    যোগাযোগ করুন
                </a>
            </nav>

            <!-- Mobile Button -->
            <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg bg-slate-100 text-slate-700">
                <span x-show="!mobileMenu">☰</span>
                <span x-show="mobileMenu">✕</span>
            </button>
        </div>

        <!-- Mobile Dropdown -->
        <div x-show="mobileMenu" x-transition class="md:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-2 text-sm font-semibold">
            <a href="{{ route('home') }}" class="block py-2 text-slate-700">হোম</a>
            @foreach($menuCategories ?? [] as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="block py-2 text-slate-700">{{ $cat->name }}</a>
            @endforeach
            <a href="{{ route('team.index') }}" class="block py-2 text-slate-700">আমাদের টিম</a>
            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="block text-center py-2.5 rounded-lg bg-brand text-white mt-2">
                WhatsApp-এ কথা বলুন
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold text-white mb-3">{{ $siteName }}</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                    {{ cms_setting('site_tagline', 'আধুনিক প্রযুক্তি ও বিশ্বস্ত সেবার মাধ্যমে আপনার ব্যবসাকে এগিয়ে নিতে আমরা প্রতিশ্রুতিবদ্ধ।') }}
                </p>
            </div>
            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-3">গুরুত্বপূর্ণ লিংক</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white">হোম পেজ</a></li>
                    <li><a href="{{ route('team.index') }}" class="hover:text-white">আমাদের টিম</a></li>
                    @foreach(($menuCategories ?? collect())->take(4) as $cat)
                        <li><a href="{{ route('category.show', $cat->slug) }}" class="hover:text-white">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-3">যোগাযোগের ঠিকানা</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li>📍 {{ cms_setting('office_address', 'সিলেট, বাংলাদেশ') }}</li>
                    <li>📞 {{ $phone }}</li>
                    @if(cms_setting('editor_name'))
                        <li>👤 {{ cms_setting('editor_name') }}</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ $siteName }}. সর্বস্বত্ব সংরক্ষিত।
        </div>
    </footer>

    {!! cms_setting('custom_footer_code') !!}
</body>
</html>
