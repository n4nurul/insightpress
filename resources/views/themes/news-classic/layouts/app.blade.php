<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteName = cms_setting('site_name', 'সিলেট প্রতিদিন ২৪');
        $primaryColor = cms_setting('primary_color', '#dc2626');
        $metaTitle = trim($__env->yieldContent('meta_title')) ?: $siteName . ' — ' . cms_setting('site_tagline', 'সত্য ও ন্যায়ের পথে');
        $metaDesc = trim($__env->yieldContent('meta_desc')) ?: cms_setting('default_meta_desc', $siteName);
        $ogImage = trim($__env->yieldContent('og_image')) ?: (cms_setting('logo') ? asset('storage/' . cms_setting('logo')) : '');
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph (Facebook & WhatsApp Preview) -->
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif

    @yield('schema')

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: {{ $primaryColor }}; }
        body { font-family: 'Hind Siliguri', sans-serif; }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
    </style>

    @if($pixelId = cms_setting('fb_pixel_id'))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $pixelId }}'); fbq('track', 'PageView');
    </script>
    @endif
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">
    <!-- Top Date & Social Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-1.5">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <span>📅 {{ bn_date(now()) }}</span>
            <div class="space-x-4">
                <a href="{{ route('team.index') }}" class="hover:text-white">সম্পাদকীয় ও প্রতিনিধি</a>
                <a href="/admin/dashboard" class="hover:text-white">অ্যাডমিন</a>
            </div>
        </div>
    </div>

    <!-- Header Logo & Top Ad -->
    <header class="bg-white border-b border-slate-200 py-4">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <a href="/" class="flex items-center gap-3">
                @if(cms_setting('logo'))
                    <img src="{{ asset('storage/' . cms_setting('logo')) }}" alt="{{ $siteName }}" class="h-14 object-contain">
                @else
                    <div>
                        <span class="text-3xl font-extrabold text-primary">{{ $siteName }}</span>
                        <p class="text-xs text-slate-500">{{ cms_setting('site_tagline', 'সত্য ও ন্যায়ের পথে অবিচল') }}</p>
                    </div>
                @endif
            </a>
            <div class="min-h-[70px] flex items-center justify-center">
                @yield('header_ad')
            </div>
        </div>
    </header>

    <!-- Main Navigation Menu -->
    <nav class="bg-primary text-white sticky top-0 z-40 shadow">
        <div class="max-w-7xl mx-auto px-4 flex items-center gap-1 overflow-x-auto text-sm font-semibold">
            <a href="/" class="px-3.5 py-3 hover:bg-black/20 whitespace-nowrap">প্রচ্ছদ</a>
            @foreach($menuCategories ?? [] as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="px-3.5 py-3 hover:bg-black/20 whitespace-nowrap">
                    {{ $cat->name }}
                </a>
            @endforeach
            <a href="{{ route('team.index') }}" class="px-3.5 py-3 hover:bg-black/20 whitespace-nowrap">আমাদের পরিবার</a>
        </div>
    </nav>

    <!-- Page Body -->
    <main class="max-w-7xl mx-auto px-4 py-6 flex-1 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-12 border-t-4 border-primary">
        <div class="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <h3 class="text-xl font-bold text-white mb-2">{{ $siteName }}</h3>
                <p class="text-slate-400">{{ cms_setting('site_tagline') }}</p>
            </div>
            <div>
                <h4 class="font-bold text-white mb-2">সম্পাদকীয় কার্যালয়</h4>
                @if(cms_setting('editor_name'))<p>সম্পাদক ও প্রকাশক: <strong class="text-white">{{ cms_setting('editor_name') }}</strong></p>@endif
                <p>ঠিকানা: {{ cms_setting('office_address', 'সিলেট, বাংলাদেশ') }}</p>
                <p>মোবাইল: {{ cms_setting('contact_phone') }}</p>
            </div>
            <div class="md:text-right flex flex-col justify-between">
                <div class="space-x-3">
                    <a href="{{ route('team.index') }}" class="hover:text-white">প্রতিনিধি তালিকা</a>
                    <a href="/sitemap.xml" class="hover:text-white">সাইটম্যাপ</a>
                </div>
                <p class="text-xs text-slate-500 mt-4">© {{ date('Y') }} {{ $siteName }}। সর্বস্বত্ব সংরক্ষিত।</p>
            </div>
        </div>
    </footer>

    <!-- Delayed Google AdSense Loader (Zero PageSpeed Penalty) -->
    @if($adsense = cms_setting('adsense_client'))
    <script>
        let adLoaded = false;
        function loadAdSense() {
            if (adLoaded) return; adLoaded = true;
            let s = document.createElement('script');
            s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsense }}';
            s.async = true; s.crossOrigin = 'anonymous';
            document.head.appendChild(s);
        }
        ['scroll', 'mousemove', 'touchstart'].forEach(e => window.addEventListener(e, loadAdSense, { once: true }));
        setTimeout(loadAdSense, 2500);
    </script>
    @endif
    @stack('scripts')
</body>
</html>
