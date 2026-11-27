@extends('layouts.admin')
@section('title', 'ড্যাশবোর্ড ওভারভিউ')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">মোট সংবাদ / পোস্ট</div>
        <div class="text-2xl font-bold text-slate-800 mt-1">{{ $stats['total_news'] }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">মোট পাঠক ভিউ</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_views']) }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">ক্যাটাগরি ও উপজেলা</div>
        <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['total_categories'] }}</div>
    </div>
    <div class="bg-white p-5 rounded-xl border border-slate-200">
        <div class="text-sm text-slate-500">প্রতিনিধি / টিম</div>
        <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['total_team'] }}</div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
        <h2 class="font-bold text-slate-800">সাম্প্রতিক প্রকাশিত কন্টেন্ট</h2>
        <a href="{{ route('admin.posts.create', ['type' => 'news']) }}" class="px-3 py-1.5 bg-emerald-600 text-white text-sm rounded-lg font-medium">+ নতুন যোগ করুন</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-slate-600 border-b">
                <tr>
                    <th class="px-5 py-3">শিরোনাম</th>
                    <th class="px-5 py-3">ক্যাটাগরি</th>
                    <th class="px-5 py-3">SEO স্কোর</th>
                    <th class="px-5 py-3">ভিউ</th>
                    <th class="px-5 py-3 text-right">অ্যাকশন</th>
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
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:underline font-medium">এডিট</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-slate-400">এখনো কোনো পোস্ট যুক্ত করা হয়নি।</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
