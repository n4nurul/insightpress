@extends('themes.sylhet-view.layouts.app')
@section('title', cms_setting('site_name', 'সিলেটভিউ২৪') . ' — ' . cms_setting('site_tagline', ''))

@section('content')
@php
    $searchQuery = trim(request('q', ''));
    $searchResults = $searchQuery !== ''
        ? \App\Models\Post::published()->with('category')->where('type', 'news')->where('title', 'like', "%{$searchQuery}%")->latest('published_at')->take(20)->get()
        : collect();

    $mainLead = $leadNews->first() ?? $latestNews->first();
    $leftLead = $leadNews->skip(1)->first() ?? $latestNews->where('id', '!=', $mainLead?->id)->first();
    $usedIds = collect([$mainLead?->id, $leftLead?->id])->filter();
    $pool = $leadNews->skip(2)->merge($latestNews)->whereNotIn('id', $usedIds)->unique('id')->values();
    $leftBullets = $pool->take(5);
    $centerSubLeads = $pool->skip(5)->take(2);
    $firstCatBlock = $categoryBlocks->first();
    $otherCatBlocks = $categoryBlocks->skip(1);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
    @if($searchQuery !== '')
        <div class="bg-white border border-slate-300 p-5 mb-6">
            <div class="flex items-center justify-between border-b-2 border-sv-red pb-2.5 mb-4">
                <h1 class="text-base font-bold">“{{ $searchQuery }}” অনুসন্ধানের ফলাফল ({{ bn_number($searchResults->count()) }}টি)</h1>
                <a href="{{ route('home') }}" class="text-xs font-bold text-sv-red hover:underline">✕ বন্ধ করুন</a>
            </div>
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
        </div>
    @else

    <!-- SYLHETVIEW24 SIGNATURE 3-COLUMN TOP GRID -->
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

    <!-- FULL-WIDTH REGIONAL / SYLHET SPECIAL SECTION -->
    @if($firstCatBlock && $firstCatBlock->latest_posts && $firstCatBlock->latest_posts->count() > 0)
    <section class="bg-white border border-slate-300 mb-8">
        <div class="bg-sv-navy text-white px-4 py-2.5 flex items-center justify-between border-b-4 border-sv-red">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-sv-red inline-block"></span>
                <a href="{{ route('category.show', $firstCatBlock->slug) }}">{{ $firstCatBlock->name }}</a>
            </h2>
            <a href="{{ route('category.show', $firstCatBlock->slug) }}" class="text-xs font-bold bg-sv-red px-3 py-1 rounded-sm hover:opacity-90">
                সব খবর »
            </a>
        </div>

        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($firstCatBlock->latest_posts->take(4) as $regPost)
            <article class="border border-slate-200 group bg-slate-50/50">
                <a href="{{ route('post.show', $regPost->slug) }}" class="block aspect-video bg-slate-200 overflow-hidden">
                    @if($regPost->featured_image)
                        <img src="{{ asset('storage/' . $regPost->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                    @endif
                </a>
                <div class="p-3">
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-sv-red leading-snug line-clamp-2">
                        <a href="{{ route('post.show', $regPost->slug) }}">{{ $regPost->title }}</a>
                    </h3>
                </div>
            </article>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 2-COLUMN BOXED CATEGORY PORTAL BLOCKS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($otherCatBlocks as $catBlock)
            @if($catBlock->latest_posts && $catBlock->latest_posts->count() > 0)
            <section class="bg-white border border-slate-300 p-4">
                <div class="flex items-center justify-between border-b-2 border-slate-200 pb-2 mb-4">
                    <h2 class="text-lg font-bold text-slate-900 border-l-4 border-sv-red pl-2.5 leading-none">
                        <a href="{{ route('category.show', $catBlock->slug) }}" class="hover:text-sv-red">{{ $catBlock->name }}</a>
                    </h2>
                    <a href="{{ route('category.show', $catBlock->slug) }}" class="text-xs font-bold text-sv-red hover:underline">আরও খবর »</a>
                </div>

                @php
                    $firstPost = $catBlock->latest_posts->first();
                    $restPosts = $catBlock->latest_posts->skip(1)->take(4);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                    @if($firstPost)
                    <article class="sm:col-span-6 group">
                        <a href="{{ route('post.show', $firstPost->slug) }}" class="block aspect-video overflow-hidden bg-slate-100 mb-2 border border-slate-200">
                            @if($firstPost->featured_image)
                                <img src="{{ asset('storage/' . $firstPost->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            @endif
                        </a>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-sv-red leading-snug mb-1.5">
                            <a href="{{ route('post.show', $firstPost->slug) }}">{{ $firstPost->title }}</a>
                        </h3>
                        <p class="text-xs text-slate-600 line-clamp-2">
                            {{ $firstPost->excerpt ?: Str::limit(strip_tags($firstPost->body), 90) }}
                        </p>
                    </article>
                    @endif

                    <div class="sm:col-span-6 divide-y divide-slate-200">
                        @foreach($restPosts as $rPost)
                        <a href="{{ route('post.show', $rPost->slug) }}" class="py-2.5 first:pt-0 last:pb-0 flex gap-2.5 items-start group">
                            @if($rPost->featured_image)
                                <img src="{{ asset('storage/' . $rPost->featured_image) }}" class="w-16 h-12 object-cover shrink-0 border border-slate-200">
                            @endif
                            <span class="text-xs font-bold text-slate-800 group-hover:text-sv-red leading-snug line-clamp-2">{{ $rPost->title }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
        @endforeach
    </div>
    @endif
</div>
@endsection
