@extends('layouts.admin')
@section('title', 'Users & Roles Management')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add New User Form -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 h-fit">
        <h2 class="font-bold text-slate-800 mb-4">Add New User</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 text-sm">
            @csrf
            <div>
                <label class="block font-semibold mb-1">Full Name *</label>
                <input type="text" name="name" placeholder="e.g. Kamal Ahmed" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">Email Address *</label>
                <input type="email" name="email" placeholder="user@domain.com" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">User Type (Role) *</label>
                <select name="role" required class="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="reporter">Reporter (Submit Drafts Only)</option>
                    <option value="editor">Editor (Publish & Manage Content)</option>
                    <option value="admin">Super Admin (Full Access)</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Password (Min 6 chars) *</label>
                <input type="password" name="password" placeholder="••••••••" required class="w-full px-3 py-2 border rounded-lg">
            </div>
            <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700">
                Create User
            </button>
        </form>
    </div>

    <!-- All Users List -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b font-bold text-slate-800">All Registered Users ({{ $users->count() }})</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Name & Email</th>
                        <th class="px-4 py-3">User Type</th>
                        <th class="px-4 py-3">Reset Password</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($users as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-800">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="ml-1 px-2 py-0.5 text-[10px] bg-emerald-100 text-emerald-700 rounded-full">You</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($user->id === auth()->id())
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-purple-100 text-purple-700">
                                    {{ $user->role }}
                                </span>
                            @else
                                <form action="{{ route('admin.users.role', $user) }}" method="POST" class="flex items-center gap-1">
                                    @csrf
                                    <select name="role" onchange="this.form.submit()" class="px-2 py-1 text-xs font-semibold border rounded bg-slate-50">
                                        <option value="reporter" {{ $user->role === 'reporter' ? 'selected' : '' }}>Reporter</option>
                                        <option value="editor" {{ $user->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </form>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.users.password', $user) }}" method="POST" class="flex items-center gap-1">
                                @csrf
                                <input type="password" name="password" placeholder="New pass" required class="w-24 px-2 py-1 text-xs border rounded">
                                <button class="px-2 py-1 bg-slate-800 text-white text-xs rounded hover:bg-slate-700">Save</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline font-medium text-xs">Delete</button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400">Active</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
