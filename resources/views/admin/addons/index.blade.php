@extends('layouts.admin')
@section('title', 'Addons & Optional Modules')

@section('content')
<div class="mb-5 bg-white p-5 rounded-xl border border-slate-200">
    <h2 class="font-bold text-slate-800">InsightPress Modular Addons</h2>
    <p class="text-xs text-slate-500 mt-1">Enable or disable optional features per client requirement. Deactivated modules stay hidden from the dashboard and frontend.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach($addons as $key => $addon)
        @php $isActive = cms_addon($key, $addon['default']); @endphp
        <div class="bg-white rounded-xl border {{ $isActive ? 'border-emerald-300 shadow-sm' : 'border-slate-200 opacity-85' }} p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 text-slate-600">
                        {{ $addon['category'] }}
                    </span>
                    <span class="px-2.5 py-0.5 text-[11px] font-bold rounded-full {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                        {{ $isActive ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h3 class="font-bold text-slate-900 text-base mb-1.5">{{ $addon['name'] }}</h3>
                <p class="text-xs text-slate-600 leading-relaxed mb-5">{{ $addon['description'] }}</p>
            </div>

            <form action="{{ route('admin.addons.toggle', $key) }}" method="POST" class="pt-3 border-t border-slate-100">
                @csrf
                <button type="submit"
                        class="w-full py-2 px-4 rounded-lg text-xs font-bold transition {{ $isActive ? 'bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                    {{ $isActive ? 'Deactivate Addon' : 'Activate Addon' }}
                </button>
            </form>
        </div>
    @endforeach
</div>
@endsection
