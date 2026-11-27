@extends('themes.corporate-clean.layout')
@section('title', $post->meta_title ?: $post->title)

@section('content')
<div class="bg-slate-900 text-white py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold">{{ $post->title }}</h1>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-10 shadow-sm leading-relaxed text-slate-700">
        {!! $post->body !!}
    </div>
</div>
@endsection
