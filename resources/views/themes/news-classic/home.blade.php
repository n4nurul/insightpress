@extends('themes.news-classic.layouts.app')
@section('title', cms_setting('site_name', 'InsightPress News') . ' — ' . cms_setting('site_tagline', ''))

@section('content')
@php
    // Search Query Handling
    $searchQuery = trim(request('q', ''));
    $searchResults = $searchQuery !== ''
        ? \App\Models\Post::published()->with('category')->where('type', 'news')->where('title', 'like', "%{$searchQuery}%")->latest('published_at')->take(20)->get()
        : collect();

    // Lead & Top News Fallback
    $mainLead = $leadNews->first() ?? $latestNews->first();
    $subLeads = $leadNews->skip(1)->take(4);
    if ($subLeads->count() < 4) {
        $excludeIds = collect([$mainLead?->id])->merge($subLeads->pluck('id'))->filter();
        $extra = $latestNews->whereNotIn('id', $excludeIds)->take(4 - $subLeads->count());
        $subLeads = $subLeads->merge($extra);
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    @if($searchQuery !== '')
        <!-- Search Results Section -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8">
            <div class="flex items-center justify-between border-b pb-3 mb-5">
                <h1 class="text-xl font-bold text-slate-900">
                    “{{ $searchQuery }}” অনুসন্ধানের ফলাফল ({{ $searchResults->count() }}টি সংবাদ)
                </h1>
                <a href="{{ route('home') }}" class="text-xs font-semibold text-brand hover:underline">✕ অনুসন্ধান বন্ধ করুন</a>
            </div>
            @if($searchResults->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($searchResults as $item)
                        <article class="group">
                            <a href="{{ route('post.show', $item->slug) }}" class="block overflow-hidden rounded-xl bg-slate-100 aspect-video mb-2.5">
                                @if($item->featured_image)
                                    <img src="{{ asset('storage/' . $item->featured_image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @endif
                            </a>
                            <h2 class="font-bold text-slate-900 group-hover:text-brand leading-snug line-clamp-2">
                                <a href="{{ route('post.show', $item->slug) }}">{{ $item->title }}</a>
                            </h2>
                            <div class="text-[11px] text-slate-400 mt-1">{{ bn_date($item->published_at) }}</div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-center py-8 text-slate-500">দুঃখিত, এই শিরোনামে কোনো সংবাদ পাওয়া যায়নি।</p>
            @endif
        </div>
    @else

    <!-- HERO SECTION: Lead News (Left/Center) + Latest/Popular Tabs (Right) -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-10">
        <!-- Left & Center Lead Area (8 Columns) -->
        <div class="lg:col-span-8 space-y-6">
            @if($mainLead)
            <!-- Main Featured Lead Story -->
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs group">
                <div class="grid grid-cols-1 md:grid-cols-12">
                    <a href="{{ route('post.show', $mainLead->slug) }}" class="md:col-span-7 block overflow-hidden bg-slate-100 aspect-video md:aspect-auto">
                        @if($mainLead->featured_image)
                            <img src="{{ asset('storage/' . $mainLead->featured_image) }}" alt="{{ $mainLead->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-64 flex items-center justify-center text-slate-400">No Image</div>
                        @endif
                    </a>
                    <div class="md:col-span-5 p-5 sm:p-6 flex flex-col justify-between">
                        <div>
                            @if($mainLead->category)
                                <a href="{{ route('category.show', $mainLead->category->slug) }}" class="inline-block px-2.5 py-0.5 rounded bg-red-50 text-brand text-xs font-bold mb-2">
                                    {{ $mainLead->category->name }}
                                </a>
                            @endif
                            @if($mainLead->sub_title)
                                <div class="text-xs font-semibold text-slate-500 mb-1">{{ $mainLead->sub_title }}</div>
                            @endif
                            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 group-hover:text-brand leading-snug mb-3 transition">
                                <a href="{{ route('post.show', $mainLead->slug) }}">{{ $mainLead->title }}</a>
                            </h1>
                            <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                                {{ $mainLead->excerpt ?: Str::limit(strip_tags($mainLead->body), 140) }}
                            </p>
                        </div>
                        <div class="text-xs text-slate-400 mt-4 pt-3 border-t border-slate-100">
                            🕒 {{ bn_datetime($mainLead->published_at) }}
                        </div>
                    </div>
                </div>
            </article>
            @endif

            <!-- 4 Sub-Lead Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($subLeads as $sub)
                <article class="bg-white p-3.5 rounded-xl border border-slate-200 flex gap-3.5 items-start group hover:border-slate-300 transition">
                    <a href="{{ route('post.show', $sub->slug) }}" class="w-28 h-20 rounded-lg overflow-hidden bg-slate-100 shrink-0">
                        @if($sub->featured_image)
                            <img src="{{ asset('storage/' . $sub->featured_image) }}" alt="{{ $sub->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                        @endif
                    </a>
                    <div class="flex-1 min-w-0">
                        @if($sub->category)
                            <span class="text-[11px] font-bold text-brand block mb-0.5">{{ $sub->category->name }}</span>
                        @endif
                        <h2 class="text-sm font-bold text-slate-900 group-hover:text-brand leading-snug line-clamp-2">
                            <a href="{{ route('post.show', $sub->slug) }}">{{ $sub->title }}</a>
                        </h2>
                        <div class="text-[11px] text-slate-400 mt-1">{{ bn_date($sub->published_at) }}</div>
                    </div>
                </article>
                @endforeach
            </div>
        </div>

        <!-- Right Sidebar: Latest vs Popular Tabs + Sidebar Ad (4 Columns) -->
        <aside class="lg:col-span-4 space-y-6" x-data="{ tab: 'latest' }">
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="grid grid-cols-2 border-b border-slate-200 text-sm font-bold">
                    <button type="button" @click="tab = 'latest'"
                            :class="tab === 'latest' ? 'bg-brand text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                            class="py-3 text-center transition">
                        সর্বশেষ সংবাদ
                    </button>
                    <button type="button" @click="tab = 'popular'"
                            :class="tab === 'popular' ? 'bg-brand text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100'"
                            class="py-3 text-center transition">
                        সর্বাধিক পঠিত
                    </button>
                </div>

                <!-- Latest Tab List -->
                <div x-show="tab === 'latest'" class="divide-y divide-slate-100 max-h-[470px] overflow-y-auto">
                    @foreach($latestNews->take(8) as $idx => $item)
                    <a href="{{ route('post.show', $item->slug) }}" class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition group">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 group-hover:bg-brand group-hover:text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            {{ bn_number($idx + 1) }}
                        </span>
                        <div class="text-sm font-semibold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">
                            {{ $item->title }}
                        </div>
                    </a>
                    @endforeach
                </div>

                <!-- Popular Tab List -->
                <div x-show="tab === 'popular'" style="display: none;" class="divide-y divide-slate-100 max-h-[470px] overflow-y-auto">
                    @foreach($popularNews->take(8) as $idx => $item)
                    <a href="{{ route('post.show', $item->slug) }}" class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition group">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 group-hover:bg-brand group-hover:text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            {{ bn_number($idx + 1) }}
                        </span>
                        <div class="text-sm font-semibold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">
                            {{ $item->title }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Sidebar Ad Slot -->
            @if(cms_addon('ad_slots', true) && !empty($ads['sidebar-top']))
                <div class="bg-white p-3 rounded-xl border border-slate-200 text-center overflow-hidden">
                    {!! $ads['sidebar-top']['body'] ?? '' !!}
                </div>
            @endif
        </aside>
    </section>

    <!-- CATEGORY-WISE NEWS BLOCKS -->
    <div class="space-y-10">
        @foreach($categoryBlocks as $catBlock)
            @if($catBlock->latest_posts && $catBlock->latest_posts->count() > 0)
            <section class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs">
                <div class="flex items-center justify-between border-b-2 border-slate-100 pb-3 mb-5">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                        <span class="w-1.5 h-6 bg-brand rounded-full"></span>
                        <a href="{{ route('category.show', $catBlock->slug) }}" class="hover:text-brand">{{ $catBlock->name }}</a>
                    </h2>
                    <a href="{{ route('category.show', $catBlock->slug) }}" class="text-xs font-bold text-brand hover:underline">
                        আরও খবর →
                    </a>
                </div>

                @php
                    $catLead = $catBlock->latest_posts->first();
                    $catOthers = $catBlock->latest_posts->skip(1);
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Category Big Featured Card -->
                    @if($catLead)
                    <article class="lg:col-span-5 group">
                        <a href="{{ route('post.show', $catLead->slug) }}" class="block overflow-hidden rounded-xl bg-slate-100 aspect-video mb-3">
                            @if($catLead->featured_image)
                                <img src="{{ asset('storage/' . $catLead->featured_image) }}" alt="{{ $catLead->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </a>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand leading-snug mb-2">
                            <a href="{{ route('post.show', $catLead->slug) }}">{{ $catLead->title }}</a>
                        </h3>
                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                            {{ $catLead->excerpt ?: Str::limit(strip_tags($catLead->body), 120) }}
                        </p>
                    </article>
                    @endif

                    <!-- Category 4 Secondary News Cards -->
                    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($catOthers as $cPost)
                        <article class="flex gap-3 items-start p-2.5 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200 transition group">
                            <a href="{{ route('post.show', $cPost->slug) }}" class="w-24 h-18 rounded-lg overflow-hidden bg-slate-100 shrink-0 aspect-4/3">
                                @if($cPost->featured_image)
                                    <img src="{{ asset('storage/' . $cPost->featured_image) }}" alt="{{ $cPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                @endif
                            </a>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">
                                    <a href="{{ route('post.show', $cPost->slug) }}">{{ $cPost->title }}</a>
                                </h4>
                                <div class="text-[11px] text-slate-400 mt-1">{{ bn_date($cPost->published_at) }}</div>
                            </div>
                        </article>
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
