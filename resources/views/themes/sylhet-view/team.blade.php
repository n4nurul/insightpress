@extends('themes.sylhet-view.layouts.app')
@section('title', 'আমাদের টিম ও প্রতিনিধি — ' . cms_setting('site_name'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 text-center">
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">সম্পাদকীয় ও প্রতিনিধি পরিষদ</h1>
        <p class="text-sm text-slate-500 mt-1">{{ cms_setting('site_name') }}</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($members as $member)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center shadow-xs">
            @if($member->featured_image)
                <img src="{{ asset('storage/' . $member->featured_image) }}" alt="{{ $member->title }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-3 border-2 border-brand">
            @else
                <div class="w-24 h-24 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    {{ mb_substr($member->title, 0, 1) }}
                </div>
            @endif
            <h2 class="font-bold text-slate-900 text-base">{{ $member->title }}</h2>
            <div class="text-xs font-semibold text-brand mt-0.5">{{ $member->meta_data['designation'] ?? 'সংবাদদাতা' }}</div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center text-slate-500">
            টিম সদস্যদের তথ্য এখনো যুক্ত করা হয়নি।
        </div>
        @endforelse
    </div>
</div>
@endsection
