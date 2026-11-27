@extends('layouts.admin')
@section('title', 'কন্টেন্ট তালিকা (' . strtoupper($type) . ')')

@section('content')
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="p-4 border-b flex flex-wrap items-center justify-between gap-3">
        <form class="flex gap-2">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="শিরোনাম দিয়ে খুঁজুন..." class="px-3 py-1.5 border rounded-lg text-sm">
            <button class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-sm">খুঁজুন</button>
        </form>
        <a href="{{ route('admin.posts.create', ['type' => $type]) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold">+ নতুন যোগ করুন</a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 border-b text-slate-600">
                <tr>
                    <th class="px-5 py-3">ছবি</th>
                    <th class="px-5 py-3">শিরোনাম / নাম</th>
                    <th class="px-5 py-3">ক্যাটাগরি / তথ্য</th>
                    <th class="px-5 py-3">SEO স্কোর</th>
                    <th class="px-5 py-3">স্ট্যাটাস</th>
                    <th class="px-5 py-3 text-right">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($posts as $post)
                <tr>
                    <td class="px-5 py-3">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-12 h-9 object-cover rounded">
                        @else
                            <div class="w-12 h-9 bg-slate-200 rounded flex items-center justify-center text-xs text-slate-400">N/A</div>
                        @endif
                    </td>
                    <td class="px-5 py-3 font-semibold text-slate-800">
                        {{ $post->title }}
                        @if($post->is_breaking) <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-rose-100 text-rose-700 rounded">Breaking</span> @endif
                        @if($post->is_featured) <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-blue-100 text-blue-700 rounded">Lead</span> @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">
                        {{ $post->category?->name ?? ($post->meta_data['designation'] ?? ($post->meta_data['ad_slot'] ?? '-')) }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $post->seo_score >= 70 ? 'bg-emerald-100 text-emerald-700' : ($post->seo_score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                            {{ $post->seo_score }}/100
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $post->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right space-x-2">
                        <a href="{{ route('admin.posts.edit', $post) }}" class="text-blue-600 hover:underline font-medium">এডিট</a>
                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline" onsubmit="return confirm('নিশ্চিত ডিলিট করবেন?')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 hover:underline font-medium">ডিলিট</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">কোনো ডাটা পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $posts->links() }}</div>
</div>
@endsection
