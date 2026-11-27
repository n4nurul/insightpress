@extends('themes.news-classic.layouts.app')
@section('meta_title', $category->meta_title ?: $category->name)

@section('content')
<div class="border-b-2 border-primary pb-2 mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ $category->name }}</h1>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($posts as $post)
        <a href="{{ route('post.show', $post->slug) }}" class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md transition">
            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-full h-48 object-cover">
            @endif
            <div class="p-4">
                <h2 class="font-bold text-lg text-slate-800 line-clamp-2">{{ $post->title }}</h2>
                <span class="text-xs text-slate-400 mt-2 block">{{ bn_date($post->published_at) }}</span>
            </div>
        </a>
    @endforeach
</div>
<div class="mt-6">{{ $posts->links() }}</div>
@endsection
