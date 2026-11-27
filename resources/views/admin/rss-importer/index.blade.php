@extends('layouts.admin')
@section('title', 'RSS & URL News Importer')

@section('content')
<div class="max-w-3xl space-y-6">
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <!-- Box 1: Direct Single Article URL Importer (Works without RSS!) -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold mb-2">
            No RSS Required
        </div>
        <h2 class="font-bold text-slate-900 text-lg mb-1">1. Direct News Article URL Importer</h2>
        <p class="text-xs text-slate-500 mb-5">যেকোনো পোর্টালের (যেমন SylhetView24, Prothom Alo ইত্যাদি) একটি নিউজ পেজের লিংক পেস্ট করুন—শিরোনাম, ছবি ও বিস্তারিত অটোমেটিক ইম্পোর্ট হয়ে যাবে।</p>

        <form action="{{ route('admin.rss-importer.import-url') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">News Article Page URL *</label>
                <input type="url" name="article_url" value="{{ old('article_url') }}" required
                       placeholder="https://sylhetview24.news/news/..."
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Post Status *</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        <option value="published" selected>Published (Direct Live)</option>
                        <option value="draft">Draft (Review Later)</option>
                    </select>
                </div>
            </div>

            <div class="pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="mark_featured" value="1" class="rounded border-slate-300 text-emerald-600">
                    Mark as Lead / Featured News (হোমপেজ লিড নিউজে দেখান)
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" onclick="this.innerText='Fetching Article & Image...'; this.classList.add('opacity-75');"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow cursor-pointer">
                    Import Single Article
                </button>
            </div>
        </form>
    </div>

    <!-- Box 2: Bulk RSS Feed Importer -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <h2 class="font-bold text-slate-900 text-lg mb-1">2. Bulk RSS Feed Importer (একসাথে ৫-২০টি নিউজ)</h2>
        <p class="text-xs text-slate-500 mb-5">যেসব পোর্টালে RSS Feed চালু আছে তাদের ফিড URL দিয়ে একসাথে একাধিক নিউজ ইম্পোর্ট করুন।</p>

        <form action="{{ route('admin.rss-importer.import') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">RSS Feed URL *</label>
                <input type="url" name="feed_url" id="feed_url" value="{{ old('feed_url') }}" required
                       placeholder="https://example.com/feed বা https://example.com/rss.xml"
                       class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Category *</label>
                    <select name="category_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Number of Posts *</label>
                    <select name="limit" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        <option value="3">3 Posts</option>
                        <option value="5" selected>5 Posts</option>
                        <option value="10">10 Posts</option>
                        <option value="15">15 Posts</option>
                        <option value="20">20 Posts</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Post Status *</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg">
                        <option value="published" selected>Published (Direct Live)</option>
                        <option value="draft">Draft (Review Later)</option>
                    </select>
                </div>
            </div>

            <div class="pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="mark_featured" value="1" class="rounded border-slate-300 text-emerald-600">
                    Mark imported posts as Lead / Featured News
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" onclick="this.innerText='Importing RSS Feed...'; this.classList.add('opacity-75');"
                        class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow cursor-pointer">
                    Import Bulk RSS
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
