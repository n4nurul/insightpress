@extends('themes.news-classic.layouts.app')
@section('meta_title', 'সম্পাদকীয় পরিষদ ও প্রতিনিধি তালিকা')

@section('content')
<div class="border-b-2 border-primary pb-2 mb-6">
    <h1 class="text-2xl font-bold text-slate-900">সম্পাদকীয় পরিষদ ও প্রতিনিধি তালিকা</h1>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
    @foreach($members as $member)
        <div class="bg-white p-5 rounded-xl border border-slate-200 text-center shadow-sm">
            @if($member->featured_image)
                <img src="{{ asset('storage/' . $member->featured_image) }}" class="w-24 h-24 rounded-full mx-auto object-cover border-2 border-primary">
            @else
                <div class="w-24 h-24 rounded-full mx-auto bg-slate-200 flex items-center justify-center text-2xl font-bold text-slate-500">👤</div>
            @endif
            <h2 class="font-bold text-lg text-slate-900 mt-3">{{ $member->title }}</h2>
            <p class="text-sm font-semibold text-primary">{{ $member->meta_data['designation'] ?? '' }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $member->meta_data['area'] ?? '' }}</p>
            @if(!empty($member->meta_data['phone']))
                <p class="text-xs font-mono bg-slate-100 py-1 px-2 rounded mt-2 inline-block">📞 {{ $member->meta_data['phone'] }}</p>
            @endif
        </div>
    @endforeach
</div>
@endsection
