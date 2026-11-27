@extends('layouts.admin')
@section('title', 'Dashboard Overview')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">Published Posts</div>
        <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_news'] }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">Pending Review (Drafts)</div>
        <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_drafts'] }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">Total Views</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_views']) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">Categories & Areas</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['total_categories'] }}</div>
    </div>
</div>

@if(auth()->user()->isEditor() && cms_addon('quick_breaking', false))
<!-- Quick Breaking News Ticker Control -->
<div class="bg-white p-5 rounded-xl border border-slate-200 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold text-slate-800 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-600 animate-ping"></span>
            Quick Breaking News Ticker (Instant Scroll)
        </h2>
        <span class="text-xs text-slate-400">Leave blank to hide custom ticker</span>
    </div>
    <form action="{{ route('admin.quick-breaking.save') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-sm">
        @csrf
        <div class="md:col-span-6">
            <input type="text" name="quick_breaking_text" value="{{ $quickBreaking }}" placeholder="Type urgent breaking news headline here..." class="w-full px-3.5 py-2 border rounded-lg">
        </div>
        <div class="md:col-span-4">
            <input type="url" name="quick_breaking_url" value="{{ $quickBreakingUrl }}" placeholder="Optional link URL (https://...)" class="w-full px-3.5 py-2 border rounded-lg">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg">
                Update Ticker
            </button>
        </div>
    </form>
</div>
@endif

<!-- Pending Drafts / Editorial Approval Queue -->
@if($pendingPosts->count() > 0)
<div class="bg-amber-50/60 rounded-xl border border-amber-200 overflow-hidden mb-6">
    <div class="px-5 py-3.5 border-b border-amber-200 flex items-center justify-between">
        <h2 class="font-bold text-amber-900">⏳ Pending Review / Drafts ({{ $pendingPosts->count() }})</h2>
    </div>
    <div class="overflow-x-auto bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-slate-600 border-b">
                <tr>
                    <th class="px-5 py-2.5">Title</th>
                    <th class="px-5 py-2.5">Author</th>
                    <th class="px-5 py-2.5">Category</th>
                    <th class="px-5 py-2.5 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($pendingPosts as $draft)
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $draft->title }}</td>
                    <td class="px-5 py-3 text-xs text-slate-500">{{ $draft->author?->name ?? 'Reporter' }}</td>
                    <td class="px-5 py-3">{{ $draft->category?->name ?? '-' }}</td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <a href="{{ route('admin.posts.edit', $draft) }}" class="text-blue-600 hover:underline font-medium">Review</a>
                        @if(auth()->user()->isEditor() && cms_addon('quick_breaking', false))
                        <form action="{{ route('admin.posts.approve', $draft) }}" method="POST" class="inline">
                            @csrf
                            <button class="px-2.5 py-1 bg-emerald-600 text-white text-xs rounded font-semibold hover:bg-emerald-700">
                                ✔ Approve & Publish
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Recent Published Content -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-bold text-slate-800">Recent Published Content</h2>
        <a href="{{ route('admin.posts.create', ['type' => 'news']) }}" class="px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg font-medium">+ Write News</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-slate-600 border-b">
                <tr>
                    <th class="px-5 py-3">Title</th>
                    <th class="px-5 py-3">Category</th>
                    <th class="px-5 py-3">SEO Score</th>
                    <th class="px-5 py-3">Views</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentPosts as $post)
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $post->title }}</td>
                    <td class="px-5 py-3">{{ $post->category?->name ?? '-' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $post->seo_score >= 70 ? 'bg-emerald-100 text-emerald-700' : ($post->seo_score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                            {{ $post->seo_score }}/100
                        </span>
                    </td>
                    <td class="px-5 py-3">{{ $post->views_count }}</td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <a href="{{ route('post.show', $post->slug) }}" target="_blank" class="text-slate-500 hover:text-slate-800 text-xs">View ↗</a>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:underline font-medium">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-slate-400">No posts published yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
