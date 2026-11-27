@extends('themes.news-classic.layouts.app')
@section('title', $post->title . ' — ' . cms_setting('site_name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-10 shadow-xs">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 border-b pb-4 mb-6">{{ $post->title }}</h1>
        <div class="prose max-w-none text-slate-800 leading-relaxed space-y-4">
            {!! $post->body !!}
        </div>
    </div>
</div>
@endsection
