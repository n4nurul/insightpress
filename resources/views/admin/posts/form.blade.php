@extends('layouts.admin')
@section('title', isset($post) ? 'কন্টেন্ট এডিট করুন' : 'নতুন কন্টেন্ট যুক্ত করুন (' . strtoupper($type) . ')')

@section('content')
<form action="{{ isset($post) ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data"
      x-data="rankMathSeo({
          title: @js(old('title', $post->title ?? '')),
          slug: @js(old('slug', $post->slug ?? '')),
          body: @js(old('body', $post->body ?? '')),
          keyword: @js(old('focus_keyword', $post->focus_keyword ?? '')),
          metaTitle: @js(old('meta_title', $post->meta_title ?? '')),
          metaDesc: @js(old('meta_description', $post->meta_description ?? '')),
          hasImage: {{ isset($post) && $post->featured_image ? 'true' : 'false' }}
      })">
    @csrf
    @if(isset($post)) @method('PUT') @endif
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="seo_score" :value="score">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Main Editor -->
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4">
                @if(in_array($type, ['news', 'service']))
                <div>
                    <label class="block text-sm font-semibold mb-1">উপ-শিরোনাম / Shoulder (ঐচ্ছিক)</label>
                    <input type="text" name="sub_title" value="{{ old('sub_title', $post->sub_title ?? '') }}" placeholder="যেমন: সিলেটে বন্যা পরিস্থিতি" class="w-full px-3.5 py-2 border rounded-lg text-sm">
                </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        {{ $type === 'team' ? 'নাম *' : ($type === 'ad' ? 'বিজ্ঞাপনের নাম *' : 'মূল শিরোনাম *') }}
                    </label>
                    <input type="text" name="title" x-model="title" required class="w-full px-3.5 py-2.5 border rounded-lg font-semibold">
                </div>

                @if(isset($post))
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">URL Slug</label>
                    <input type="text" name="slug" x-model="slug" class="w-full px-3 py-1.5 border rounded-lg text-xs bg-slate-50">
                </div>
                @endif

                @if($type === 'team')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">পদবি *</label>
                        <input type="text" name="meta_data[designation]" value="{{ old('meta_data.designation', $post->meta_data['designation'] ?? '') }}" placeholder="যেমন: সম্পাদক ও প্রকাশক" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">এলাকা / বিভাগ</label>
                        <input type="text" name="meta_data[area]" value="{{ old('meta_data.area', $post->meta_data['area'] ?? '') }}" placeholder="যেমন: বিশ্বনাথ প্রতিনিধি" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">মোবাইল নম্বর</label>
                        <input type="text" name="meta_data[phone]" value="{{ old('meta_data.phone', $post->meta_data['phone'] ?? '') }}" placeholder="017XXXXXXXX" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                @elseif($type === 'ad')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Ad Slot অবস্থান *</label>
                        <select name="meta_data[ad_slot]" class="w-full px-3 py-2 border rounded-lg text-sm">
                            @php $slot = $post->meta_data['ad_slot'] ?? ''; @endphp
                            <option value="header_top" {{ $slot === 'header_top' ? 'selected' : '' }}>Header Top Banner (728x90)</option>
                            <option value="sidebar_1" {{ $slot === 'sidebar_1' ? 'selected' : '' }}>Sidebar Top (300x250)</option>
                            <option value="inside_news" {{ $slot === 'inside_news' ? 'selected' : '' }}>Inside Single News</option>
                            <option value="home_middle" {{ $slot === 'home_middle' ? 'selected' : '' }}>Home Page Middle Banner</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">ব্যানার লিংক (URL)</label>
                        <input type="url" name="meta_data[ad_link]" value="{{ old('meta_data.ad_link', $post->meta_data['ad_link'] ?? '') }}" placeholder="https://..." class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">অথবা Google AdSense / HTML Script কোড</label>
                    <textarea name="body" rows="4" placeholder="<ins class='adsbygoogle'...>" class="w-full px-3 py-2 border rounded-lg text-xs font-mono">{{ old('body', $post->body ?? '') }}</textarea>
                </div>
                @else
                <div>
                    <label class="block text-sm font-semibold mb-1">সংক্ষিপ্ত বিবরণ (Excerpt)</label>
                    <textarea name="excerpt" rows="2" class="w-full px-3.5 py-2 border rounded-lg text-sm">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">বিস্তারিত বিবরণ (Body)</label>
                    <textarea name="body" x-model="body" rows="12" class="w-full px-3.5 py-2.5 border rounded-lg text-sm leading-relaxed">{{ old('body', $post->body ?? '') }}</textarea>
                </div>
                @endif
            </div>

            <!-- Rank Math Live SEO Box -->
            @if(!in_array($type, ['team', 'ad']))
            <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🚀</span>
                        <h3 class="font-bold text-slate-800">InsightPress Rank SEO Analyzer</h3>
                    </div>
                    <div class="px-3 py-1 rounded-full text-sm font-bold"
                         :class="score >= 70 ? 'bg-emerald-100 text-emerald-700' : (score >= 40 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')">
                        SEO Score: <span x-text="score"></span> / 100
                    </div>
                </div>

                <!-- Google SERP Live Preview -->
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                    <div class="text-xs text-slate-400 mb-1">Google Search Preview</div>
                    <div class="text-xs text-emerald-700 truncate">{{ url('/') }}/<span x-text="slug || 'post-url'"></span></div>
                    <div class="text-base font-semibold text-blue-700 truncate" x-text="metaTitle || title || 'এখানে শিরোনাম দেখাবে'"></div>
                    <div class="text-xs text-slate-600 line-clamp-2 mt-0.5" x-text="metaDesc || 'গুগল সার্চে এই আর্টিকেলের মেটা ডেসক্রিপশন এখানে প্রদর্শিত হবে...'"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block font-semibold mb-1">Focus Keyword (মূল কিওয়ার্ড)</label>
                        <input type="text" name="focus_keyword" x-model="keyword" placeholder="যেমন: সিলেটের খবর" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Custom SEO Title</label>
                        <input type="text" name="meta_title" x-model="metaTitle" placeholder="খালি রাখলে মূল শিরোনাম নেবে" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>

                <div class="text-sm">
                    <label class="block font-semibold mb-1">Meta Description (<span x-text="metaDesc.length"></span>/160 অক্ষর)</label>
                    <textarea name="meta_description" x-model="metaDesc" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>

                <!-- Live Checklist -->
                <ul class="text-xs space-y-1.5 pt-2 border-t text-slate-600">
                    <li :class="checks.keywordInTitle ? 'text-emerald-600 font-medium' : 'text-rose-500'">
                        <span x-text="checks.keywordInTitle ? '✔' : '✖'"></span> ফোকাস কিওয়ার্ডটি শিরোনামে ব্যবহৃত হয়েছে
                    </li>
                    <li :class="checks.keywordInDesc ? 'text-emerald-600 font-medium' : 'text-rose-500'">
                        <span x-text="checks.keywordInDesc ? '✔' : '✖'"></span> ফোকাস কিওয়ার্ডটি Meta Description-এ আছে
                    </li>
                    <li :class="checks.descLength ? 'text-emerald-600 font-medium' : 'text-amber-600'">
                        <span x-text="checks.descLength ? '✔' : '✖'"></span> Meta Description আদর্শ দৈর্ঘ্যের (৫০–১৬০ অক্ষর) মধ্যে আছে
                    </li>
                    <li :class="checks.wordCount ? 'text-emerald-600 font-medium' : 'text-amber-600'">
                        <span x-text="checks.wordCount ? '✔' : '✖'"></span> আর্টিকেলে পর্যাপ্ত শব্দ রয়েছে (কমপক্ষে ১৫০ শব্দ)
                    </li>
                    <li :class="hasImage ? 'text-emerald-600 font-medium' : 'text-rose-500'">
                        <span x-text="hasImage ? '✔' : '✖'"></span> ফিচার্ড ইমেজ যুক্ত করা হয়েছে (ফেসবুক ও গুগল প্রিভিউয়ের জন্য)
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <!-- Right Sidebar Options -->
        <div class="space-y-5">
            <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 text-sm">
                <h3 class="font-bold text-slate-800 border-b pb-2">পাবলিশ ও অপশন</h3>

                <div>
                    <label class="block font-semibold mb-1">স্ট্যাটাস</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg">
                        <option value="published" {{ ($post->status ?? '') === 'published' ? 'selected' : '' }}>Published (প্রকাশিত)</option>
                        <option value="draft" {{ ($post->status ?? '') === 'draft' ? 'selected' : '' }}>Draft (খসড়া)</option>
                    </select>
                </div>

                @if(in_array($type, ['news', 'service']))
                <div>
                    <label class="block font-semibold mb-1">ক্যাটাগরি / উপজেলা</label>
                    <select name="category_id" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">-- সিলেক্ট করুন --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($post->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($type === 'news')
                <div class="space-y-2 pt-2 border-t">
                    <label class="flex items-center gap-2 font-medium text-rose-600">
                        <input type="checkbox" name="is_breaking" value="1" {{ ($post->is_breaking ?? false) ? 'checked' : '' }} class="rounded"> ব্রেকিং নিউজ টিকারে দেখান
                    </label>
                    <label class="flex items-center gap-2 font-medium text-blue-600">
                        <input type="checkbox" name="is_featured" value="1" {{ ($post->is_featured ?? false) ? 'checked' : '' }} class="rounded"> লিড নিউজ (হোমপেজ টপ)
                    </label>
                </div>

                <div>
                    <label class="block font-semibold mb-1">প্রতিবেদকের নাম</label>
                    <input type="text" name="meta_data[reporter]" value="{{ old('meta_data.reporter', $post->meta_data['reporter'] ?? 'নিজস্ব প্রতিবেদক') }}" class="w-full px-3 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block font-semibold mb-1">YouTube / Facebook Video URL</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $post->video_url ?? '') }}" placeholder="https://youtube.com/..." class="w-full px-3 py-2 border rounded-lg">
                </div>
                @endif

                <div>
                    <label class="block font-semibold mb-1">ছবি আপলোড (Auto WebP)</label>
                    <input type="file" name="featured_image" @change="hasImage = true" accept="image/*" class="w-full text-xs border rounded-lg p-2">
                    @if(isset($post) && $post->featured_image)
                        <img src="{{ asset('storage/' . $post->featured_image) }}" class="mt-2 w-full h-36 object-cover rounded-lg border">
                    @endif
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition">
                    {{ isset($post) ? 'আপডেট করুন' : 'পাবলিশ করুন' }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
function rankMathSeo(initial) {
    return {
        title: initial.title,
        slug: initial.slug,
        body: initial.body,
        keyword: initial.keyword,
        metaTitle: initial.metaTitle,
        metaDesc: initial.metaDesc,
        hasImage: initial.hasImage,
        get checks() {
            const kw = this.keyword.trim().toLowerCase();
            const t = (this.metaTitle || this.title).toLowerCase();
            const d = this.metaDesc.toLowerCase();
            const words = this.body.trim().split(/\s+/).filter(Boolean).length;
            return {
                keywordInTitle: kw.length > 0 && t.includes(kw),
                keywordInDesc: kw.length > 0 && d.includes(kw),
                descLength: this.metaDesc.length >= 50 && this.metaDesc.length <= 160,
                wordCount: words >= 150,
            };
        },
        get score() {
            let s = 10;
            if (this.title.length >= 15) s += 15;
            if (this.checks.keywordInTitle) s += 25;
            if (this.checks.keywordInDesc) s += 15;
            if (this.checks.descLength) s += 10;
            if (this.checks.wordCount) s += 15;
            if (this.hasImage) s += 10;
            return Math.min(100, s);
        }
    }
}
</script>
@endsection
