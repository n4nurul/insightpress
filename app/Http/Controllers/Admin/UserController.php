<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data =$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,editor,reporter',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function updateRole(Request $request, User$user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot change your own role.']);
        }

        $request->validate(['role' => 'required|in:admin,editor,reporter']);
        $user->update(['role' =>$request->role]);

        return back()->with('success', 'User role updated.');
    }

    public function updatePassword(Request $request, User$user)
    {
        $request->validate(['password' => 'required|string|min:6']);
        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) { return back()->withErrors(['error' => 'You cannot delete your own active account.']); }$user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
