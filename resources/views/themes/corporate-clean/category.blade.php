@extends('themes.corporate-clean.layout')
@section('title', $category->meta_title ?: $category->name . ' — ' . cms_setting('site_name'))

@section('content')
<div class="bg-slate-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
        @if($category->meta_description)
            <p class="text-slate-300 text-sm mt-2 max-w-2xl">{{ $category->meta_description }}</p>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($posts as $post)
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
            @endif
            <div class="p-5">
                <h2 class="text-lg font-bold text-slate-900 mb-2">
                    <a href="{{ route('post.show', $post->slug) }}" class="hover:text-brand">{{ $post->title }}</a>
                </h2>
                <p class="text-sm text-slate-600 line-clamp-3 mb-4">{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 120) }}</p>
                <a href="{{ route('post.show', $post->slug) }}" class="text-sm font-semibold text-brand hover:underline">বিস্তারিত →</a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-slate-500">এই ক্যাটাগরিতে এখনো কোনো তথ্য যুক্ত করা হয়নি।</div>
        @endforelse
    </div>
    <div class="mt-8">{{ $posts->links() }}</div>
</div>
@endsection
