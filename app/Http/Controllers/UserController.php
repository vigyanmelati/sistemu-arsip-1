<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }
    
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);
        return view('users.index', compact('users'));
    }
    
    public function create()
    {
        return view('users.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,user',
        ]);
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        
        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }
    
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }
    
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,admin,user',
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];
        
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }
    
    public function destroy(User $user)
    {
        // Tidak boleh hapus diri sendiri
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}