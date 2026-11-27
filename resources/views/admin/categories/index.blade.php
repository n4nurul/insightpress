@extends('layouts.admin')
@section('title', 'Categories & Areas Manager')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add Form -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 h-fit">
        <h2 class="font-bold text-slate-800 mb-4">Add New Category / Area</h2>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block font-semibold mb-1">Name *</label>
                <input type="text" name="name" placeholder="e.g. National, Sylhet, Bishwanath" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">Parent Category (Optional)</label>
                <select name="parent_id" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">-- Root Category --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border rounded-lg">
                        <option value="news">News</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Sort Order</label>
                    <input type="number" name="sort_order" value="0" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            <div>
                <label class="flex items-center gap-2 font-medium">
                    <input type="checkbox" name="show_in_menu" value="1" checked class="rounded text-emerald-600"> Show in Header Menu
                </label>
            </div>
            <div>
                <label class="block font-semibold mb-1">SEO Meta Title</label>
                <input type="text" name="meta_title" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700">Save Category</button>
        </form>
    </div>

    <!-- List -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b font-bold text-slate-800">All Categories</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Parent</th>
                        <th class="px-5 py-3">In Menu</th>
                        <th class="px-5 py-3">Order</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($categories as $cat)
                    <tr>
                        <td class="px-5 py-3 font-semibold">{{ $cat->name }} <span class="text-xs text-slate-400">({{ $cat->slug }})</span></td>
                        <td class="px-5 py-3">{{ $cat->parent?->name ?? '-' }}</td>
                        <td class="px-5 py-3">{!! $cat->show_in_menu ? '<span class="text-emerald-600 font-bold">Yes</span>' : 'No' !!}</td>
                        <td class="px-5 py-3">{{ $cat->sort_order }}</td>
                        <td class="px-5 py-3 text-right">
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
