<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SubBagian;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);
        $subBagians = SubBagian::orderBy('nama_sub_bagian')->get();

        return view('superadmin.users.index', compact('users', 'subBagians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,superadmin,user,tu',
            'sub_bagian_id' => 'nullable|required_if:role,user|exists:sub_bagians,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'sub_bagian_id' => $request->role === 'user' ? $request->sub_bagian_id : null,
        ]);

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,superadmin,user,tu',
            'sub_bagian_id' => 'nullable|required_if:role,user|exists:sub_bagians,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'sub_bagian_id' => $request->role === 'user' ? $request->sub_bagian_id : null,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:8',
            ]);

            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->back()
                ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()
            ->route('superadmin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
