@extends('layouts.admin')
@section('title', 'Content Manager (' . strtoupper($type) . ')')

@section('content')
<div class="bg-white rounded-xl border border-slate-200 overflow-visible" x-data="postTableManager()">
    <!-- Status Tabs (Active / Archived / Trash) -->
    <div class="px-4 pt-3 border-b bg-slate-50 rounded-t-xl flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1 text-sm font-semibold">
            <a href="{{ route('admin.posts.index', ['type' => $type, 'filter' => 'active']) }}"
               class="px-4 py-2.5 rounded-t-lg border-b-2 transition {{ $filter === 'active' ? 'bg-white border-emerald-600 text-emerald-700' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                All Active ({{ $counts['active'] }})
            </a>
            <a href="{{ route('admin.posts.index', ['type' => $type, 'filter' => 'archived']) }}"
               class="px-4 py-2.5 rounded-t-lg border-b-2 transition {{ $filter === 'archived' ? 'bg-white border-amber-600 text-amber-700' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                📦 Archived ({{ $counts['archived'] }})
            </a>
            <a href="{{ route('admin.posts.index', ['type' => $type, 'filter' => 'trash']) }}"
               class="px-4 py-2.5 rounded-t-lg border-b-2 transition {{ $filter === 'trash' ? 'bg-white border-rose-600 text-rose-700' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                🗑️ Trash ({{ $counts['trash'] }})
            </a>
        </div>

        <a href="{{ route('admin.posts.create', ['type' => $type]) }}" class="mb-2 px-4 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
            + Add New
        </a>
    </div>

    <!-- WordPress-Style Toolbar -->
    <div class="p-4 border-b flex flex-wrap items-center justify-between gap-3 text-sm">
        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isEditor())
            <form id="bulk-form" action="{{ route('admin.posts.bulk') }}" method="POST" class="flex items-center gap-1.5" @submit="submitBulk($event)">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <select name="bulk_action" x-model="bulkAction" class="px-3 py-1.5 border border-slate-300 rounded-lg bg-white text-slate-700 focus:outline-none">
                    <option value="">Bulk actions</option>
                    @if($filter === 'trash')
                        <option value="restore">Recover</option>
                        <option value="force_delete">Delete Permanently</option>
                    @else
                        <option value="publish">Publish</option>
                        <option value="draft">Move to Draft</option>
                        <option value="archive">Archive</option>
                        <option value="trash">Move to Trash</option>
                    @endif
                </select>
                <button type="submit" class="px-3 py-1.5 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition">
                    Apply
                </button>
            </form>
            @endif

            <form method="GET" action="{{ route('admin.posts.index') }}" class="flex flex-wrap items-center gap-1.5">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="filter" value="{{ $filter }}">
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                <select name="month" class="px-3 py-1.5 border border-slate-300 rounded-lg bg-white text-slate-700 focus:outline-none">
                    <option value="">All dates</option>
                    @foreach($months as $m)
                        <option value="{{ $m['value'] }}" {{ request('month') === $m['value'] ? 'selected' : '' }}>
                            {{ $m['label'] }}
                        </option>
                    @endforeach
                </select>

                @if(in_array($type, ['news', 'service']))
                <select name="category_id" class="px-3 py-1.5 border border-slate-300 rounded-lg bg-white text-slate-700 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @endif

                <button type="submit" class="px-3 py-1.5 border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg font-medium transition">
                    Filter
                </button>

                @if(request()->filled('month') || request()->filled('category_id') || request()->filled('search'))
                    <a href="{{ route('admin.posts.index', ['type' => $type, 'filter' => $filter]) }}" class="text-xs text-slate-500 hover:text-rose-600 px-1.5">Reset</a>
                @endif
            </form>
        </div>

        <form method="GET" action="{{ route('admin.posts.index') }}" class="flex items-center gap-1.5">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="filter" value="{{ $filter }}">
            @if(request('month')) <input type="hidden" name="month" value="{{ request('month') }}"> @endif
            @if(request('category_id')) <input type="hidden" name="category_id" value="{{ request('category_id') }}"> @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title..." class="px-3 py-1.5 border border-slate-300 rounded-lg text-sm">
            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-700">Search</button>
        </form>
    </div>

    <div class="overflow-x-auto pb-32">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 border-b text-slate-600">
                <tr>
                    <th class="pl-4 pr-2 py-3 w-8">
                        <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300 text-emerald-600">
                    </th>
                    <th class="px-4 py-3">Image</th>
                    <th class="px-4 py-3">Title / Name</th>
                    <th class="px-4 py-3">Category / Info</th>
                    <th class="px-4 py-3">Date & Time</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($posts as $post)
                <tr class="hover:bg-slate-50/70" :class="selectedIds.includes({{ $post->id }}) ? 'bg-emerald-50/40' : ''">
                    <td class="pl-4 pr-2 py-3">
                        <input type="checkbox" value="{{ $post->id }}" x-model.number="selectedIds" class="row-checkbox rounded border-slate-300 text-emerald-600">
                    </td>
                    <td class="px-4 py-3">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" class="w-12 h-9 object-cover rounded">
                        @else
                            <div class="w-12 h-9 bg-slate-200 rounded flex items-center justify-center text-xs text-slate-400">N/A</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800">
                        {{ $post->title }}
                        @if($post->is_breaking) <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-rose-100 text-rose-700 rounded">Breaking</span> @endif
                        @if($post->is_featured) <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-blue-100 text-blue-700 rounded">Lead</span> @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        {{ $post->category?->name ?? ($post->meta_data['designation'] ?? ($post->meta_data['ad_slot'] ?? '-')) }}
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                        {{ $post->published_at ? $post->published_at->format('d M Y, h:i A') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($filter === 'trash')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-rose-100 text-rose-700 font-semibold">Trashed</span>
                        @elseif($post->status === 'archived')
                            <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-semibold">Archived</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs capitalize {{ $post->status === 'published' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                                {{ $post->status }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="relative inline-block text-left" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = !open"
                                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-200/70 hover:text-slate-900 focus:outline-none transition"
                                    title="Actions">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                </svg>
                            </button>

                            <div x-show="open" x-transition.origin.top.right style="display: none;"
                                 class="absolute right-0 mt-1 w-48 rounded-xl bg-white shadow-lg border border-slate-200 py-1.5 z-50 text-left text-xs font-medium">
                                @if($filter === 'trash')
                                    <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3.5 py-2 text-emerald-700 hover:bg-emerald-50 flex items-center gap-2 font-semibold">
                                            <span>♻️</span> Recover Post
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.posts.force-delete', $post->id) }}" method="POST" onsubmit="return confirm('Permanently delete this post and its image? This cannot be undone!')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3.5 py-2 text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                            <span>❌</span> Delete Forever
                                        </button>
                                    </form>
                                @else
                                    @if(in_array($type, ['news', 'service', 'page']) && $post->status === 'published')
                                        <a href="{{ route('post.show', $post->slug) }}" target="_blank" class="px-3.5 py-2 text-slate-700 hover:bg-slate-100 flex items-center gap-2">
                                            <span>↗️</span> View Live
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.posts.edit', $post) }}" class="px-3.5 py-2 text-blue-600 hover:bg-blue-50 flex items-center gap-2">
                                        <span>✏️</span> Edit
                                    </a>

                                    @if($type === 'news' && cms_addon('photocard', true))
                                        <button type="button"
                                                @click="open = false; openPhotocardStudio({
                                                    title: @js($post->title),
                                                    category: @js($post->category?->name ?? 'সংবাদ'),
                                                    dateStr: @js(bn_date($post->published_at)),
                                                    imgUrl: @js($post->featured_image ? asset('storage/' . $post->featured_image) : '')
                                                })"
                                                class="w-full text-left px-3.5 py-2 text-purple-600 hover:bg-purple-50 flex items-center gap-2 font-semibold">
                                            <span>🖼️</span> Photocard Studio
                                        </button>
                                    @endif

                                    @if(auth()->user()->isEditor())
                                        @if($post->status !== 'archived')
                                            <form action="{{ route('admin.posts.archive', $post) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full text-left px-3.5 py-2 text-amber-600 hover:bg-amber-50 flex items-center gap-2">
                                                    <span>📦</span> Archive
                                                </button>
                                            </form>
                                        @endif

                                        <div class="border-t border-slate-100 my-1"></div>

                                        <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Move this item to Trash?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full text-left px-3.5 py-2 text-rose-600 hover:bg-rose-50 flex items-center gap-2">
                                                <span>🗑️</span> Move to Trash
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t">{{ $posts->links() }}</div>

    <!-- Pro Social Photocard Studio Modal -->
    <div x-show="cardModal" style="display: none;" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
        <div class="bg-white rounded-2xl max-w-5xl w-full overflow-hidden shadow-2xl grid grid-cols-1 lg:grid-cols-12" @click.outside="cardModal = false">
            <!-- Left Customization Controls -->
            <div class="lg:col-span-6 p-5 sm:p-6 space-y-3.5 border-r border-slate-200 max-h-[90vh] overflow-y-auto text-sm">
                <div class="flex items-center justify-between border-b pb-2.5">
                    <h3 class="font-bold text-slate-800 text-base">🎨 Facebook Photocard Studio</h3>
                    <button type="button" @click="cardModal = false" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
                </div>

                <!-- 1. Card Size & Design Template -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-xs text-slate-600 mb-1">Card Size (Ratio)</label>
                        <select x-model="card.size" @change="renderCard()" class="w-full px-2.5 py-1.5 border rounded-lg text-xs font-semibold bg-white">
                            <option value="square">Square (1080 × 1080)</option>
                            <option value="portrait">FB Portrait (1080 × 1350)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-xs text-slate-600 mb-1">Design Style</label>
                        <select x-model="card.template" @change="renderCard()" class="w-full px-2.5 py-1.5 border rounded-lg text-xs font-semibold bg-white">
                            <option value="classic">News Photo + Texture Card</option>
                            <option value="overlay">Breaking Dark Overlay</option>
                            <option value="quote">Quote / উক্তি Card</option>
                        </select>
                    </div>
                </div>

                <!-- 2. Headline Background Texture -->
                <div>
                    <label class="block font-semibold text-xs text-slate-600 mb-1">Headline Background Texture (আকর্ষণীয় টেক্সচার)</label>
                    <div class="grid grid-cols-4 gap-1.5 text-[11px] font-semibold">
                        <button type="button" @click="card.texture = 'grid'; renderCard()"
                                :class="card.texture === 'grid' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="py-1.5 px-2 rounded-lg border text-center">
                            Modern Grid
                        </button>
                        <button type="button" @click="card.texture = 'diagonal'; renderCard()"
                                :class="card.texture === 'diagonal' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="py-1.5 px-2 rounded-lg border text-center">
                            Diagonal Lines
                        </button>
                        <button type="button" @click="card.texture = 'newsprint'; renderCard()"
                                :class="card.texture === 'newsprint' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="py-1.5 px-2 rounded-lg border text-center">
                            Newsprint
                        </button>
                        <button type="button" @click="card.texture = 'clean'; renderCard()"
                                :class="card.texture === 'clean' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-700 border-slate-200'"
                                class="py-1.5 px-2 rounded-lg border text-center">
                            Clean White
                        </button>
                    </div>
                </div>

                <!-- 3. Brand Color & Font Size -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-xs text-slate-600 mb-1">Brand Accent Color</label>
                        <div class="flex items-center gap-1.5">
                            <input type="color" x-model="card.brandColor" @input="renderCard()" class="h-8 w-10 p-0.5 border rounded cursor-pointer">
                            <button type="button" @click="card.brandColor = '#dc2626'; renderCard()" class="w-6 h-6 rounded-full bg-red-600"></button>
                            <button type="button" @click="card.brandColor = '#059669'; renderCard()" class="w-6 h-6 rounded-full bg-emerald-600"></button>
                            <button type="button" @click="card.brandColor = '#1d4ed8'; renderCard()" class="w-6 h-6 rounded-full bg-blue-700"></button>
                            <button type="button" @click="card.brandColor = '#b45309'; renderCard()" class="w-6 h-6 rounded-full bg-amber-700"></button>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-xs text-slate-600 mb-1">Font Size (<span x-text="card.fontSize"></span>px)</label>
                        <input type="range" min="36" max="62" x-model="card.fontSize" @input="renderCard()" class="w-full mt-1.5">
                    </div>
                </div>

                <!-- 4. Headline / Quote Text -->
                <div>
                    <label class="block font-semibold text-xs text-slate-600 mb-1" x-text="card.template === 'quote' ? 'Quote / উক্তির মূল কথা' : 'Card Headline Text'"></label>
                    <textarea rows="2" x-model="card.title" @input="renderCard()" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea>
                </div>

                <!-- Conditional Speaker Name for Quote Template -->
                <div x-show="card.template === 'quote'" class="grid grid-cols-2 gap-3 bg-purple-50/70 p-2.5 rounded-lg border border-purple-100">
                    <div>
                        <label class="block font-semibold text-xs text-purple-900 mb-1">বক্তার নাম (Speaker Name)</label>
                        <input type="text" x-model="card.speakerName" @input="renderCard()" placeholder="যেমন: ড. মুহাম্মদ ইউনূস" class="w-full px-2.5 py-1.5 border rounded bg-white text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-xs text-purple-900 mb-1">পদবি (Designation)</label>
                        <input type="text" x-model="card.speakerRole" @input="renderCard()" placeholder="প্রধান উপদেষ্টা" class="w-full px-2.5 py-1.5 border rounded bg-white text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-xs text-slate-600 mb-1">Category / Breaking Badge</label>
                        <input type="text" x-model="card.category" @input="renderCard()" class="w-full px-3 py-1.5 border rounded-lg text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-xs text-slate-600 mb-1">Date Text</label>
                        <input type="text" x-model="card.dateStr" @input="renderCard()" class="w-full px-3 py-1.5 border rounded-lg text-xs">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-1 text-xs font-medium text-slate-700">
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox" x-model="card.showLogo" @change="renderCard()" class="rounded"> Portal Logo/Name
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox" x-model="card.showDate" @change="renderCard()" class="rounded"> Date
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="checkbox" x-model="card.showCommentCTA" @change="renderCard()" class="rounded"> "বিস্তারিত কমেন্টে"
                    </label>
                </div>

                <div class="pt-3 border-t flex items-center gap-3">
                    <button type="button" @click="downloadStudioCard()" class="flex-1 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow transition">
                        ⬇️ Download PNG (<span x-text="card.size === 'portrait' ? '1080×1350' : '1080×1080'"></span>)
                    </button>
                    <button type="button" @click="cardModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl">
                        Close
                    </button>
                </div>
            </div>

            <!-- Right Live Preview -->
            <div class="lg:col-span-6 bg-slate-100 p-5 flex flex-col items-center justify-center">
                <div class="text-xs font-semibold text-slate-500 mb-2">
                    Live Facebook Preview (<span x-text="card.size === 'portrait' ? '1080 × 1350 Portrait' : '1080 × 1080 Square'"></span>)
                </div>
                <canvas id="studio-photocard-canvas" width="1080" height="1080"
                        :class="card.size === 'portrait' ? 'max-w-[310px]' : 'max-w-[380px]'"
                        class="w-full rounded-xl shadow-lg bg-white border border-slate-200"></canvas>
            </div>
        </div>
    </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@600;700&display=swap" rel="stylesheet">
