@extends('layouts.admin')
@section('title', isset($post) ? 'Edit Content' : 'Create New (' . strtoupper($type) . ')')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-color: #cbd5e1;
        min-height: 320px;
        font-size: 15px;
        line-height: 1.75;
    }
    .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 0.75rem 0;
    }
</style>

@php
    $pubDateTime = isset($post) && $post->published_at ? $post->published_at : now();
@endphp

<form action="{{ isset($post) ? route('admin.posts.update', $post) : route('admin.posts.store') }}" method="POST" enctype="multipart/form-data"
      x-data="rankMathSeo({
          title: @js(old('title', $post->title ?? '')),
          slug: @js(old('slug', $post->slug ?? '')),
          body: @js(old('body', $post->body ?? '')),
          keyword: @js(old('focus_keyword', $post->focus_keyword ?? '')),
          metaTitle: @js(old('meta_title', $post->meta_title ?? '')),
          metaDesc: @js(old('meta_description', $post->meta_description ?? '')),
          hasImage: {{ isset($post) && $post->featured_image ? 'true' : 'false' }},
          pubDate: @js(old('publish_date', $pubDateTime->format('Y-m-d'))),
          pubTime: @js(old('publish_time', $pubDateTime->format('H:i:s')))
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
                    <label class="block text-sm font-semibold mb-1">Sub-title / Shoulder (Optional)</label>
                    <input type="text" name="sub_title" value="{{ old('sub_title', $post->sub_title ?? '') }}" placeholder="e.g. Special Report" class="w-full px-3.5 py-2 border rounded-lg text-sm">
                </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold mb-1">
                        {{ $type === 'team' ? 'Full Name *' : ($type === 'ad' ? 'Ad Title *' : 'Title *') }}
                    </label>
                    <input type="text" name="title" x-model="title" @input="autoSlug()" required class="w-full px-3.5 py-2.5 border rounded-lg font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Custom URL Slug (Editable)</label>
                    <div class="flex items-center border rounded-lg overflow-hidden bg-slate-50 text-xs">
                        <span class="px-3 py-2 text-slate-400 bg-slate-100 border-r select-none">{{ url('/') }}/</span>
                        <input type="text" name="slug" x-model="slug" @input="slugEdited = true" placeholder="auto-generated-from-title" class="w-full px-3 py-2 bg-white focus:outline-none text-slate-800 font-medium">
                    </div>
                </div>

                @if($type === 'team')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Designation *</label>
                        <input type="text" name="meta_data[designation]" value="{{ old('meta_data.designation', $post->meta_data['designation'] ?? '') }}" placeholder="e.g. Editor & Publisher" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Area / Department</label>
                        <input type="text" name="meta_data[area]" value="{{ old('meta_data.area', $post->meta_data['area'] ?? '') }}" placeholder="e.g. Sylhet Correspondent" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Phone Number</label>
                        <input type="text" name="meta_data[phone]" value="{{ old('meta_data.phone', $post->meta_data['phone'] ?? '') }}" placeholder="+88017XXXXXXXX" class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                @elseif($type === 'ad')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Ad Slot Position *</label>
                        <select name="meta_data[ad_slot]" class="w-full px-3 py-2 border rounded-lg text-sm">
                            @php $slot = $post->meta_data['ad_slot'] ?? ''; @endphp
                            <option value="header_top" {{ $slot === 'header_top' ? 'selected' : '' }}>Header Top Banner (728x90)</option>
                            <option value="sidebar_1" {{ $slot === 'sidebar_1' ? 'selected' : '' }}>Sidebar Top (300x250)</option>
                            <option value="inside_news" {{ $slot === 'inside_news' ? 'selected' : '' }}>Inside Single News</option>
                            <option value="home_middle" {{ $slot === 'home_middle' ? 'selected' : '' }}>Home Page Middle Banner</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Target URL</label>
                        <input type="url" name="meta_data[ad_link]" value="{{ old('meta_data.ad_link', $post->meta_data['ad_link'] ?? '') }}" placeholder="https://..." class="w-full px-3 py-2 border rounded-lg text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Or Google AdSense / HTML Script Code</label>
                    <textarea name="body" rows="4" placeholder="<ins class='adsbygoogle'...>" class="w-full px-3 py-2 border rounded-lg text-xs font-mono">{{ old('body', $post->body ?? '') }}</textarea>
                </div>
                @else
                <div>
                    <label class="block text-sm font-semibold mb-1">Short Excerpt</label>
                    <textarea name="excerpt" rows="2" class="w-full px-3.5 py-2 border rounded-lg text-sm">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-semibold">Content Body</label>
                        <span x-show="uploadingImage" class="text-xs font-semibold text-emerald-600 animate-pulse">Uploading image...</span>
                    </div>
                    <!-- Hidden textarea synced with Quill HTML -->
                    <textarea name="body" x-model="body" class="hidden">{{ old('body', $post->body ?? '') }}</textarea>
                    <!-- Rich Text Editor Container -->
                    <div wire:ignore>
                        <div id="quill-editor">{!! old('body', $post->body ?? '') !!}</div>
                    </div>
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
                    <div class="text-base font-semibold text-blue-700 truncate" x-text="metaTitle || title || 'Post Title Preview'"></div>
                    <div class="text-xs text-slate-600 line-clamp-2 mt-0.5" x-text="metaDesc || 'Your meta description preview will appear here in Google search results...'"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block font-semibold mb-1">Focus Keyword</label>
                        <input type="text" name="focus_keyword" x-model="keyword" placeholder="e.g. Sylhet News" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Custom SEO Title</label>
                        <input type="text" name="meta_title" x-model="metaTitle" placeholder="Leave blank to use main title" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>

                <div class="text-sm">
                    <label class="block font-semibold mb-1">Meta Description (<span x-text="metaDesc.length"></span>/160 characters)</label>
                    <textarea name="meta_description" x-model="metaDesc" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>

                <!-- Live Checklist -->
                <ul class="text-xs space-y-1.5 pt-2 border-t text-slate-600">
                    <li :class="checks.keywordInTitle ? 'text-emerald-600 font-medium' : 'text-rose-500'">
                        <span x-text="checks.keywordInTitle ? '✔' : '✖'"></span> Focus Keyword appears in the SEO Title
                    </li>
                    <li :class="checks.keywordInDesc ? 'text-emerald-600 font-medium' : 'text-rose-500'">
                        <span x-text="checks.keywordInDesc ? '✔' : '✖'"></span> Focus Keyword appears in the Meta Description
                    </li>
                    <li :class="checks.descLength ? 'text-emerald-600 font-medium' : 'text-amber-600'">
                        <span x-text="checks.descLength ? '✔' : '✖'"></span> Meta Description length is optimal (50–160 characters)
                    </li>
                    <li :class="checks.wordCount ? 'text-emerald-600 font-medium' : 'text-amber-600'">
                        <span x-text="checks.wordCount ? '✔' : '✖'"></span> Content length is sufficient (at least 150 words)
                    </li>
                    <li :class="hasImage ? 'text-emerald-600 font-medium' : 'text-rose-500'">
                        <span x-text="hasImage ? '✔' : '✖'"></span> Featured image is attached (for Social & Google Rich Results)
                    </li>
                </ul>
            </div>
            @endif
        </div>

        <!-- Right Sidebar Options -->
        <div class="space-y-5">
            <div class="bg-white p-5 rounded-xl border border-slate-200 space-y-4 text-sm">
                <h3 class="font-bold text-slate-800 border-b pb-2">Publish & Options</h3>

                <div>
                    <label class="block font-semibold mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg">
                        <option value="published" {{ ($post->status ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ ($post->status ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="archived" {{ ($post->status ?? '') === 'archived' ? 'selected' : '' }}>Archived (Hidden from Public)</option>
                    </select>
                </div>

                <!-- Separate Publish Date & Publish Time with Set Now -->
                <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-700 text-xs uppercase">Publish Schedule</span>
                        <button type="button" @click="setNow()" class="text-xs text-emerald-600 hover:underline font-semibold">
                            Set Current Time
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Date</label>
                            <input type="date" name="publish_date" x-model="pubDate" class="w-full px-2.5 py-1.5 border rounded-lg bg-white text-xs">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">Time (HH:MM:SS)</label>
                            <input type="time" step="1" name="publish_time" x-model="pubTime" class="w-full px-2.5 py-1.5 border rounded-lg bg-white text-xs">
                        </div>
                    </div>
                </div>

                @if(in_array($type, ['news', 'service']))
                <div>
                    <label class="block font-semibold mb-1">Category / Area</label>
                    <select name="category_id" class="w-full px-3 py-2 border rounded-lg">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($post->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($type === 'news')
                <div class="space-y-2 pt-2 border-t">
                    <label class="flex items-center gap-2 font-medium text-rose-600">
                        <input type="checkbox" name="is_breaking" value="1" {{ ($post->is_breaking ?? false) ? 'checked' : '' }} class="rounded"> Show in Breaking Ticker
                    </label>
                    <label class="flex items-center gap-2 font-medium text-blue-600">
                        <input type="checkbox" name="is_featured" value="1" {{ ($post->is_featured ?? false) ? 'checked' : '' }} class="rounded"> Featured / Lead News
                    </label>
                </div>

                <div>
                    <label class="block font-semibold mb-1">Reporter Name</label>
                    <input type="text" name="meta_data[reporter]" value="{{ old('meta_data.reporter', $post->meta_data['reporter'] ?? 'Staff Correspondent') }}" class="w-full px-3 py-2 border rounded-lg">
                </div>

                <div>
                    <label class="block font-semibold mb-1">YouTube / Facebook Video URL</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $post->video_url ?? '') }}" placeholder="https://youtube.com/..." class="w-full px-3 py-2 border rounded-lg">
                </div>
                @endif

                <div>
                    <label class="block font-semibold mb-1">Featured Image (Auto WebP)</label>
                    <input type="file" name="featured_image" @change="hasImage = true" accept="image/*" class="w-full text-xs border rounded-lg p-2">
                    @if(isset($post) && $post->featured_image)
                        <img src="{{ asset('storage/' . $post->featured_image) }}" class="mt-2 w-full h-36 object-cover rounded-lg border">
                    @endif
                </div>

                <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow transition">
                    {{ isset($post) ? 'Update Content' : 'Publish Content' }}
                </button>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
function rankMathSeo(initial) {
    return {
        title: initial.title,
        slug: initial.slug,
        slugEdited: initial.slug.length > 0,
        body: initial.body,
        keyword: initial.keyword,
        metaTitle: initial.metaTitle,
        metaDesc: initial.metaDesc,
        hasImage: initial.hasImage,
        pubDate: initial.pubDate,
        pubTime: initial.pubTime,
        uploadingImage: false,
        init() {
            const editorEl = document.getElementById('quill-editor');
            if (!editorEl) return;

            const quill = new Quill(editorEl, {
                theme: 'snow',
                placeholder: 'Write your article or content here...',
                modules: {
                    toolbar: {
                        container: [
                            [{ 'header': [1, 2, 3, 4, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'align': [] }],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            ['blockquote', 'link', 'image', 'video'],
                            ['clean']
                        ],
                        handlers: {
                            image: () => {
                                const input = document.createElement('input');
                                input.setAttribute('type', 'file');
                                input.setAttribute('accept', 'image/*');
                                input.click();

                                input.onchange = async () => {
                                    const file = input.files[0];
                                    if (!file) return;

                                    this.uploadingImage = true;
                                    const formData = new FormData();
                                    formData.append('image', file);

                                    try {
                                        const res = await fetch('{{ route("admin.editor.upload") }}', {
                                            method: 'POST',
                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                            body: formData
                                        });
                                        const data = await res.json();
                                        if (data.url) {
                                            const range = quill.getSelection(true);
                                            quill.insertEmbed(range.index, 'image', data.url);
                                            quill.setSelection(range.index + 1);
                                        }
                                    } catch (e) {
                                        alert('Image upload failed. Max 5MB allowed.');
                                    } finally {
                                        this.uploadingImage = false;
                                    }
                                };
                            }
                        }
                    }
                }
            });

            quill.on('text-change', () => {
                this.body = quill.root.innerHTML;
            });
        },
        setNow() {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            this.pubDate = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
            this.pubTime = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
        },
        autoSlug() {
            if (!this.slugEdited) {
                this.slug = this.title.trim()
                    .replace(/[^\p{L}\p{N}\s\-]+/gu, '')
                    .replace(/[\s\-]+/gu, '-')
                    .replace(/^-+|-+$/g, '')
                    .toLowerCase();
            }
        },
        get checks() {
            const kw = this.keyword.trim().toLowerCase();
            const t = (this.metaTitle || this.title).toLowerCase();
            const d = this.metaDesc.toLowerCase();
            const plainText = this.body.replace(/<[^>]*>/g, ' ').trim();
            const words = plainText.split(/\s+/).filter(Boolean).length;
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
