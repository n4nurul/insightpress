@extends('themes.sylhet-view.layouts.app')
@section('title', cms_setting('site_name', 'সিলেটভিউ২৪') . ' — ' . cms_setting('site_tagline', ''))

@section('content')
@php
    $searchQuery = trim(request('q', ''));
    $archiveDate = trim(request('date', ''));

    $searchResults = collect();
    if ($searchQuery !== '') {
        $searchResults = \App\Models\Post::published()
            ->with('category')
            ->where('type', 'news')
            ->where('title', 'like', "%{$searchQuery}%")
            ->latest('published_at')
            ->take(24)
            ->get();
    } elseif ($archiveDate !== '') {
        $searchResults = \App\Models\Post::published()
            ->with('category')
            ->where('type', 'news')
            ->whereDate('published_at', $archiveDate)
            ->latest('published_at')
            ->take(30)
            ->get();
    }

    $mainLead = $leadNews->first() ?? $latestNews->first();
    $leftLead = $leadNews->skip(1)->first() ?? $latestNews->where('id', '!=', $mainLead?->id)->first();
    $usedIds = collect([$mainLead?->id, $leftLead?->id])->filter();
    $pool = $leadNews->skip(2)->merge($latestNews)->whereNotIn('id', $usedIds)->unique('id')->values();
    $leftBullets = $pool->take(5);
    $centerSubLeads = $pool->skip(5)->take(2);

    // Section 2: Two Separate Categories -> নির্বাচিত সংবাদ (Selected) & ভিডিও (Video)
    $selectedCat = \App\Models\Category::where('slug', 'selected')
        ->orWhere('slug', 'nirbachito')
        ->orWhere('name', 'নির্বাচিত সংবাদ')
        ->first();

    $selectedNews = $selectedCat
        ? \App\Models\Post::published()->with('category')->inCategory($selectedCat->id)->latest('published_at')->take(6)->get()
        : collect();

    $videoCat = \App\Models\Category::where('slug', 'video')
        ->orWhere('slug', 'videos')
        ->orWhere('name', 'ভিডিও')
        ->first();

    $videoPosts = $videoCat
        ? \App\Models\Post::published()->with('category')->inCategory($videoCat->id)->latest('published_at')->take(2)->get()
        : collect();

    $firstCatBlock = $categoryBlocks->first();
    $otherCatBlocks = $categoryBlocks->skip(1);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
    @if($searchQuery !== '' || $archiveDate !== '')
        <div class="bg-white border border-slate-300 p-5 mb-6">
            <div class="flex items-center justify-between border-b-2 border-sv-red pb-2.5 mb-4">
                <h1 class="text-base font-bold">
                    @if($searchQuery !== '')
                        “{{ $searchQuery }}” অনুসন্ধানের ফলাফল ({{ bn_number($searchResults->count()) }}টি)
                    @else
                        নিউজ আর্কাইভ: {{ bn_date($archiveDate) }} ({{ bn_number($searchResults->count()) }}টি সংবাদ)
                    @endif
                </h1>
                <a href="{{ route('home') }}" class="text-xs font-bold text-sv-red hover:underline">✕ বন্ধ করুন</a>
            </div>
            @if($searchResults->isEmpty())
                <div class="py-10 text-center text-sm text-slate-500">এই তারিখে বা অনুসন্ধানে কোনো সংবাদ পাওয়া যায়নি।</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($searchResults as $item)
                    <a href="{{ route('post.show', $item->slug) }}" class="group block bg-slate-50 border border-slate-200 p-2.5">
                        <div class="aspect-video overflow-hidden bg-slate-200 mb-2">
                            @if($item->featured_image)
                                <img src="{{ asset('storage/' . $item->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            @endif
                        </div>
                        <h2 class="text-sm font-bold text-slate-900 group-hover:text-sv-red line-clamp-2">{{ $item->title }}</h2>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    @else

    <!-- SECTION 1: SYLHETVIEW24 SIGNATURE 3-COLUMN TOP GRID -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-4 mb-8">
        <!-- LEFT COLUMN (3 Cols): 1 Photo Lead + 5 Red-Arrow Headlines -->
        <div class="lg:col-span-3 space-y-3">
            @if($leftLead)
            <article class="bg-white border border-slate-300 group">
                <a href="{{ route('post.show', $leftLead->slug) }}" class="block aspect-video bg-slate-100 overflow-hidden">
                    @if($leftLead->featured_image)
                        <img src="{{ asset('storage/' . $leftLead->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                    @endif
                </a>
                <div class="p-3 border-t-2 border-sv-red">
                    <h2 class="text-base font-bold text-slate-900 group-hover:text-sv-red leading-snug">
                        <a href="{{ route('post.show', $leftLead->slug) }}">{{ $leftLead->title }}</a>
                    </h2>
                </div>
            </article>
            @endif

            <div class="bg-white border border-slate-300 divide-y divide-slate-200">
                @foreach($leftBullets as $bItem)
                <a href="{{ route('post.show', $bItem->slug) }}" class="p-2.5 flex items-start gap-2 hover:bg-red-50/40 group">
                    <span class="text-sv-red font-bold text-sm leading-tight">»</span>
                    <span class="text-sm font-bold text-slate-800 group-hover:text-sv-red leading-snug line-clamp-2">{{ $bItem->title }}</span>
                </a>
                @endforeach
            </div>
        </div>

        <!-- CENTER COLUMN (5 Cols): Main Hero Overlay Lead + 2 Sub-Lead Cards -->
        <div class="lg:col-span-5 space-y-4">
            @if($mainLead)
            <article class="bg-white border border-slate-300 group">
                <a href="{{ route('post.show', $mainLead->slug) }}" class="block aspect-16/10 bg-slate-900 overflow-hidden relative">
                    @if($mainLead->featured_image)
                        <img src="{{ asset('storage/' . $mainLead->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-transparent"></div>
                    <div class="absolute bottom-0 inset-x-0 p-4 text-white">
                        @if($mainLead->category)
                            <span class="inline-block bg-sv-red text-white text-[11px] font-bold px-2 py-0.5 mb-1.5">
                                {{ $mainLead->category->name }}
                            </span>
                        @endif
                        <h1 class="text-xl sm:text-2xl font-bold leading-snug group-hover:text-amber-300 transition">
                            {{ $mainLead->title }}
                        </h1>
                    </div>
                </a>
                <div class="p-3.5 bg-white text-sm text-slate-600 line-clamp-2 leading-relaxed">
                    {{ $mainLead->excerpt ?: Str::limit(strip_tags($mainLead->body), 145) }}
                </div>
            </article>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                @foreach($centerSubLeads as $sub)
                <article class="bg-white border border-slate-300 group">
                    <a href="{{ route('post.show', $sub->slug) }}" class="block aspect-video bg-slate-100 overflow-hidden">
                        @if($sub->featured_image)
                            <img src="{{ asset('storage/' . $sub->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                        @endif
                    </a>
                    <div class="p-3">
                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-2">
                            <a href="{{ route('post.show', $sub->slug) }}">{{ $sub->title }}</a>
                        </h3>
                    </div>
                </article>
                @endforeach
            </div>
        </div>

        <!-- RIGHT COLUMN (4 Cols): Numbered + Thumbnail Latest/Popular Tabs -->
        <aside class="lg:col-span-4 space-y-4" x-data="{ svTab: 'latest' }">
            <div class="bg-white border border-slate-300 overflow-hidden">
                <div class="grid grid-cols-2 text-sm font-bold border-b-2 border-sv-red">
                    <button type="button" @click="svTab = 'latest'"
                            :class="svTab === 'latest' ? 'bg-sv-red text-white' : 'bg-sv-navy text-white/85'"
                            class="py-2.5 text-center transition">
                        সর্বশেষ সংবাদ
                    </button>
                    <button type="button" @click="svTab = 'popular'"
                            :class="svTab === 'popular' ? 'bg-sv-red text-white' : 'bg-sv-navy text-white/85'"
                            class="py-2.5 text-center transition">
                        সর্বাধিক পঠিত
                    </button>
                </div>

                <div x-show="svTab === 'latest'" class="divide-y divide-slate-200 max-h-[505px] overflow-y-auto">
                    @foreach($latestNews->take(9) as $idx => $item)
                    <a href="{{ route('post.show', $item->slug) }}" class="p-2.5 flex gap-2.5 items-center hover:bg-slate-50 group">
                        <span class="w-6 h-6 bg-slate-200 text-slate-800 group-hover:bg-sv-red group-hover:text-white text-xs font-bold flex items-center justify-center shrink-0">
                            {{ bn_number($idx + 1) }}
                        </span>
                        <div class="flex-1 text-sm font-bold text-slate-800 group-hover:text-sv-red leading-snug line-clamp-2">
                            {{ $item->title }}
                        </div>
                        @if($item->featured_image)
                            <img src="{{ asset('storage/' . $item->featured_image) }}" class="w-16 h-12 object-cover shrink-0 border border-slate-200">
                        @endif
                    </a>
                    @endforeach
                </div>

                <div x-show="svTab === 'popular'" style="display: none;" class="divide-y divide-slate-200 max-h-[505px] overflow-y-auto">
                    @foreach($popularNews->take(9) as $idx => $item)
                    <a href="{{ route('post.show', $item->slug) }}" class="p-2.5 flex gap-2.5 items-center hover:bg-slate-50 group">
                        <span class="w-6 h-6 bg-slate-200 text-slate-800 group-hover:bg-sv-red group-hover:text-white text-xs font-bold flex items-center justify-center shrink-0">
                            {{ bn_number($idx + 1) }}
                        </span>
                        <div class="flex-1 text-sm font-bold text-slate-800 group-hover:text-sv-red leading-snug line-clamp-2">
                            {{ $item->title }}
                        </div>
                        @if($item->featured_image)
                            <img src="{{ asset('storage/' . $item->featured_image) }}" class="w-16 h-12 object-cover shrink-0 border border-slate-200">
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            @if(cms_addon('ad_slots', true) && !empty($ads['sidebar-top']))
                <div class="bg-white p-2.5 border border-slate-300 text-center overflow-hidden">
                    {!! $ads['sidebar-top']['body'] ?? '' !!}
                </div>
            @endif
        </aside>
    </section>

    <!-- SECTION 2: SELECTED NEWS (6 CARDS GRID) + VIDEO & NEWS ARCHIVE -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8 bg-white border border-slate-300 p-4 sm:p-5">
        <!-- LEFT 8 COLUMNS: নির্বাচিত সংবাদ (3x2 Grid + Quick Category Bar) -->
        <div class="lg:col-span-8 flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-300 pb-2 mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-sv-red leading-none">@if($selectedCat)<a href="{{ route('category.show', $selectedCat->slug) }}" class="hover:underline">{{ $selectedCat->name }}</a>@else নির্বাচিত সংবাদ @endif</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-5">
                    @foreach($selectedNews as $selItem)
                    <article class="group flex flex-col">
                        <a href="{{ route('post.show', $selItem->slug) }}" class="block aspect-video bg-slate-100 overflow-hidden border border-slate-200 mb-2">
                            @if($selItem->featured_image)
                                <img src="{{ asset('storage/' . $selItem->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </a>
                        <h3 class="text-[15px] font-bold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-2 mb-2">
                            <a href="{{ route('post.show', $selItem->slug) }}">{{ $selItem->title }}</a>
                        </h3>
                        @if($selItem->category)
                            <div class="mt-auto">
                                <a href="{{ route('category.show', $selItem->category->slug) }}"
                                   class="inline-block bg-sv-red text-white text-[11px] font-bold px-2 py-0.5 rounded-xs hover:opacity-90">
                                    {{ $selItem->category->name }}
                                </a>
                            </div>
                        @endif
                    </article>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- RIGHT 4 COLUMNS: ভিডিও + নিউজ আর্কাইভ -->
        <aside class="lg:col-span-4 space-y-5">
            <!-- Video Block -->
            <div>
                <div class="border-b border-slate-300 pb-2 mb-3 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-sv-red leading-none">@if($videoCat)<a href="{{ route('category.show', $videoCat->slug) }}" class="hover:underline">{{ $videoCat->name }}</a>@else ভিডিও @endif</h2>
                </div>

                @php
                    $mainVideo = $videoPosts->first();
                    $secondVideo = $videoPosts->skip(1)->first();
                @endphp

                <div class="bg-[#222222] text-white p-3 space-y-3">
                    @if($mainVideo)
                    <article class="group">
                        <a href="{{ route('post.show', $mainVideo->slug) }}" class="block relative aspect-video bg-black overflow-hidden mb-2">
                            @if($mainVideo->featured_image)
                                <img src="{{ asset('storage/' . $mainVideo->featured_image) }}" class="w-full h-full object-cover opacity-85 group-hover:scale-105 transition">
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="w-12 h-12 rounded-full bg-white/85 text-sv-navy flex items-center justify-center shadow-lg group-hover:bg-sv-red group-hover:text-white transition">
                                    ▶
                                </span>
                            </div>
                        </a>
                        <h3 class="text-sm font-bold text-slate-100 group-hover:text-amber-300 leading-snug">
                            <a href="{{ route('post.show', $mainVideo->slug) }}">{{ $mainVideo->title }}</a>
                        </h3>
                    </article>
                    @endif

                    @if($secondVideo)
                    <article class="pt-3 border-t border-white/15 flex gap-3 items-start group">
                        <a href="{{ route('post.show', $secondVideo->slug) }}" class="relative w-28 aspect-video bg-black overflow-hidden shrink-0">
                            @if($secondVideo->featured_image)
                                <img src="{{ asset('storage/' . $secondVideo->featured_image) }}" class="w-full h-full object-cover opacity-85 group-hover:scale-105 transition">
                            @endif
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="w-7 h-7 rounded-full bg-white/85 text-sv-navy text-xs flex items-center justify-center">▶</span>
                            </div>
                        </a>
                        <h4 class="text-xs font-bold text-slate-200 group-hover:text-amber-300 leading-snug line-clamp-3">
                            <a href="{{ route('post.show', $secondVideo->slug) }}">{{ $secondVideo->title }}</a>
                        </h4>
                    </article>
                    @endif
                </div>
            </div>

            <!-- News Archive Date Filter Widget -->
            <div class="bg-[#222222] text-white p-4">
                <div class="flex items-baseline justify-between mb-3">
                    <h3 class="text-lg font-bold text-white leading-none">নিউজ আর্কাইভ</h3>
                    <span class="text-[11px] text-slate-400">(নির্দিষ্ট দিন অনুযায়ী সার্চ করুন)</span>
                </div>
                <form action="{{ route('home') }}" method="GET" class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ request('date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
                           class="flex-1 px-3 py-2 bg-white text-slate-900 text-xs font-bold rounded-xs focus:outline-none">
                    <button type="submit" title="আর্কাইভ খুঁজুন"
                            class="w-9 h-9 rounded-full bg-sv-red hover:opacity-90 text-white flex items-center justify-center shrink-0 cursor-pointer">
                        🔍
                    </button>
                </form>
            </div>
        </aside>
    </section>

        <!-- SECTION 3: 3-COLUMN x 2-ROW (6 CATEGORIES) DISTRICT / TOPIC GRID -->
    @php
        $gridSixCats = $categoryBlocks->filter(fn($c) => $c->latest_posts && $c->latest_posts->count() > 0 && !in_array($c->slug, ['selected', 'nirbachito', 'video', 'videos']) && !in_array($c->name, ['নির্বাচিত সংবাদ', 'ভিডিও']))->take(6);
        $remainingCatBlocks = $categoryBlocks->filter(fn($c) => $c->latest_posts && $c->latest_posts->count() > 0 && !str_contains($c->name, 'খেলা') && !in_array($c->slug, ['selected', 'nirbachito', 'video', 'videos']) && !in_array($c->name, ['নির্বাচিত সংবাদ', 'ভিডিও']))->skip(6);
    @endphp

    @if($gridSixCats->count() > 0)
    <section class="bg-white border border-slate-300 p-4 sm:p-5 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
            @foreach($gridSixCats as $catBlock)
                @php
                    $leadPost = $catBlock->latest_posts->first();
                    $bulletPosts = $catBlock->latest_posts->skip(1)->take(2);
                @endphp
                <div class="flex flex-col">
                    <!-- Top Black Border + Red Category Title -->
                    <div class="border-t-2 border-slate-900 pt-2 pb-2 mb-3 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-sv-red leading-none">
                            <a href="{{ route('category.show', $catBlock->slug) }}" class="hover:underline">{{ $catBlock->name }}</a>
                        </h2>
                    </div>

                    <!-- 1 Featured Photo Card -->
                    @if($leadPost)
                    <article class="group mb-3">
                        <a href="{{ route('post.show', $leadPost->slug) }}" class="block aspect-video bg-slate-100 overflow-hidden border border-slate-200 mb-2.5">
                            @if($leadPost->featured_image)
                                <img src="{{ asset('storage/' . $leadPost->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </a>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-2">
                            <a href="{{ route('post.show', $leadPost->slug) }}">{{ $leadPost->title }}</a>
                        </h3>
                    </article>
                    @endif

                    <!-- 2 Square-Bullet Sub Headlines -->
                    <div class="space-y-2.5 pt-2.5 border-t border-slate-200">
                        @foreach($bulletPosts as $bPost)
                        <a href="{{ route('post.show', $bPost->slug) }}" class="flex items-start gap-2 group">
                            <span class="text-slate-500 group-hover:text-sv-red text-[10px] mt-1.5 shrink-0">▪</span>
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-sv-red leading-snug line-clamp-2">
                                {{ $bPost->title }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    <!-- SECTION 4: FULL-WIDTH DARK STADIUM SPORTS BANNER (খেলাধুলা) -->
    @php
        $sportsCat = $categoryBlocks->first(function ($c) {
            return str_contains(mb_strtolower($c->slug), 'sport')
                || str_contains(mb_strtolower($c->slug), 'khela')
                || str_contains($c->name, 'খেলা');
        });
        $sportsPosts = $sportsCat
            ? \App\Models\Post::published()
                ->with('category')
                ->inCategory($sportsCat->id)
                ->latest('published_at')
                ->take(8)
                ->get()
            : collect();
    @endphp

    @if($sportsCat && $sportsPosts->count() > 0)
    </div><!-- close outer container for full-bleed banner -->
    <section class="relative w-full bg-[#0b1f14] text-white py-10 mb-8 overflow-hidden border-y border-emerald-950"
             style="background-image: linear-gradient(rgba(8, 22, 14, 0.84), rgba(6, 16, 11, 0.90)), radial-gradient(circle at 50% 35%, #1d5c37 0%, #09170f 75%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Top Section Title -->
            <div class="mb-5 flex items-center justify-between">
                <h2 class="inline-block text-xl font-bold text-amber-200 border-t-2 border-amber-200/80 pt-1.5 leading-none">
                    <a href="{{ route('category.show', $sportsCat->slug) }}" class="hover:text-white transition">
                        {{ $sportsCat->name }}
                    </a>
                </h2>
                <a href="{{ route('category.show', $sportsCat->slug) }}" class="text-xs font-bold text-amber-200 hover:text-white">
                    সব খেলাধুলা »
                </a>
            </div>

            @php
                $spLead = $sportsPosts->first();
                $spMiddle = $sportsPosts->skip(1)->take(2);
                $spRight = $sportsPosts->skip(3)->take(5);
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left 5 Cols: Big Lead Photo + Title + Excerpt -->
                @if($spLead)
                <article class="lg:col-span-5 group">
                    <a href="{{ route('post.show', $spLead->slug) }}" class="block aspect-16/10 bg-black/50 overflow-hidden mb-3.5 border border-white/15">
                        @if($spLead->featured_image)
                            <img src="{{ asset('storage/' . $spLead->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                    </a>
                    <h3 class="text-xl sm:text-2xl font-bold text-white group-hover:text-amber-300 leading-snug mb-2">
                        <a href="{{ route('post.show', $spLead->slug) }}">{{ $spLead->title }}</a>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 line-clamp-2 leading-relaxed">
                        {{ $spLead->excerpt ?: Str::limit(strip_tags($spLead->body), 135) }}
                    </p>
                </article>
                @endif

                <!-- Middle 3 Cols: 2 Stacked Photo Cards -->
                <div class="lg:col-span-3 space-y-4">
                    @foreach($spMiddle as $mSp)
                    <article class="group">
                        <a href="{{ route('post.show', $mSp->slug) }}" class="block aspect-video bg-black/50 overflow-hidden mb-2 border border-white/15">
                            @if($mSp->featured_image)
                                <img src="{{ asset('storage/' . $mSp->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </a>
                        <h4 class="text-sm font-bold text-slate-100 group-hover:text-amber-300 leading-snug line-clamp-2">
                            <a href="{{ route('post.show', $mSp->slug) }}">{{ $mSp->title }}</a>
                        </h4>
                    </article>
                    @endforeach
                </div>

                <!-- Right 4 Cols: 5 Horizontal Thumbnail + Title Items -->
                <div class="lg:col-span-4 divide-y divide-white/15">
                    @foreach($spRight as $rSp)
                    <a href="{{ route('post.show', $rSp->slug) }}" class="py-2.5 first:pt-0 last:pb-0 flex gap-3 items-center group">
                        @if($rSp->featured_image)
                            <img src="{{ asset('storage/' . $rSp->featured_image) }}" class="w-24 h-14 object-cover shrink-0 border border-white/15 bg-black/40">
                        @endif
                        <span class="text-sm font-bold text-slate-100 group-hover:text-amber-300 leading-snug line-clamp-2">
                            {{ $rSp->title }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6"><!-- reopen outer container -->
    @endif

    <!-- SECTION 5: 3-COLUMN ASYMMETRIC FEATURE (মুক্তমত 3 : লাইফস্টাইল 6 : সাহিত্য 3) -->
    @php
        $usedSec5Ids = collect();

        $opinionCat = $categoryBlocks->first(fn($c) => str_contains($c->name, 'মুক্তমত') || str_contains(mb_strtolower($c->slug), 'opinion') || str_contains(mb_strtolower($c->slug), 'muktomot'));
        if (!$opinionCat) {
            $opinionCat = $remainingCatBlocks->first();
        }
        if ($opinionCat) $usedSec5Ids->push($opinionCat->id);

        $lifestyleCat = $categoryBlocks->first(fn($c) => !$usedSec5Ids->contains($c->id) && (str_contains($c->name, 'লাইফস্টাইল') || str_contains(mb_strtolower($c->slug), 'lifestyle')));
        if (!$lifestyleCat) {
            $lifestyleCat = $remainingCatBlocks->first(fn($c) => !$usedSec5Ids->contains($c->id));
        }
        if ($lifestyleCat) $usedSec5Ids->push($lifestyleCat->id);

        $literatureCat = $categoryBlocks->first(fn($c) => !$usedSec5Ids->contains($c->id) && (str_contains($c->name, 'সাহিত্য') || str_contains(mb_strtolower($c->slug), 'literature') || str_contains(mb_strtolower($c->slug), 'sahitto')));
        if (!$literatureCat) {
            $literatureCat = $remainingCatBlocks->first(fn($c) => !$usedSec5Ids->contains($c->id));
        }
        if ($literatureCat) $usedSec5Ids->push($literatureCat->id);

        $finalRemainingBlocks = $remainingCatBlocks->filter(fn($c) => !$usedSec5Ids->contains($c->id) && !str_contains($c->name, 'বিনোদন'));
    @endphp

    @if($opinionCat || $lifestyleCat || $literatureCat)
    <section class="bg-white border border-slate-300 p-4 sm:p-5 mb-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- LEFT 3 COLS: মুক্তমত (Circular Author Avatar + Italic Title + Author Name) -->
            <div class="lg:col-span-3">
                @if($opinionCat && $opinionCat->latest_posts && $opinionCat->latest_posts->count() > 0)
                <div class="border-t-2 border-slate-900 pt-2 pb-2 mb-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-sv-red leading-none">
                        <a href="{{ route('category.show', $opinionCat->slug) }}" class="hover:underline">{{ $opinionCat->name }}</a>
                    </h2>
                </div>

                <div class="divide-y divide-slate-200">
                    @foreach($opinionCat->latest_posts->take(2) as $opPost)
                    <article class="py-4 first:pt-1 last:pb-1 text-center group">
                        <a href="{{ route('post.show', $opPost->slug) }}" class="inline-block mb-2.5">
                            @if($opPost->featured_image)
                                <img src="{{ asset('storage/' . $opPost->featured_image) }}"
                                     class="w-20 h-20 rounded-full object-cover mx-auto border-2 border-slate-200 group-hover:border-sv-red transition">
                            @else
                                <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-500 font-bold text-xl flex items-center justify-center mx-auto border border-slate-200">
                                    ✍
                                </div>
                            @endif
                        </a>
                        <h3 class="text-sm font-bold italic text-[#1e3a5f] group-hover:text-sv-red leading-snug mb-1.5 px-1">
                            <a href="{{ route('post.show', $opPost->slug) }}">{{ $opPost->title }}</a>
                        </h3>
                        <div class="text-xs font-bold text-slate-800">
                            {{ $opPost->sub_title ?: ($opPost->author?->name ?? 'কলামিস্ট') }}
                        </div>
                    </article>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- CENTER 6 COLS: লাইফস্টাইল (1 Wide Overlay Hero + 2 Side-by-Side Bottom Cards) -->
            <div class="lg:col-span-6">
                @if($lifestyleCat && $lifestyleCat->latest_posts && $lifestyleCat->latest_posts->count() > 0)
                <div class="border-t-2 border-slate-900 pt-2 pb-2 mb-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-sv-red leading-none">
                        <a href="{{ route('category.show', $lifestyleCat->slug) }}" class="hover:underline">{{ $lifestyleCat->name }}</a>
                    </h2>
                </div>

                @php
                    $lifeLead = $lifestyleCat->latest_posts->first();
                    $lifeSubs = $lifestyleCat->latest_posts->skip(1)->take(2);
                @endphp

                @if($lifeLead)
                <article class="group mb-4">
                    <a href="{{ route('post.show', $lifeLead->slug) }}" class="block relative aspect-16/9 bg-slate-900 overflow-hidden border border-slate-200">
                        @if($lifeLead->featured_image)
                            <img src="{{ asset('storage/' . $lifeLead->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                        <div class="absolute inset-x-0 bottom-0 bg-black/75 px-4 py-3 text-center">
                            <h3 class="text-base sm:text-lg font-bold text-white group-hover:text-amber-300 leading-snug line-clamp-1">
                                {{ $lifeLead->title }}
                            </h3>
                        </div>
                    </a>
                </article>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    @foreach($lifeSubs as $lSub)
                    <a href="{{ route('post.show', $lSub->slug) }}" class="flex items-start gap-2.5 group sm:border-r last:border-r-0 border-slate-200 sm:pr-2">
                        @if($lSub->featured_image)
                            <img src="{{ asset('storage/' . $lSub->featured_image) }}" class="w-20 h-14 object-cover shrink-0 border border-slate-200">
                        @endif
                        <span class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-3">
                            {{ $lSub->title }}
                        </span>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- RIGHT 3 COLS: সাহিত্য (Horizontal Thumbnail + Title List) -->
            <div class="lg:col-span-3">
                @if($literatureCat && $literatureCat->latest_posts && $literatureCat->latest_posts->count() > 0)
                <div class="border-t-2 border-slate-900 pt-2 pb-2 mb-4 border-b border-slate-200">
                    <h2 class="text-lg font-bold text-sv-red leading-none">
                        <a href="{{ route('category.show', $literatureCat->slug) }}" class="hover:underline">{{ $literatureCat->name }}</a>
                    </h2>
                </div>

                <div class="divide-y divide-slate-200">
                    @foreach($literatureCat->latest_posts->take(3) as $litPost)
                    <a href="{{ route('post.show', $litPost->slug) }}" class="py-3 first:pt-0 last:pb-0 flex items-start gap-2.5 group">
                        @if($litPost->featured_image)
                            <img src="{{ asset('storage/' . $litPost->featured_image) }}" class="w-24 h-16 object-cover shrink-0 border border-slate-200">
                        @endif
                        <span class="text-xs sm:text-sm font-semibold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-3">
                            {{ $litPost->title }}
                        </span>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>
    @endif

    <!-- SECTION 6: FULL-WIDTH DARK MAROON ENTERTAINMENT BANNER (বিনোদন) -->
    @php
        $entCat = $categoryBlocks->first(function ($c) {
            return str_contains(mb_strtolower($c->slug), 'entertainment')
                || str_contains(mb_strtolower($c->slug), 'binodon')
                || str_contains($c->name, 'বিনোদন');
        });
        $entPosts = $entCat
            ? \App\Models\Post::published()
                ->with('category')
                ->inCategory($entCat->id)
                ->latest('published_at')
                ->take(6)
                ->get()
            : collect();
    @endphp

    @if($entCat && $entPosts->count() > 0)
    </div><!-- close outer container for full-bleed banner -->
    <section class="relative w-full bg-[#360909] text-white py-10 mb-8 overflow-hidden border-y border-red-950"
             style="background-image: radial-gradient(circle at 80% 25%, rgba(185, 28, 28, 0.35) 0%, transparent 55%), linear-gradient(135deg, #2b0707 0%, #4d0d0d 55%, #240505 100%);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <!-- Top Section Title with Horizontal Line -->
            <div class="border-b border-white/25 pb-2 mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-amber-100 leading-none">
                    <a href="{{ route('category.show', $entCat->slug) }}" class="hover:text-white transition">
                        {{ $entCat->name }}
                    </a>
                </h2>
                <a href="{{ route('category.show', $entCat->slug) }}" class="text-xs font-bold text-amber-200 hover:text-white">
                    সব বিনোদন »
                </a>
            </div>

            @php
                $entLead = $entPosts->first();
                $entGrid = $entPosts->skip(1)->take(5);
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Left 6 Cols: Big Lead Photo Only (1 Post) -->
                @if($entLead)
                <article class="lg:col-span-6 group">
                    <a href="{{ route('post.show', $entLead->slug) }}" class="block aspect-16/10 bg-black/50 overflow-hidden mb-3.5 border border-white/15">
                        @if($entLead->featured_image)
                            <img src="{{ asset('storage/' . $entLead->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                    </a>
                    <h3 class="text-xl sm:text-2xl font-bold text-white group-hover:text-amber-300 leading-snug mb-2">
                        <a href="{{ route('post.show', $entLead->slug) }}">{{ $entLead->title }}</a>
                    </h3>
                    <p class="text-xs sm:text-sm text-rose-100/80 line-clamp-2 leading-relaxed">
                        {{ $entLead->excerpt ?: Str::limit(strip_tags($entLead->body), 140) }}
                    </p>
                </article>
                @endif

                <!-- Right 6 Cols: 2-Column Grid with 5 Cards (Total 6 Posts) -->
                <div class="lg:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-5">
                    @foreach($entGrid as $eItem)
                    <article class="group">
                        <a href="{{ route('post.show', $eItem->slug) }}" class="block aspect-video bg-black/50 overflow-hidden mb-2 border border-white/15">
                            @if($eItem->featured_image)
                                <img src="{{ asset('storage/' . $eItem->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </a>
                        <h4 class="text-sm font-bold text-slate-100 group-hover:text-amber-300 leading-snug line-clamp-2">
                            <a href="{{ route('post.show', $eItem->slug) }}">{{ $eItem->title }}</a>
                        </h4>
                    </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6"><!-- reopen outer container -->
    @endif

    <!-- SECTION 7+: ALL REMAINING CATEGORIES IN 3-COLUMN SYLHETVIEW STYLE (Unlimited Categories Supported) -->
    @if($finalRemainingBlocks->count() > 0)
    <section class="bg-white border border-slate-300 p-4 sm:p-5 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-8">
            @foreach($finalRemainingBlocks as $catBlock)
                @php
                    $leadPost = $catBlock->latest_posts->first();
                    $bulletPosts = $catBlock->latest_posts->skip(1)->take(2);
                @endphp
                <div class="flex flex-col">
                    <!-- Top Black Border + Red Category Title -->
                    <div class="border-t-2 border-slate-900 pt-2 pb-2 mb-3 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-sv-red leading-none">
                            <a href="{{ route('category.show', $catBlock->slug) }}" class="hover:underline">{{ $catBlock->name }}</a>
                        </h2>
                        <a href="{{ route('category.show', $catBlock->slug) }}" class="text-[11px] font-bold text-slate-500 hover:text-sv-red">আরও »</a>
                    </div>

                    <!-- 1 Featured Photo Card -->
                    @if($leadPost)
                    <article class="group mb-3">
                        <a href="{{ route('post.show', $leadPost->slug) }}" class="block aspect-video bg-slate-100 overflow-hidden border border-slate-200 mb-2.5">
                            @if($leadPost->featured_image)
                                <img src="{{ asset('storage/' . $leadPost->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </a>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-2">
                            <a href="{{ route('post.show', $leadPost->slug) }}">{{ $leadPost->title }}</a>
                        </h3>
                    </article>
                    @endif

                    <!-- 2 Square-Bullet Sub Headlines -->
                    <div class="space-y-2.5 pt-2.5 border-t border-slate-200">
                        @foreach($bulletPosts as $bPost)
                        <a href="{{ route('post.show', $bPost->slug) }}" class="flex items-start gap-2 group">
                            <span class="text-slate-500 group-hover:text-sv-red text-[10px] mt-1.5 shrink-0">▪</span>
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-sv-red leading-snug line-clamp-2">
                                {{ $bPost->title }}
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    @endif
    @endif
</div>
@endsection
