@extends('themes.sylhet-view.layouts.app')
@section('title', ($post->meta_title ?: $post->title) . ' — ' . cms_setting('site_name'))
@section('meta_description', $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->body), 155)))

@push('meta')
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $post->title }}">
    <meta property="og:description" content="{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 155) }}">
    @if($post->featured_image)
        <meta property="og:image" content="{{ asset('storage/' . $post->featured_image) }}">
    @endif
    @if(cms_addon('schema_seo', true))
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "headline": {!! json_encode($post->title, JSON_UNESCAPED_UNICODE) !!},
        "description": {!! json_encode($post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->body), 155)), JSON_UNESCAPED_UNICODE) !!},
        "image": [
            "{{ $post->featured_image ? asset('storage/' . $post->featured_image) : (cms_setting('logo') ? asset('storage/' . cms_setting('logo')) : url('/')) }}"
        ],
        "datePublished": "{{ $post->published_at?->toIso8601String() }}",
        "dateModified": "{{ $post->updated_at?->toIso8601String() }}",
        "author": {
            "@type": "Person",
            "name": {!! json_encode($post->author?->name ?? cms_setting('site_name', 'Editorial Desk'), JSON_UNESCAPED_UNICODE) !!}
        },
        "publisher": {
            "@type": "Organization",
            "name": {!! json_encode(cms_setting('site_name', 'InsightPress News'), JSON_UNESCAPED_UNICODE) !!},
            "logo": {
                "@type": "ImageObject",
                "url": "{{ cms_setting('logo') ? asset('storage/' . cms_setting('logo')) : url('/') }}"
            }
        }
    }
    </script>
    @endif
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ fontSize: 18, copied: false }">
    <!-- Breadcrumb -->
    <nav class="text-xs text-slate-500 mb-4 flex flex-wrap items-center gap-1.5">
        <a href="{{ route('home') }}" class="hover:text-brand">হোম</a>
        <span>/</span>
        @if($post->category)
            <a href="{{ route('category.show', $post->category->slug) }}" class="text-brand font-bold hover:underline">
                {{ $post->category->name }}
            </a>
            <span>/</span>
        @endif
        <span class="text-slate-700 truncate max-w-xs sm:max-w-md">{{ $post->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Article Column (8 Columns) -->
        <article class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-5 sm:p-8 shadow-xs">
            @if($post->category)
                <a href="{{ route('category.show', $post->category->slug) }}" class="inline-block px-3 py-1 rounded-md bg-red-50 text-brand text-xs font-bold mb-3">
                    {{ $post->category->name }}
                </a>
            @endif

            @if($post->sub_title)
                <div class="text-sm sm:text-base font-semibold text-slate-500 mb-1.5">{{ $post->sub_title }}</div>
            @endif

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 leading-snug mb-4">
                {{ $post->title }}
            </h1>

            <!-- Author, Publish Date & Reader Controls (Font Size + Share) -->
            <div class="flex flex-wrap items-center justify-between gap-3 py-3.5 border-y border-slate-200 mb-6 text-xs text-slate-600">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center">
                        ✍️
                    </div>
                    <div>
                        <div class="font-bold text-slate-900">{{ $post->author?->name ?? 'নিজস্ব প্রতিবেদক' }}</div>
                        <div class="text-slate-500">প্রকাশিত: {{ bn_datetime($post->published_at) }}</div>
                    </div>
                </div>

                <!-- Font Size & Share Buttons -->
                <div class="flex items-center gap-1.5">
                    <button type="button" @click="fontSize = Math.max(15, fontSize - 2)" class="px-2.5 py-1.5 rounded border border-slate-200 hover:bg-slate-100 font-bold" title="ফন্ট ছোট করুন">A-</button>
                    <button type="button" @click="fontSize = Math.min(26, fontSize + 2)" class="px-2.5 py-1.5 rounded border border-slate-200 hover:bg-slate-100 font-bold" title="ফন্ট বড় করুন">A+</button>

                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"
                       class="px-3 py-1.5 rounded bg-[#1877F2] text-white font-semibold hover:opacity-90">
                        Facebook
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank"
                       class="px-3 py-1.5 rounded bg-[#25D366] text-white font-semibold hover:opacity-90">
                        WhatsApp
                    </a>
                    <button type="button" @click="navigator.clipboard.writeText('{{ url()->current() }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="px-2.5 py-1.5 rounded border border-slate-200 hover:bg-slate-100 font-semibold">
                        <span x-text="copied ? '✓ কপি হয়েছে' : '🔗 কপি'"></span>
                    </button>
                </div>
            </div>

            <!-- Featured Image -->
            @if($post->featured_image)
                <figure class="mb-6">
                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-xl object-cover max-h-[480px]">
                </figure>
            @endif

            <!-- Optional Video Embed -->
            @if($post->video_url)
                <div class="mb-6 aspect-video rounded-xl overflow-hidden bg-slate-900">
                    <iframe src="{{ str_replace('watch?v=', 'embed/', $post->video_url) }}" class="w-full h-full" allowfullscreen></iframe>
                </div>
            @endif

            <!-- Inside Article Ad Slot -->
            @if(cms_addon('ad_slots', true) && !empty($ads['inside-news']))
                <div class="my-5 p-3 bg-slate-50 rounded-xl border border-slate-200 text-center overflow-hidden">
                    {!! $ads['inside-news']['body'] ?? '' !!}
                </div>
            @endif

            <!-- Article Body -->
            <div class="article-content prose max-w-none text-slate-800 leading-relaxed space-y-4" :style="'font-size:' + fontSize + 'px'">
                {!! $post->body !!}
            </div>

            <!-- Related News Section -->
            @if($relatedPosts->count() > 0)
            <div class="mt-10 pt-6 border-t-2 border-slate-100">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-brand rounded-full"></span>
                    এ সম্পর্কিত আরও খবর
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($relatedPosts as $rel)
                    <a href="{{ route('post.show', $rel->slug) }}" class="flex gap-3 items-start p-2.5 rounded-xl border border-slate-100 hover:border-slate-300 hover:bg-slate-50 transition group">
                        <div class="w-24 h-16 rounded-lg overflow-hidden bg-slate-100 shrink-0">
                            @if($rel->featured_image)
                                <img src="{{ asset('storage/' . $rel->featured_image) }}" alt="{{ $rel->title }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">{{ $rel->title }}</h4>
                            <div class="text-[11px] text-slate-400 mt-1">{{ bn_date($rel->published_at) }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </article>

        <!-- Right Sidebar (4 Columns) -->
        <aside class="lg:col-span-4 space-y-6">
            @if(cms_addon('ad_slots', true) && !empty($ads['sidebar-top']))
                <div class="bg-white p-3 rounded-xl border border-slate-200 text-center overflow-hidden">
                    {!! $ads['sidebar-top']['body'] ?? '' !!}
                </div>
            @endif

            @php
                $latestSidebar = \App\Models\Post::published()->where('type', 'news')->where('id', '!=', $post->id)->latest('published_at')->take(8)->get();
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="bg-slate-900 text-white px-4 py-3 font-bold text-sm flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    সর্বশেষ সংবাদ
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($latestSidebar as $idx => $sItem)
                    <a href="{{ route('post.show', $sItem->slug) }}" class="p-3.5 flex items-start gap-3 hover:bg-slate-50 transition group">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 group-hover:bg-brand group-hover:text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                            {{ bn_number($idx + 1) }}
                        </span>
                        <div class="text-sm font-semibold text-slate-800 group-hover:text-brand leading-snug line-clamp-2">
                            {{ $sItem->title }}
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
