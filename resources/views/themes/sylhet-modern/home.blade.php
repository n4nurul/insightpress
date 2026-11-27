@extends('themes.sylhet-modern.layouts.app')
@section('title', cms_setting('site_name', 'সিলেট টুডে ২৪') . ' — ' . cms_setting('site_tagline', ''))

@section('content')
@php
    $searchQuery = trim(request('q', ''));
    $searchResults = $searchQuery !== ''
        ? \App\Models\Post::published()->with('category')->where('type', 'news')->where('title', 'like', "%{$searchQuery}%")->latest('published_at')->take(20)->get()
        : collect();

    $mainLead = $leadNews->first() ?? $latestNews->first();
    $centerLeads = $leadNews->skip(1)->take(4);
    if ($centerLeads->count() < 4) {
        $exclude = collect([$mainLead?->id])->merge($centerLeads->pluck('id'))->filter();
        $centerLeads = $centerLeads->merge($latestNews->whereNotIn('id', $exclude)->take(4 - $centerLeads->count()));
    }
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @if($searchQuery !== '')
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-8">
            <div class="flex items-center justify-between border-b pb-3 mb-5">
                <h1 class="text-lg font-bold text-slate-900">“{{ $searchQuery }}” অনুসন্ধানের ফলাফল ({{ bn_number($searchResults->count()) }}টি)</h1>
                <a href="{{ route('home') }}" class="text-xs font-bold text-rose-600 hover:underline">✕ বন্ধ করুন</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($searchResults as $item)
                <article class="group">
                    <a href="{{ route('post.show', $item->slug) }}" class="block rounded-lg overflow-hidden bg-slate-100 aspect-video mb-2">
                        @if($item->featured_image)
                            <img src="{{ asset('storage/' . $item->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                        @endif
                    </a>
                    <h2 class="text-sm font-bold text-slate-900 group-hover:text-brand leading-snug">
                        <a href="{{ route('post.show', $item->slug) }}">{{ $item->title }}</a>
                    </h2>
                </article>
                @endforeach
            </div>
        </div>
    @else

    <!-- SYLHET TODAY 3-COLUMN HERO GRID -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-10">
        <!-- Left Main Featured Story (5 Columns) -->
        <div class="lg:col-span-5">
            @if($mainLead)
            <article class="bg-white rounded-xl border border-slate-200 overflow-hidden h-full flex flex-col justify-between group shadow-xs">
                <div>
                    <a href="{{ route('post.show', $mainLead->slug) }}" class="block overflow-hidden bg-slate-100 aspect-16/10 relative">
                        @if($mainLead->featured_image)
                            <img src="{{ asset('storage/' . $mainLead->featured_image) }}" alt="{{ $mainLead->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                        @if($mainLead->category)
                            <span class="absolute bottom-3 left-3 bg-brand text-white text-xs font-bold px-2.5 py-1 rounded">
                                {{ $mainLead->category->name }}
                            </span>
                        @endif
                    </a>
                    <div class="p-5">
                        @if($mainLead->sub_title)
                            <div class="text-xs font-bold text-brand mb-1">{{ $mainLead->sub_title }}</div>
                        @endif
                        <h1 class="text-2xl font-bold text-slate-900 group-hover:text-brand leading-snug mb-3 transition">
                            <a href="{{ route('post.show', $mainLead->slug) }}">{{ $mainLead->title }}</a>
                        </h1>
                        <p class="text-sm text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $mainLead->excerpt ?: Str::limit(strip_tags($mainLead->body), 165) }}
                        </p>
                    </div>
                </div>
                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 text-xs text-slate-500">
                    🕒 {{ bn_datetime($mainLead->published_at) }}
                </div>
            </article>
            @endif
        </div>

        <!-- Center 2x2 Sub-Lead Cards (4 Columns) -->
        <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
            @foreach($centerLeads as $cLead)
            <article class="bg-white rounded-xl border border-slate-200 overflow-hidden group flex flex-col justify-between shadow-xs">
                <div>
                    <a href="{{ route('post.show', $cLead->slug) }}" class="block overflow-hidden bg-slate-100 aspect-video">
                        @if($cLead->featured_image)
                            <img src="{{ asset('storage/' . $cLead->featured_image) }}" alt="{{ $cLead->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                    </a>
                    <div class="p-3">
                        <h2 class="text-sm font-bold text-slate-900 group-hover:text-brand leading-snug line-clamp-3">
                            <a href="{{ route('post.show', $cLead->slug) }}">{{ $cLead->title }}</a>
                        </h2>
                    </div>
                </div>
                <div class="px-3 pb-2.5 text-[11px] text-slate-400">
                    {{ bn_date($cLead->published_at) }}
                </div>
            </article>
            @endforeach
        </div>

        <!-- Right Latest / Trending Feed (3 Columns) -->
        <aside class="lg:col-span-3 bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col shadow-xs" x-data="{ mode: 'latest' }">
            <div class="grid grid-cols-2 text-xs font-bold border-b border-slate-200">
                <button type="button" @click="mode = 'latest'"
                        :class="mode === 'latest' ? 'bg-brand text-white' : 'bg-slate-50 text-slate-700'"
                        class="py-3 text-center transition">
                    সর্বশেষ
                </button>
                <button type="button" @click="mode = 'popular'"
                        :class="mode === 'popular' ? 'bg-brand text-white' : 'bg-slate-50 text-slate-700'"
                        class="py-3 text-center transition">
                    আলোচিত
                </button>
            </div>

            <div x-show="mode === 'latest'" class="divide-y divide-slate-100 overflow-y-auto max-h-[450px]">
                @foreach($latestNews->take(8) as $item)
                <a href="{{ route('post.show', $item->slug) }}" class="p-3 flex gap-2.5 items-start hover:bg-slate-50 transition group">
                    @if($item->featured_image)
                        <img src="{{ asset('storage/' . $item->featured_image) }}" class="w-16 h-12 rounded object-cover shrink-0">
                    @endif
                    <div class="text-xs font-bold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">
                        {{ $item->title }}
                    </div>
                </a>
                @endforeach
            </div>

            <div x-show="mode === 'popular'" style="display: none;" class="divide-y divide-slate-100 overflow-y-auto max-h-[450px]">
                @foreach($popularNews->take(8) as $item)
                <a href="{{ route('post.show', $item->slug) }}" class="p-3 flex gap-2.5 items-start hover:bg-slate-50 transition group">
                    @if($item->featured_image)
                        <img src="{{ asset('storage/' . $item->featured_image) }}" class="w-16 h-12 rounded object-cover shrink-0">
                    @endif
                    <div class="text-xs font-bold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">
                        {{ $item->title }}
                    </div>
                </a>
                @endforeach
            </div>
        </aside>
    </section>

    <!-- REGIONAL / CATEGORY HIGHLIGHTS -->
    <div class="space-y-8">
        @foreach($categoryBlocks as $idx => $catBlock)
            @if($catBlock->latest_posts && $catBlock->latest_posts->count() > 0)
            <section class="{{ $idx === 0 ? 'bg-emerald-50/70 border-emerald-200' : 'bg-white border-slate-200' }} rounded-2xl border p-5 sm:p-6">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-3 mb-5">
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand"></span>
                        <a href="{{ route('category.show', $catBlock->slug) }}" class="hover:text-brand">{{ $catBlock->name }}</a>
                    </h2>
                    <a href="{{ route('category.show', $catBlock->slug) }}" class="px-3 py-1 rounded-full bg-white border border-slate-200 text-xs font-bold text-brand hover:bg-brand hover:text-white transition">
                        সব খবর →
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($catBlock->latest_posts->take(4) as $cPost)
                    <article class="bg-white rounded-xl border border-slate-200/80 overflow-hidden group flex flex-col justify-between shadow-2xs">
                        <div>
                            <a href="{{ route('post.show', $cPost->slug) }}" class="block overflow-hidden bg-slate-100 aspect-video">
                                @if($cPost->featured_image)
                                    <img src="{{ asset('storage/' . $cPost->featured_image) }}" alt="{{ $cPost->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @endif
                            </a>
                            <div class="p-3.5">
                                <h3 class="text-sm font-bold text-slate-900 group-hover:text-brand leading-snug line-clamp-2 mb-1.5">
                                    <a href="{{ route('post.show', $cPost->slug) }}">{{ $cPost->title }}</a>
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-2">
                                    {{ $cPost->excerpt ?: Str::limit(strip_tags($cPost->body), 80) }}
                                </p>
                            </div>
                        </div>
                        <div class="px-3.5 py-2 border-t border-slate-100 text-[11px] text-slate-400">
                            {{ bn_date($cPost->published_at) }}
                        </div>
                    </article>
                    @endforeach
                </div>
            </section>
            @endif
        @endforeach
    </div>
    @endif
</div>
@endsection
