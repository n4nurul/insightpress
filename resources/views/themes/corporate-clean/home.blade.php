@extends('themes.corporate-clean.layout')
@section('title', cms_setting('site_name', 'Corporate Home') . ' — ' . cms_setting('site_tagline', ''))

@section('content')
@php
    $services = \App\Models\Post::published()->where('type', 'service')->latest('published_at')->take(6)->get();
    $blogs = \App\Models\Post::published()->where('type', 'news')->latest('published_at')->take(3)->get();
    $team = \App\Models\Post::published()->where('type', 'team')->orderBy('id')->take(4)->get();
    $phone = cms_setting('contact_phone', '+8801700000000');
    $waNumber = preg_replace('/[^0-9]/', '', $phone);
@endphp

<!-- Hero Section -->
<section class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white py-20 lg:py-28">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl">
        <span class="inline-block px-3.5 py-1 rounded-full text-xs font-semibold bg-white/10 text-emerald-300 mb-4">
            বিশ্বস্ত কর্পোরেট ও ডিজিটাল সলিউশন
        </span>
        <h1 class="text-3xl sm:text-5xl font-bold leading-tight mb-5">
            {{ cms_setting('site_name', 'আপনার ব্যবসার নির্ভরযোগ্য সহযোগী') }}
        </h1>
        <p class="text-base sm:text-lg text-slate-300 mb-8 leading-relaxed">
            {{ cms_setting('site_tagline', 'আমরা আধুনিক মানসম্মত সেবা, দ্রুত ডেলিভারি এবং দীর্ঘমেয়াদী সাপোর্টের মাধ্যমে আপনার প্রতিষ্ঠানের প্রবৃদ্ধি নিশ্চিত করি।') }}
        </p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="#services" class="px-6 py-3 rounded-xl bg-brand text-white font-semibold shadow-lg hover:opacity-90 transition">
                আমাদের সেবাসমূহ দেখুন
            </a>
            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="px-6 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold border border-white/20 transition">
                সরাসরি পরামর্শ নিন ↗
            </a>
        </div>
    </div>
</section>

<!-- Services & Projects Section -->
<section id="services" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">আমাদের সেবাসমূহ ও প্রজেক্ট</h2>
        <p class="text-slate-600 mt-2 text-sm sm:text-base">ক্লায়েন্টদের চাহিদা অনুযায়ী কাস্টমাইজড ও আধুনিক সমাধান</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
            @if($service->featured_image)
                <img src="{{ asset('storage/' . $service->featured_image) }}" alt="{{ $service->title }}" class="w-full h-48 object-cover">
            @else
                <div class="w-full h-48 bg-slate-100 flex items-center justify-center text-3xl text-brand">💼</div>
            @endif
            <div class="p-6 flex-1 flex flex-col">
                @if($service->category)
                    <span class="text-xs font-semibold text-brand uppercase mb-1">{{ $service->category->name }}</span>
                @endif
                <h3 class="text-lg font-bold text-slate-900 mb-2">
                    <a href="{{ route('post.show', $service->slug) }}" class="hover:text-brand">{{ $service->title }}</a>
                </h3>
                <p class="text-sm text-slate-600 line-clamp-3 mb-4 flex-1">
                    {{ $service->excerpt ?: Str::limit(strip_tags($service->body), 120) }}
                </p>
                <a href="{{ route('post.show', $service->slug) }}" class="text-sm font-semibold text-brand hover:underline">
                    বিস্তারিত জানুন →
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300 text-slate-500">
            অ্যাডমিন প্যানেলের <strong>Services & Projects</strong> থেকে আপনার সেবাগুলো যুক্ত করুন।
        </div>
        @endforelse
    </div>
</section>

<!-- Team Preview -->
@if($team->count() > 0)
<section class="bg-white border-y border-slate-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900">আমাদের টিম</h2>
                <p class="text-slate-600 text-sm mt-1">অভিজ্ঞ ও দক্ষ টিম মেম্বারদের পরিচালনায় আমাদের কার্যক্রম</p>
            </div>
            <a href="{{ route('team.index') }}" class="text-sm font-semibold text-brand hover:underline">সবাইকে দেখুন →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($team as $member)
            <div class="bg-slate-50 rounded-2xl p-5 text-center border border-slate-200">
                @if($member->featured_image)
                    <img src="{{ asset('storage/' . $member->featured_image) }}" alt="{{ $member->title }}" class="w-24 h-24 rounded-full object-cover mx-auto mb-4 border-2 border-brand">
                @else
                    <div class="w-24 h-24 rounded-full bg-slate-200 mx-auto mb-4 flex items-center justify-center text-2xl font-bold text-slate-600">
                        {{ mb_substr($member->title, 0, 1) }}
                    </div>
                @endif
                <h3 class="font-bold text-slate-900">{{ $member->title }}</h3>
                <p class="text-xs font-semibold text-brand mt-0.5">{{ $member->meta_data['designation'] ?? '' }}</p>
                @if(!empty($member->meta_data['area']))
                    <p class="text-xs text-slate-500 mt-1">{{ $member->meta_data['area'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Latest Blog & Updates -->
@if($blogs->count() > 0)
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-8">সাম্প্রতিক ব্লগ ও আপডেট</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($blogs as $blog)
        <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            @if($blog->featured_image)
                <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->title }}" class="w-full h-44 object-cover">
            @endif
            <div class="p-5">
                <div class="text-xs text-slate-400 mb-1">{{ bn_date($blog->published_at) }}</div>
                <h3 class="font-bold text-slate-900 mb-2">
                    <a href="{{ route('post.show', $blog->slug) }}" class="hover:text-brand">{{ $blog->title }}</a>
                </h3>
                <p class="text-sm text-slate-600 line-clamp-2">{{ $blog->excerpt ?: Str::limit(strip_tags($blog->body), 100) }}</p>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif

<!-- CTA Banner -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-brand rounded-3xl p-8 sm:p-12 text-white flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold">আপনার প্রজেক্ট বা সেবা সম্পর্কে কথা বলতে চান?</h2>
            <p class="text-white/90 text-sm sm:text-base mt-1">আমাদের টিমের সাথে সরাসরি যোগাযোগ করুন এবং দ্রুত সমাধান পান।</p>
        </div>
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="px-6 py-3.5 bg-white text-slate-900 font-bold rounded-xl shadow hover:bg-slate-100 whitespace-nowrap">
            WhatsApp মেসেজ পাঠান
        </a>
    </div>
</section>
@endsection
