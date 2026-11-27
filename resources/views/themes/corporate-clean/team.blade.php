@extends('themes.corporate-clean.layout')
@section('title', 'আমাদের টিম — ' . cms_setting('site_name'))

@section('content')
<div class="bg-slate-900 text-white py-12 text-center">
    <h1 class="text-3xl font-bold">আমাদের নেতৃত্ব ও টিম</h1>
    <p class="text-slate-300 text-sm mt-2">দক্ষ পেশাজীবীদের সমন্বয়ে গঠিত আমাদের কর্মপরিবার</p>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($members as $member)
        <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
            @if($member->featured_image)
                <img src="{{ asset('storage/' . $member->featured_image) }}" alt="{{ $member->title }}" class="w-28 h-28 rounded-full object-cover mx-auto mb-4 border-2 border-brand">
            @else
                <div class="w-28 h-28 rounded-full bg-slate-100 mx-auto mb-4 flex items-center justify-center text-3xl font-bold text-slate-500">
                    {{ mb_substr($member->title, 0, 1) }}
                </div>
            @endif
            <h2 class="text-lg font-bold text-slate-900">{{ $member->title }}</h2>
            <p class="text-xs font-semibold text-brand mt-1">{{ $member->meta_data['designation'] ?? '' }}</p>
            @if(!empty($member->meta_data['area']))
                <p class="text-xs text-slate-500 mt-1">{{ $member->meta_data['area'] }}</p>
            @endif
            @if(!empty($member->meta_data['phone']))
                <p class="text-xs text-slate-600 mt-2 font-medium">📞 {{ $member->meta_data['phone'] }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
