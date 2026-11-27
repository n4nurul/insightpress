@extends('themes.news-classic.layouts.app')

@section('content')
<!-- Breaking News Ticker -->
@if($breakingNews->count())
<div class="bg-white border border-slate-200 rounded-lg mb-6 flex items-center overflow-hidden shadow-sm">
    <span class="bg-primary text-white text-xs font-bold px-3.5 py-2.5 whitespace-nowrap">সদ্যপ্রাপ্ত</span>
    <marquee onmouseover="this.stop();" onmouseout="this.start();" class="text-sm font-semibold text-slate-800 px-3">
        @foreach($breakingNews as $bNews)
            <a href="{{ route('post.show', $bNews->slug) }}" class="hover:text-primary mr-8">● {{ $bNews->title }}</a>
        @endforeach
    </marquee>
</div>
@endif

<!-- Lead News & Latest Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
    <!-- Main Lead -->
    <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        @if($mainLead = $leadNews->first() ?? $latestNews->first())
            <a href="{{ route('post.show', $mainLead->slug) }}" class="block group">
                <div class="aspect-video bg-slate-200 overflow-hidden">
                    @if($mainLead->featured_image)
                        <img src="{{ asset('storage/' . $mainLead->featured_image) }}" alt="{{ $mainLead->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @endif
                </div>
                <div class="p-4">
                    @if($mainLead->sub_title)<span class="text-xs font-bold text-primary">{{ $mainLead->sub_title }}</span>@endif
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 group-hover:text-primary mt-1 leading-snug">{{ $mainLead->title }}</h1>
                    <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $mainLead->excerpt ?? Str::limit(strip_tags($mainLead->body), 140) }}</p>
                </div>
            </a>
        @endif
    </div>

    <!-- Sub Leads -->
    <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
        @foreach($leadNews->skip(1)->take(4) as $subLead)
            <a href="{{ route('post.show', $subLead->slug) }}" class="bg-white p-3 rounded-xl border border-slate-200 flex gap-3 hover:border-primary transition">
                @if($subLead->featured_image)
                    <img src="{{ asset('storage/' . $subLead->featured_image) }}" class="w-24 h-20 object-cover rounded-lg shrink-0">
                @endif
                <div>
                    <h2 class="text-sm font-bold text-slate-800 line-clamp-2 leading-snug">{{ $subLead->title }}</h2>
                    <span class="text-[11px] text-slate-400 mt-1 block">{{ bn_date($subLead->published_at) }}</span>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Latest & Popular Sidebar -->
    <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 p-4 h-fit">
        <h3 class="font-bold text-base border-b-2 border-primary pb-2 mb-3 text-slate-800">সর্বশেষ সংবাদ</h3>
        <div class="space-y-3 divide-y divide-slate-100">
            @foreach($latestNews->take(6) as $item)
                <a href="{{ route('post.show', $item->slug) }}" class="block pt-2 first:pt-0 text-sm font-medium text-slate-800 hover:text-primary leading-snug">
                    ▸ {{ $item->title }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<!-- Category Blocks (উপজেলা ও বিভাগীয় সংবাদ) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @foreach($categoryBlocks as $catBlock)
        @if($catBlock->latest_posts->count())
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between border-b-2 border-primary pb-2 mb-4">
                <h2 class="text-lg font-bold text-slate-900">{{ $catBlock->name }}</h2>
                <a href="{{ route('category.show', $catBlock->slug) }}" class="text-xs font-semibold text-primary hover:underline">সব খবর →</a>
            </div>
            <div class="space-y-3">
                @foreach($catBlock->latest_posts as $cPost)
                    <a href="{{ route('post.show', $cPost->slug) }}" class="flex gap-3 items-center group">
                        @if($cPost->featured_image)
                            <img src="{{ asset('storage/' . $cPost->featured_image) }}" class="w-20 h-14 object-cover rounded shrink-0">
                        @endif
                        <h3 class="text-sm font-semibold text-slate-800 group-hover:text-primary line-clamp-2">{{ $cPost->title }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    @endforeach
</div>
@endsection
