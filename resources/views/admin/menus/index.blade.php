@extends('layouts.admin')
@section('title', 'Menu Builder')

@php
    $flatList = [];
    foreach ($menuItems as $parent) {
        $flatList[] = [
            'id' => $parent->id,
            'label' => $parent->label,
            'url' => $parent->url,
            'type' => $parent->type,
            'is_sub' => false,
            'open_new_tab' => (bool) $parent->open_new_tab,
            'expanded' => false,
        ];
        foreach ($parent->children as $child) {
            $flatList[] = [
                'id' => $child->id,
                'label' => $child->label,
                'url' => $child->url,
                'type' => $child->type,
                'is_sub' => true,
                'open_new_tab' => (bool) $child->open_new_tab,
                'expanded' => false,
            ];
        }
    }
@endphp

@section('content')
<div class="space-y-6" x-data="menuBuilder(@js($flatList))">

    <!-- Top Theme & Location Switcher -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.menus.index') }}" class="flex flex-wrap items-center gap-4">
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">1. Select Theme</label>
                <select name="theme" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-bold text-slate-800 bg-slate-50 focus:outline-none focus:border-emerald-600">
                    @foreach($themes as $slug => $title)
                        <option value="{{ $slug }}" {{ $selectedTheme === $slug ? 'selected' : '' }}>
                            {{ $title }} {{ cms_setting('active_theme') === $slug ? '★ Active' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-500 mb-1">2. Select Menu Area</label>
                <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                    <a href="{{ route('admin.menus.index', ['theme' => $selectedTheme, 'location' => 'header']) }}"
                       class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ $selectedLocation === 'header' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Header Main Menu
                    </a>
                    <a href="{{ route('admin.menus.index', ['theme' => $selectedTheme, 'location' => 'footer']) }}"
                       class="px-4 py-1.5 rounded-lg text-xs font-bold transition {{ $selectedLocation === 'footer' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Footer Menu
                    </a>
                </div>
            </div>
        </form>

        <div class="flex items-center gap-3">
            <span x-show="savedNotice" x-transition style="display: none;" class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-lg">
                ✅ মেনু সেভ হয়েছে!
            </span>
            <button type="button" @click="saveMenu()" :disabled="saving"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-bold rounded-xl shadow-sm transition cursor-pointer">
                <span x-show="!saving">💾 Save Menu</span>
                <span x-show="saving" style="display:none;">Saving...</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left Sidebar: Accordion Add Panels (WordPress Style) -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs divide-y divide-slate-200"
             x-data="{ openPanel: 'categories' }">

            <div class="px-5 py-3.5 bg-slate-50">
                <h2 class="text-sm font-bold text-slate-800">Add Menu Items</h2>
            </div>

            <!-- Panel 1: Categories -->
            <div>
                <button type="button" @click="openPanel = openPanel === 'categories' ? '' : 'categories'"
                        class="w-full px-5 py-3.5 flex items-center justify-between text-left font-bold text-sm text-slate-800 hover:bg-slate-50 cursor-pointer">
                    <span>🗂️ Categories</span>
                    <span class="text-xs text-slate-400" x-text="openPanel === 'categories' ? '▲' : '▼'"></span>
                </button>
                <div x-show="openPanel === 'categories'" class="px-5 pb-4 pt-1">
                    <form action="{{ route('admin.menus.bulk-categories') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
                        <input type="hidden" name="location" value="{{ $selectedLocation }}">

                        <div class="max-h-52 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-2">
                            @foreach($categories as $cat)
                                <label class="flex items-center gap-2.5 text-sm text-slate-700 hover:text-slate-900 cursor-pointer select-none">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ $cat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                            Add to Menu →
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panel 2: Pages -->
            <div>
                <button type="button" @click="openPanel = openPanel === 'pages' ? '' : 'pages'"
                        class="w-full px-5 py-3.5 flex items-center justify-between text-left font-bold text-sm text-slate-800 hover:bg-slate-50 cursor-pointer">
                    <span>📄 Pages</span>
                    <span class="text-xs text-slate-400" x-text="openPanel === 'pages' ? '▲' : '▼'"></span>
                </button>
                <div x-show="openPanel === 'pages'" style="display: none;" class="px-5 pb-4 pt-1">
                    <form action="{{ route('admin.menus.bulk-pages') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
                        <input type="hidden" name="location" value="{{ $selectedLocation }}">

                        <div class="max-h-44 overflow-y-auto border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-2">
                            @forelse($pages as $pg)
                                <label class="flex items-center gap-2.5 text-sm text-slate-700 hover:text-slate-900 cursor-pointer select-none">
                                    <input type="checkbox" name="page_ids[]" value="{{ $pg->id }}" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ $pg->title }}</span>
                                </label>
                            @empty
                                <p class="text-xs text-slate-400">কোনো পাবলিশড পেজ নেই।</p>
                            @endforelse
                        </div>
                        @if($pages->count() > 0)
                            <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                                Add to Menu →
                            </button>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Panel 3: Custom Link & Quick Presets -->
            <div>
                <button type="button" @click="openPanel = openPanel === 'custom' ? '' : 'custom'"
                        class="w-full px-5 py-3.5 flex items-center justify-between text-left font-bold text-sm text-slate-800 hover:bg-slate-50 cursor-pointer">
                    <span>🔗 Custom Links</span>
                    <span class="text-xs text-slate-400" x-text="openPanel === 'custom' ? '▲' : '▼'"></span>
                </button>
                <div x-show="openPanel === 'custom'" style="display: none;" class="px-5 pb-4 pt-1" x-data="{ label: '', url: '' }">
                    <form action="{{ route('admin.menus.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
                        <input type="hidden" name="location" value="{{ $selectedLocation }}">
                        <input type="hidden" name="type" value="custom">

                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="label='প্রচ্ছদ'; url='/'" class="text-[11px] px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 rounded-lg font-bold cursor-pointer">+ প্রচ্ছদ</button>
                            <button type="button" @click="label='প্রতিনিধি পরিষদ'; url='/team'" class="text-[11px] px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 rounded-lg font-bold cursor-pointer">+ প্রতিনিধি</button>
                            <button type="button" @click="label='বিভাগসমূহ'; url='#'" class="text-[11px] px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 rounded-lg font-bold cursor-pointer">+ ড্রপডাউন গ্রুপ (#)</button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Link Text *</label>
                            <input type="text" name="label" x-model="label" required placeholder="যেমন: প্রচ্ছদ বা অন্যান্য"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">URL *</label>
                            <input type="text" name="url" x-model="url" required placeholder="যেমন: / বা https://..."
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                        </div>

                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                            <input type="checkbox" name="open_new_tab" value="1" class="rounded text-emerald-600">
                            <span>Open in new tab</span>
                        </label>

                        <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition cursor-pointer">
                            Add to Menu →
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Main Area: Drag & Drop + One-Click Dropdown Structure -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Menu Structure (Drag to Reorder)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        যেকোনো আইটেমকে ড্রপডাউন বানাতে তার ডান পাশের <span class="font-bold text-indigo-600">↳ Submenu</span> বাটনে ক্লিক করুন (এটি ঠিক ওপরের মেনুটির ভেতর ঢুকে যাবে)।
                    </p>
                </div>
            </div>

            <div class="space-y-2" x-show="items.length > 0">
                <template x-for="(item, idx) in items" :key="item.id">
                    <div draggable="true"
                         @dragstart="dragIndex = idx"
                         @dragover.prevent="overIndex = idx"
                         @drop.prevent="dropItem(idx)"
                         @dragend="dragIndex = null; overIndex = null"
                         :class="[
                            item.is_sub ? 'ml-8 sm:ml-12 border-l-4 border-l-indigo-500 bg-indigo-50/30' : 'bg-slate-50/80',
                            overIndex === idx ? 'ring-2 ring-emerald-500' : ''
                         ]"
                         class="border border-slate-200 rounded-xl transition">

                        <!-- Main Bar -->
                        <div class="px-3.5 py-2.5 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="cursor-grab active:cursor-grabbing text-slate-400 hover:text-slate-700 select-none text-base" title="Drag to reorder">⋮⋮</span>
                                <span x-show="item.is_sub" class="text-xs font-bold text-indigo-600">↳</span>
                                <span class="font-bold text-sm text-slate-900 truncate" x-text="item.label"></span>
                                <span x-show="item.is_sub" class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-bold">Dropdown Item</span>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Move Up / Down (Mobile & Precision Friendly) -->
                                <button type="button" @click="moveUp(idx)" :disabled="idx === 0"
                                        class="px-2 py-1 text-xs rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-30 cursor-pointer" title="Move Up">↑</button>
                                <button type="button" @click="moveDown(idx)" :disabled="idx === items.length - 1"
                                        class="px-2 py-1 text-xs rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-30 cursor-pointer" title="Move Down">↓</button>

                                <!-- One-Click Submenu / Dropdown Toggle -->
                                <button type="button"
                                        x-show="!item.is_sub && idx > 0"
                                        @click="makeSub(idx)"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 cursor-pointer">
                                    ↳ Submenu
                                </button>
                                <button type="button"
                                        x-show="item.is_sub"
                                        @click="makeMain(idx)"
                                        class="px-2.5 py-1 text-xs font-bold rounded-lg bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 cursor-pointer">
                                    ↖ Main
                                </button>

                                <!-- Expand Edit -->
                                <button type="button" @click="item.expanded = !item.expanded"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 cursor-pointer">
                                    <span x-text="item.expanded ? '▲' : 'Edit ▼'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Inline Edit Drawer -->
                        <div x-show="item.expanded" style="display: none;" class="px-4 py-3 bg-white border-t border-slate-200 rounded-b-xl space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">Navigation Label</label>
                                    <input type="text" x-model="item.label" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-500 mb-1">URL</label>
                                    <input type="text" x-model="item.url" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 text-sm">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                                    <input type="checkbox" x-model="item.open_new_tab" class="rounded text-emerald-600">
                                    <span>Open link in new tab</span>
                                </label>

                                <form :action="'{{ url('admin/menus') }}/' + item.id" method="POST" onsubmit="return confirm('Remove this menu item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-rose-600 hover:underline cursor-pointer">
                                        🗑️ Remove Item
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="items.length === 0" class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
                <p class="text-sm font-bold text-slate-600">এই থিমের মেনুতে এখনো কোনো আইটেম যোগ করা হয়নি।</p>
                <p class="text-xs text-slate-400 mt-1">বাম পাশ থেকে Categories, Pages বা Custom Links সিলেক্ট করে যোগ করুন।</p>
            </div>

            <div x-show="items.length > 0" class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
                <button type="button" @click="saveMenu()" :disabled="saving"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition cursor-pointer">
                    💾 Save Menu
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function menuBuilder(initialItems) {
    return {
        items: initialItems,
        dragIndex: null,
        overIndex: null,
        saving: false,
        savedNotice: false,

        dropItem(targetIdx) {
            if (this.dragIndex === null || this.dragIndex === targetIdx) return;
            const moved = this.items.splice(this.dragIndex, 1)[0];
            this.items.splice(targetIdx, 0, moved);
            if (this.items.length > 0) {
                this.items[0].is_sub = false;
            }
            this.dragIndex = null;
            this.overIndex = null;
        },

        moveUp(idx) {
            if (idx <= 0) return;
            const temp = this.items[idx];
            this.items[idx] = this.items[idx - 1];
            this.items[idx - 1] = temp;
            this.items[0].is_sub = false;
        },

        moveDown(idx) {
            if (idx >= this.items.length - 1) return;
            const temp = this.items[idx];
            this.items[idx] = this.items[idx + 1];
            this.items[idx + 1] = temp;
            this.items[0].is_sub = false;
        },

        makeSub(idx) {
            if (idx > 0) {
                this.items[idx].is_sub = true;
            }
        },

        makeMain(idx) {
            this.items[idx].is_sub = false;
        },

        async saveMenu() {
            this.saving = true;
            this.savedNotice = false;

            let currentParentId = null;
            const payload = this.items.map((item, idx) => {
                if (idx === 0 || !item.is_sub) {
                    item.is_sub = false;
                    currentParentId = item.id;
                    return {
                        id: item.id,
                        parent_id: null,
                        label: item.label,
                        url: item.url,
                        open_new_tab: item.open_new_tab
                    };
                } else {
                    return {
                        id: item.id,
                        parent_id: currentParentId,
                        label: item.label,
                        url: item.url,
                        open_new_tab: item.open_new_tab
                    };
                }
            });

            try {
                const res = await fetch('{{ route('admin.menus.save-tree') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ items: payload })
                });

                if (res.ok) {
                    this.savedNotice = true;
                    setTimeout(() => this.savedNotice = false, 3000);
                }
            } finally {
                this.saving = false;
            }
        }
    };
}
</script>
@endsection
