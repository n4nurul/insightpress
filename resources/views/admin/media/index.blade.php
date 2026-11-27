@extends('layouts.admin')
@section('title', 'Media Library')

@section('content')
<div class="bg-white p-5 rounded-xl border border-slate-200 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="font-bold text-slate-800">Uploaded Media Files ({{ count($files) }})</h2>
        <p class="text-xs text-slate-500 mt-0.5">Copy image URLs to reuse inside posts, pages, or custom HTML slots.</p>
    </div>

    <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 text-sm">
        @csrf
        <input type="file" name="image" accept="image/*" required class="text-xs border rounded-lg p-1.5 bg-slate-50">
        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg">
            + Upload Image
        </button>
    </form>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4" x-data="{ copied: '' }">
    @forelse($files as $file)
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col shadow-sm group">
        <div class="aspect-square bg-slate-100 overflow-hidden relative">
            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition">
            <span class="absolute bottom-1.5 right-1.5 px-1.5 py-0.5 bg-slate-900/75 text-white text-[10px] rounded">
                {{ $file['size'] }}
            </span>
        </div>

        <div class="p-2.5 flex-1 flex flex-col justify-between text-xs">
            <div class="truncate font-medium text-slate-700 mb-2" title="{{ $file['path'] }}">
                {{ $file['name'] }}
            </div>

            <div class="flex items-center justify-between gap-1.5 pt-2 border-t border-slate-100">
                <button type="button"
                        @click="navigator.clipboard.writeText('{{ $file['url'] }}'); copied = '{{ $file['path'] }}'; setTimeout(() => copied = '', 2000)"
                        class="flex-1 py-1 px-2 rounded bg-slate-100 hover:bg-emerald-600 hover:text-white font-semibold text-slate-700 transition text-center">
                    <span x-show="copied !== '{{ $file['path'] }}'">Copy URL</span>
                    <span x-show="copied === '{{ $file['path'] }}'" class="text-emerald-600 group-hover:text-white">✔ Copied</span>
                </button>

                @if(auth()->user()->isEditor())
                <form action="{{ route('admin.media.destroy') }}" method="POST" onsubmit="return confirm('Permanently delete this image?')">
                    @csrf @method('DELETE')
                    <input type="hidden" name="path" value="{{ $file['path'] }}">
                    <button type="submit" class="py-1 px-2 rounded bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white font-semibold transition" title="Delete">
                        🗑
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white p-12 rounded-xl border border-dashed border-slate-300 text-center text-slate-400 text-sm">
        No images uploaded yet.
    </div>
    @endforelse
</div>
@endsection
