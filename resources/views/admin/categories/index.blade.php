@extends('layouts.admin')
@section('title', 'Categories & Areas Manager')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6"
     x-data="{
        editMode: false,
        actionUrl: '{{ route('admin.categories.store') }}',
        form: {
            id: '',
            name: '',
            parent_id: '',
            type: 'news',
            sort_order: 0,
            show_in_menu: true,
            meta_title: ''
        },
        startEdit(cat) {
            this.editMode = true;
            this.actionUrl = '{{ url('admin/categories') }}/' + cat.id;
            this.form = {
                id: cat.id,
                name: cat.name,
                parent_id: cat.parent_id ? String(cat.parent_id) : '',
                type: cat.type || 'news',
                sort_order: cat.sort_order ?? 0,
                show_in_menu: Boolean(cat.show_in_menu),
                meta_title: cat.meta_title || ''
            };
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        resetForm() {
            this.editMode = false;
            this.actionUrl = '{{ route('admin.categories.store') }}';
            this.form = {
                id: '',
                name: '',
                parent_id: '',
                type: 'news',
                sort_order: 0,
                show_in_menu: true,
                meta_title: ''
            };
        }
     }">

    <!-- Left Form (Add / Edit Category) -->
    <div class="lg:col-span-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-slate-900" x-text="editMode ? 'Edit Category / Area' : 'Add New Category / Area'"></h2>
                <button type="button" x-show="editMode" @click="resetForm()" style="display: none;"
                        class="text-xs font-semibold text-rose-600 hover:underline">
                    ✕ Cancel Edit
                </button>
            </div>

            <form :action="actionUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Name *</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. National, Sylhet, Bishwanath"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Parent Category (Optional)</label>
                    <select name="parent_id" x-model="form.parent_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-600">
                        <option value="">-- Root Category --</option>
                        @foreach($parents as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Type</label>
                        <select name="type" x-model="form.type"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-600">
                            <option value="news">News</option>
                            <option value="service">Service</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" x-model="form.sort_order"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-600">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 cursor-pointer pt-1">
                    <input type="checkbox" name="show_in_menu" value="1" x-model="form.show_in_menu"
                           class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <span>Show in Header Menu</span>
                </label>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">SEO Meta Title</label>
                    <input type="text" name="meta_title" x-model="form.meta_title"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-emerald-600">
                </div>

                <button type="submit"
                        :class="editMode ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-700 hover:bg-emerald-800'"
                        class="w-full py-3 rounded-xl text-white font-bold text-sm transition cursor-pointer"
                        x-text="editMode ? 'Update Category' : 'Save Category'">
                </button>
            </form>
        </div>
    </div>

    <!-- Right Table (All Categories) -->
    <div class="lg:col-span-8">
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">All Categories</h2>
                <span class="text-xs text-slate-500">Total: {{ $categories->count() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Name</th>
                            <th class="px-4 py-3.5">Parent</th>
                            <th class="px-4 py-3.5">Order</th>
                            <th class="px-5 py-3.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5 font-bold text-slate-900">
                                {{ $cat->name }}
                                
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $cat->parent?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-semibold text-slate-700">{{ $cat->sort_order }}</td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-3">
                                <button type="button"
                                        @click='startEdit(@json($cat))'
                                        class="text-blue-600 hover:text-blue-800 font-semibold text-xs cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-xs cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-400">No categories found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
