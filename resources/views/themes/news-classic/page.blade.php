@extends('themes.news-classic.layouts.app')
@section('meta_title', $post->meta_title ?: $post->title)

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 md:p-10 rounded-xl border border-slate-200">
    <h1 class="text-2xl md:text-3xl font-bold text-slate-900 border-b pb-4 mb-6">{{ $post->title }}</h1>
    <div class="prose max-w-none text-slate-700 leading-relaxed whitespace-pre-line">{{ $post->body }}</div>
</div>
@endsection
