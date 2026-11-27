@extends('themes.sylhet-view.layouts.app')
@section('title', $category->name . ' — ' . cms_setting('site_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mb-6 flex items-center justify-between">
        <div>
            <div class="text-xs text-slate-400 mb-1"><a href="{{ route('home') }}" class="hover:text-brand">হোম</a> / বিভাগ</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 flex items-center gap-2.5">
                <span class="w-2 h-7 bg-brand rounded-full"></span>
                {{ $category->name }}
            </h1>
        </div>
        <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
            মোট {{ bn_number($posts->total()) }}টি সংবাদ
        </span>
    </div>

    @if($posts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($posts as $post)
            <article class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs group flex flex-col justify-between">
                <div>
                    <a href="{{ route('post.show', $post->slug) }}" class="block overflow-hidden bg-slate-100 aspect-video">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-slate-400">No Image</div>
                        @endif
                    </a>
                    <div class="p-4">
                        <h2 class="font-bold text-slate-900 group-hover:text-brand leading-snug line-clamp-2 mb-2">
                            <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <p class="text-xs text-slate-600 line-clamp-2">
                            {{ $post->excerpt ?: Str::limit(strip_tags($post->body), 95) }}
                        </p>
                    </div>
                </div>
                <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-400">
                    🕒 {{ bn_date($post->published_at) }}
                </div>
            </article>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
            এই বিভাগে এখনো কোনো সংবাদ প্রকাশিত হয়নি।
        </div>
    @endif
</div>
@endsection
