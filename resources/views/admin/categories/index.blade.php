@extends('layouts.admin')
@section('title', 'ক্যাটাগরি ও উপজেলা ম্যানেজার')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add Form -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 h-fit">
        <h2 class="font-bold text-slate-800 mb-4">নতুন ক্যাটাগরি / উপজেলা যোগ করুন</h2>
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block font-semibold mb-1">নাম *</label>
                <input type="text" name="name" placeholder="যেমন: জাতীয়, সিলেট, বিশ্বনাথ" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">প্যারেন্ট ক্যাটাগরি (ঐচ্ছিক)</label>
                <select name="parent_id" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">-- মূল ক্যাটাগরি --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold mb-1">ধরন</label>
                    <select name="type" class="w-full px-3 py-2 border rounded-lg">
                        <option value="news">News</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-1">সিরিয়াল (Order)</label>
                    <input type="number" name="sort_order" value="0" class="w-full px-3 py-2 border rounded-lg">
                </div>
            </div>
            <div>
                <label class="flex items-center gap-2 font-medium">
                    <input type="checkbox" name="show_in_menu" value="1" checked class="rounded text-emerald-600"> হেডার মেনুতে দেখান
                </label>
            </div>
            <div>
                <label class="block font-semibold mb-1">SEO Meta Title</label>
                <input type="text" name="meta_title" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700">সংরক্ষণ করুন</button>
        </form>
    </div>

    <!-- List -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b font-bold text-slate-800">সকল ক্যাটাগরি তালিকা</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b text-slate-600">
                    <tr>
                        <th class="px-5 py-3">নাম</th>
                        <th class="px-5 py-3">প্যারেন্ট</th>
                        <th class="px-5 py-3">মেনু</th>
                        <th class="px-5 py-3">সিরিয়াল</th>
                        <th class="px-5 py-3 text-right">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($categories as $cat)
                    <tr>
                        <td class="px-5 py-3 font-semibold">{{ $cat->name }} <span class="text-xs text-slate-400">({{ $cat->slug }})</span></td>
                        <td class="px-5 py-3">{{ $cat->parent?->name ?? '-' }}</td>
                        <td class="px-5 py-3">{!! $cat->show_in_menu ? '<span class="text-emerald-600 font-bold">হ্যাঁ</span>' : 'না' !!}</td>
                        <td class="px-5 py-3">{{ $cat->sort_order }}</td>
                        <td class="px-5 py-3 text-right">
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('ডিলিট করতে চান?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline">ডিলিট</button>
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