<script>
function postTableManager() {
    return {
        selectedIds: [],
        bulkAction: '',
        cardModal: false,
        loadedImg: null,
        loadedLogo: null,
        card: {
            size: 'square',
            template: 'classic',
            texture: 'grid',
            brandColor: @js(cms_setting('primary_color', '#dc2626')),
            siteName: @js(cms_setting('site_name', 'InsightPress News')),
            siteHost: @js(parse_url(url('/'), PHP_URL_HOST)),
            logoUrl: @js(cms_setting('logo') ? asset('storage/' . cms_setting('logo')) : ''),
            title: '',
            category: '',
            dateStr: '',
            imgUrl: '',
            speakerName: '',
            speakerRole: '',
            fontSize: 46,
            showLogo: true,
            showDate: true,
            showCommentCTA: true,
        },
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(el => Number(el.value));
            } else {
                this.selectedIds = [];
            }
        },
        submitBulk(event) {
            if (!this.bulkAction) {
                event.preventDefault();
                alert('Please select a bulk action first.');
                return;
            }
            if (this.selectedIds.length === 0) {
                event.preventDefault();
                alert('Please select at least one item using the checkboxes.');
                return;
            }
            if (this.bulkAction === 'force_delete' && !confirm('Permanently delete selected items? This cannot be undone!')) {
                event.preventDefault();
            }
        },
        async loadImage(url) {
            if (!url) return null;
            try {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                    img.src = url;
                });
                return img;
            } catch (e) {
                return null;
            }
        },
        async openPhotocardStudio(post) {
            this.card.title = post.title;
            this.card.category = post.category;
            this.card.dateStr = post.dateStr;
            this.card.imgUrl = post.imgUrl;
            this.card.speakerName = '';
            this.card.speakerRole = '';
            this.loadedImg = null;
            this.cardModal = true;

            this.loadedImg = await this.loadImage(post.imgUrl);
            if (!this.loadedLogo && this.card.logoUrl) {
                this.loadedLogo = await this.loadImage(this.card.logoUrl);
            }
            this.$nextTick(() => this.renderCard());
        },
        drawTextureBackground(ctx, x, y, w, h, textureType, accentColor) {
            ctx.fillStyle = textureType === 'newsprint' ? '#faf7f2' : '#ffffff';
            ctx.fillRect(x, y, w, h);

            if (textureType === 'clean') return;

            ctx.save();
            ctx.beginPath();
            ctx.rect(x, y, w, h);
            ctx.clip();

            if (textureType === 'grid') {
                const rad = ctx.createRadialGradient(x + w * 0.85, y + h * 0.2, 20, x + w * 0.85, y + h * 0.2, w * 0.7);
                rad.addColorStop(0, accentColor + '14');
                rad.addColorStop(1, 'rgba(255,255,255,0)');
                ctx.fillStyle = rad;
                ctx.fillRect(x, y, w, h);

                ctx.strokeStyle = 'rgba(15, 23, 42, 0.055)';
                ctx.lineWidth = 1.5;
                const step = 36;
                for (let gx = x; gx <= x + w; gx += step) {
                    ctx.beginPath(); ctx.moveTo(gx, y); ctx.lineTo(gx, y + h); ctx.stroke();
                }
                for (let gy = y; gy <= y + h; gy += step) {
                    ctx.beginPath(); ctx.moveTo(x, gy); ctx.lineTo(x + w, gy); ctx.stroke();
                }
            } else if (textureType === 'diagonal') {
                ctx.strokeStyle = 'rgba(15, 23, 42, 0.045)';
                ctx.lineWidth = 2;
                const gap = 24;
                for (let i = -h; i < w + h; i += gap) {
                    ctx.beginPath();
                    ctx.moveTo(x + i, y);
                    ctx.lineTo(x + i - h, y + h);
                    ctx.stroke();
                }
            } else if (textureType === 'newsprint') {
                ctx.fillStyle = 'rgba(120, 113, 108, 0.12)';
                for (let dx = x + 16; dx < x + w; dx += 22) {
                    for (let dy = y + 16; dy < y + h; dy += 22) {
                        ctx.beginPath();
                        ctx.arc(dx, dy, 1.4, 0, Math.PI * 2);
                        ctx.fill();
                    }
                }
            }
            ctx.restore();
        },
        drawCoverImage(ctx, img, x, y, w, h) {
            if (!img) {
                ctx.fillStyle = '#e2e8f0';
                ctx.fillRect(x, y, w, h);
                return;
            }
            const scale = Math.max(w / img.width, h / img.height);
            const dw = img.width * scale;
            const dh = img.height * scale;
            const dx = x + (w - dw) / 2;
            const dy = y + (h - dh) / 2;
            ctx.save();
            ctx.beginPath();
            ctx.rect(x, y, w, h);
            ctx.clip();
            ctx.drawImage(img, dx, dy, dw, dh);
            ctx.restore();
        },
        drawBrandBadge(ctx, x, y, darkBg = false) {
            if (!this.card.showLogo) return;
            if (this.loadedLogo) {
                const maxH = 54;
                const ratio = this.loadedLogo.width / this.loadedLogo.height;
                const drawW = Math.min(240, maxH * ratio);
                const drawH = drawW / ratio;
                ctx.fillStyle = 'rgba(255,255,255,0.95)';
                ctx.beginPath();
                ctx.roundRect(x, y, drawW + 24, drawH + 16, 10);
                ctx.fill();
                ctx.drawImage(this.loadedLogo, x + 12, y + 8, drawW, drawH);
            } else {
                ctx.fillStyle = darkBg ? '#ffffff' : this.card.brandColor;
                ctx.beginPath();
                ctx.roundRect(x, y, 310, 52, 8);
                ctx.fill();
                ctx.fillStyle = darkBg ? this.card.brandColor : '#ffffff';
                ctx.font = 'bold 28px "Hind Siliguri", sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(this.card.siteName, x + 155, y + 36);
            }
        },
        getLines(ctx, text, maxWidth, maxLines = 4) {
            const words = String(text || '').split(' ');
            let lines = [];
            let line = '';
            for (let n = 0; n < words.length; n++) {
                const testLine = line + words[n] + ' ';
                if (ctx.measureText(testLine).width > maxWidth && n > 0) {
                    lines.push(line.trim());
                    line = words[n] + ' ';
                } else {
                    line = testLine;
                }
            }
            if (line.trim()) lines.push(line.trim());
            return lines.slice(0, maxLines);
        },
        drawCenteredBlock(ctx, text, centerX, boxTop, boxBottom, maxWidth, lineHeight, maxLines = 4) {
            const lines = this.getLines(ctx, text, maxWidth, maxLines);
            const totalTextHeight = (lines.length - 1) * lineHeight;
            const boxCenterY = (boxTop + boxBottom) / 2;
            let y = boxCenterY - (totalTextHeight / 2) + (lineHeight * 0.22);

            ctx.textAlign = 'center';
            for (let i = 0; i < lines.length; i++) {
                ctx.fillText(lines[i], centerX, y);
                y += lineHeight;
            }
            return y - lineHeight;
        },
        renderCard() {
            const canvas = document.getElementById('studio-photocard-canvas');
            if (!canvas) return;
            const c = this.card;
            const W = 1080;
            const H = c.size === 'portrait' ? 1350 : 1080;
            canvas.width = W;
            canvas.height = H;

            const ctx = canvas.getContext('2d');
            const fSize = Number(c.fontSize);
            const lHeight = Math.round(fSize * 1.4);

            ctx.clearRect(0, 0, W, H);

            if (c.template === 'classic') {
                const imgH = Math.round(H * 0.55);
                const footerH = 74;
                const textAreaH = H - imgH - footerH;

                this.drawCoverImage(ctx, this.loadedImg, 0, 0, W, imgH);
                this.drawBrandBadge(ctx, 36, 36, false);

                ctx.fillStyle = c.brandColor;
                ctx.fillRect(0, imgH - 8, W, 8);

                this.drawTextureBackground(ctx, 0, imgH, W, textAreaH, c.texture, c.brandColor);

                const pillW = 240;
                ctx.fillStyle = c.brandColor;
                ctx.beginPath();
                ctx.roundRect((W - pillW) / 2, imgH - 26, pillW, 48, 24);
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 25px "Hind Siliguri", sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(c.category, W / 2, imgH + 6);

                if (c.showDate) {
                    ctx.fillStyle = '#64748b';
                    ctx.font = '600 22px "Hind Siliguri", sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText(c.dateStr, W / 2, imgH + 56);
                }

                const textTop = c.showDate ? imgH + 74 : imgH + 42;
                const textBottom = H - footerH - 18;
                ctx.fillStyle = '#0f172a';
                ctx.font = 'bold ' + fSize + 'px "Hind Siliguri", sans-serif';
                this.drawCenteredBlock(ctx, c.title, W / 2, textTop, textBottom, W - 110, lHeight, 4);

                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, H - footerH, W, footerH);
                ctx.fillStyle = '#ffffff';
                ctx.font = '600 24px "Hind Siliguri", sans-serif';
                ctx.textAlign = c.showCommentCTA ? 'left' : 'center';
                ctx.fillText('🌐 ' + c.siteHost, c.showCommentCTA ? 48 : W / 2, H - 28);

                if (c.showCommentCTA) {
                    ctx.fillStyle = c.brandColor;
                    ctx.beginPath();
                    ctx.roundRect(W - 290, H - footerH + 14, 242, 46, 23);
                    ctx.fill();
                    ctx.fillStyle = '#ffffff';
                    ctx.font = 'bold 22px "Hind Siliguri", sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('💬 বিস্তারিত কমেন্টে', W - 169, H - 30);
                }

            } else if (c.template === 'overlay') {
                this.drawCoverImage(ctx, this.loadedImg, 0, 0, W, H);

                const grad = ctx.createLinearGradient(0, H * 0.25, 0, H);
                grad.addColorStop(0, 'rgba(15, 23, 42, 0.08)');
                grad.addColorStop(0.45, 'rgba(15, 23, 42, 0.85)');
                grad.addColorStop(1, 'rgba(15, 23, 42, 0.98)');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, W, H);

                this.drawBrandBadge(ctx, 45, 45, false);

                const badgeY = H - 410;
                const badgeW = 260;
                ctx.fillStyle = c.brandColor;
                ctx.beginPath();
                ctx.roundRect((W - badgeW) / 2, badgeY, badgeW, 46, 23);
                ctx.fill();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 24px "Hind Siliguri", sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('🔴 ' + c.category, W / 2, badgeY + 31);

                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold ' + fSize + 'px "Hind Siliguri", sans-serif';
                this.drawCenteredBlock(ctx, c.title, W / 2, badgeY + 65, H - 105, W - 110, lHeight, 4);

                ctx.fillStyle = c.brandColor;
                ctx.fillRect(45, H - 85, W - 90, 4);
                ctx.fillStyle = '#e2e8f0';
                ctx.font = '600 22px "Hind Siliguri", sans-serif';
                if (c.showDate) {
                    ctx.textAlign = c.showCommentCTA ? 'left' : 'center';
                    ctx.fillText(c.dateStr + '  |  ' + c.siteHost, c.showCommentCTA ? 45 : W / 2, H - 38);
                }
                if (c.showCommentCTA) {
                    ctx.textAlign = 'right';
                    ctx.fillText('💬 বিস্তারিত কমেন্টে', W - 45, H - 38);
                }

            } else {
                ctx.fillStyle = c.brandColor;
                ctx.fillRect(0, 0, W, H);

                this.drawTextureBackground(ctx, 34, 34, W - 68, H - 68, c.texture, c.brandColor);

                this.drawBrandBadge(ctx, 68, 65, false);
                if (c.showDate) {
                    ctx.fillStyle = '#64748b';
                    ctx.font = '600 23px "Hind Siliguri", sans-serif';
                    ctx.textAlign = 'right';
                    ctx.fillText(c.dateStr, W - 68, 102);
                }

                ctx.fillStyle = c.brandColor + '30';
                ctx.font = 'bold 150px Georgia, serif';
                ctx.textAlign = 'center';
                ctx.fillText('“', W / 2, 245);

                const speakerAreaTop = H - 310;
                ctx.fillStyle = '#0f172a';
                ctx.font = 'bold ' + fSize + 'px "Hind Siliguri", sans-serif';
                this.drawCenteredBlock(ctx, c.title, W / 2, 215, speakerAreaTop - 20, W - 160, lHeight, 4);

                if (this.loadedImg) {
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(W / 2, speakerAreaTop + 55, 62, 0, Math.PI * 2);
                    ctx.closePath();
                    ctx.lineWidth = 5;
                    ctx.strokeStyle = c.brandColor;
                    ctx.stroke();
                    ctx.clip();
                    this.drawCoverImage(ctx, this.loadedImg, (W / 2) - 62, speakerAreaTop - 7, 124, 124);
                    ctx.restore();
                }

                const nameY = this.loadedImg ? speakerAreaTop + 155 : speakerAreaTop + 75;
                ctx.fillStyle = c.brandColor;
                ctx.font = 'bold 31px "Hind Siliguri", sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText('— ' + (c.speakerName || c.category), W / 2, nameY);

                if (c.speakerRole) {
                    ctx.fillStyle = '#475569';
                    ctx.font = '600 23px "Hind Siliguri", sans-serif';
                    ctx.fillText(c.speakerRole, W / 2, nameY + 36);
                }

                ctx.fillStyle = '#0f172a';
                ctx.fillRect(34, H - 102, W - 68, 68);
                ctx.fillStyle = '#ffffff';
                ctx.font = '600 23px "Hind Siliguri", sans-serif';
                ctx.textAlign = c.showCommentCTA ? 'left' : 'center';
                ctx.fillText('🌐 ' + c.siteHost, c.showCommentCTA ? 68 : W / 2, H - 60);
                if (c.showCommentCTA) {
                    ctx.textAlign = 'right';
                    ctx.fillText('💬 বিস্তারিত কমেন্টে', W - 68, H - 60);
                }
            }
        },
        downloadStudioCard() {
            const canvas = document.getElementById('studio-photocard-canvas');
            const link = document.createElement('a');
            link.download = 'photocard-' + this.card.template + '-' + this.card.size + '-' + Date.now() + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    }
}
</script>
@endsection
