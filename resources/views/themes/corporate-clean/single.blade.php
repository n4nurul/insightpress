@extends('themes.corporate-clean.layout')
@section('title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: $post->excerpt)

@section('content')
<style>
    .article-content ul { list-style-type: disc; padding-left: 1.5rem; margin: 1rem 0; }
    .article-content ol { list-style-type: decimal; padding-left: 1.5rem; margin: 1rem 0; }
    .article-content blockquote { border-left: 4px solid var(--brand); padding-left: 1rem; font-style: italic; color: #475569; margin: 1rem 0; }
    .article-content .ql-align-center { text-align: center; }
    .article-content .ql-align-right { text-align: right; }
    .article-content .ql-align-justify { text-align: justify; }
    .article-content img { max-width: 100%; height: auto; border-radius: 0.75rem; margin: 1.25rem 0; }
    .article-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.25rem 0 0.5rem; }
    .article-content h3 { font-size: 1.25rem; font-weight: 700; margin: 1rem 0 0.5rem; }
    .article-content a { color: #2563eb; text-decoration: underline; }
</style>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-10 shadow-sm">
        @if($post->category)
            <a href="{{ route('category.show', $post->category->slug) }}" class="text-xs font-bold uppercase text-brand">
                {{ $post->category->name }}
            </a>
        @endif
        <h1 class="text-2xl sm:text-4xl font-bold text-slate-900 mt-2 mb-4 leading-snug">{{ $post->title }}</h1>
        <div class="text-xs text-slate-400 mb-6 pb-4 border-b">
            প্রকাশিত: {{ bn_datetime($post->published_at) }}
        </div>

        @if($post->featured_image)
            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full max-h-[450px] object-cover rounded-xl mb-8">
        @endif

        <div class="article-content text-slate-700 leading-relaxed space-y-4">
            {!! $post->body !!}
        </div>
    </div>
</div>
@endsection
