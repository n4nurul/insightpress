@extends('themes.news-classic.layouts.app')

@section('meta_title', $post->meta_title ?: $post->title)
@section('meta_desc', $post->meta_description ?: Str::limit(strip_tags($post->body), 155))
@section('og_image', $post->featured_image ? asset('storage/' . $post->featured_image) : '')

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "{{ $post->title }}",
  "datePublished": "{{ $post->published_at?->toIso8601String() }}",
  "author": { "@type": "Person", "name": "{{ $post->meta_data['reporter'] ?? 'নিজস্ব প্রতিবেদক' }}" }
}
</script>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <article class="lg:col-span-8 bg-white p-5 md:p-8 rounded-xl border border-slate-200 shadow-sm">
        @if($post->category)
            <a href="{{ route('category.show', $post->category->slug) }}" class="text-xs font-bold uppercase px-2.5 py-1 bg-primary/10 text-primary rounded">
                {{ $post->category->name }}
            </a>
        @endif

        @if($post->sub_title)<p class="text-primary font-semibold mt-3">{{ $post->sub_title }}</p>@endif
        <h1 class="text-2xl md:text-3xl font-bold text-slate-900 mt-1 leading-snug">{{ $post->title }}</h1>

        <div class="flex flex-wrap items-center justify-between gap-3 py-3 my-4 border-y border-slate-100 text-xs text-slate-500">
            <div>
                <span>✍️ {{ $post->meta_data['reporter'] ?? 'নিজস্ব প্রতিবেদক' }}</span> |
                <span>🕒 {{ bn_datetime($post->published_at) }}</span> |
                <span>👁️ {{ $post->views_count }} বার পঠিত</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="px-3 py-1.5 bg-blue-600 text-white rounded font-semibold">Facebook শেয়ার</a>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank" class="px-3 py-1.5 bg-emerald-600 text-white rounded font-semibold">WhatsApp</a>
                <button onclick="downloadPhotocard()" class="px-3 py-1.5 bg-slate-900 text-white rounded font-semibold hover:bg-primary transition">
                    🖼️ ফটোকার্ড ডাউনলোড
                </button>
            </div>
        </div>

        @if($post->featured_image)
            <img id="newsImg" src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-xl mb-6 max-h-[460px] object-cover">
        @endif

        <style>
    .article-content ul { list-style-type: disc; padding-left: 1.5rem; margin: 1rem 0; }
    .article-content ol { list-style-type: decimal; padding-left: 1.5rem; margin: 1rem 0; }
    .article-content blockquote { border-left: 4px solid #dc2626; padding-left: 1rem; font-style: italic; color: #475569; margin: 1rem 0; }
    .article-content .ql-align-center { text-align: center; }
    .article-content .ql-align-right { text-align: right; }
    .article-content .ql-align-justify { text-align: justify; }
    .article-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; }
    .article-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.25rem 0 0.5rem; }
    .article-content h3 { font-size: 1.25rem; font-weight: 700; margin: 1rem 0 0.5rem; }
    .article-content a { color: #2563eb; text-decoration: underline; }
    </style>
<div class="article-content prose max-w-none text-slate-800 text-base md:text-lg leading-relaxed whitespace-pre-line">
            {{ $post->body }}
        </div>
    </article>

    <!-- Related Sidebar -->
    <aside class="lg:col-span-4 space-y-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <h3 class="font-bold text-base border-b-2 border-primary pb-2 mb-4">এ সম্পর্কিত আরও খবর</h3>
            <div class="space-y-3">
                @foreach($relatedPosts as $rel)
                    <a href="{{ route('post.show', $rel->slug) }}" class="flex gap-3 items-center group">
                        @if($rel->featured_image)
                            <img src="{{ asset('storage/' . $rel->featured_image) }}" class="w-20 h-14 object-cover rounded shrink-0">
                        @endif
                        <h4 class="text-sm font-semibold text-slate-800 group-hover:text-primary line-clamp-2">{{ $rel->title }}</h4>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>
</div>

<!-- Hidden Canvas for 1080x1080 Instant Photocard Generator -->
<canvas id="photocardCanvas" width="1080" height="1080" class="hidden"></canvas>
@endsection

@push('scripts')
<script>
function downloadPhotocard() {
    const canvas = document.getElementById('photocardCanvas');
    const ctx = canvas.getContext('2d');
    const siteName = @json(cms_setting('site_name', 'সিলেট প্রতিদিন ২৪'));
    const title = @json($post->title);
    const dateStr = @json(bn_datetime($post->published_at));
    const primary = @json(cms_setting('primary_color', '#dc2626'));

    ctx.fillStyle = '#0f172a';
    ctx.fillRect(0, 0, 1080, 1080);

    // Top Brand Bar
    ctx.fillStyle = primary;
    ctx.fillRect(0, 0, 1080, 110);
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 44px "Hind Siliguri", sans-serif';
    ctx.fillText(siteName, 50, 72);
    ctx.font = '26px "Hind Siliguri", sans-serif';
    ctx.fillText(dateStr, 760, 68);

    const drawTextAndSave = () => {
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 46px "Hind Siliguri", sans-serif';
        const words = title.split(' ');
        let line = '', y = 720;
        for (let n = 0; n < words.length; n++) {
            let testLine = line + words[n] + ' ';
            if (ctx.measureText(testLine).width > 960 && n > 0) {
                ctx.fillText(line, 60, y);
                line = words[n] + ' ';
                y += 62;
            } else { line = testLine; }
        }
        ctx.fillText(line, 60, y);

        ctx.fillStyle = primary;
        ctx.fillRect(0, 1010, 1080, 70);
        ctx.fillStyle = '#ffffff';
        ctx.font = '24px sans-serif';
        ctx.fillText('বিস্তারিত পড়ুন: ' + window.location.host, 60, 1054);

        const link = document.createElement('a');
        link.download = 'photocard-{{ $post->id }}.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    };

    const imgEl = document.getElementById('newsImg');
    if (imgEl && imgEl.src) {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => { ctx.drawImage(img, 40, 135, 1000, 510); drawTextAndSave(); };
        img.onerror = drawTextAndSave;
        img.src = imgEl.src;
    } else { drawTextAndSave(); }
}
</script>
@endpush
