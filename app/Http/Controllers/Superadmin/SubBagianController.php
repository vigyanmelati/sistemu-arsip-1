<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Arsip;

class SubBagianController extends Controller
{
    public function index()
    {
        $subBagians = SubBagian::all();
        return view('superadmin.sub_bagians.index', compact('subBagians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_sub_bagian' => 'required|string|max:255'
        ]);

        SubBagian::create($request->all());

        return redirect()->back()->with('success', 'Sub Bagian berhasil ditambahkan');
    }

    public function update(Request $request, SubBagian $subBagian)
    {
        $request->validate([
            'nama_sub_bagian' => 'required|string|max:255'
        ]);

        $subBagian->update($request->all());

        return redirect()->back()->with('success', 'Sub Bagian berhasil diupdate');
    }

   public function destroy(SubBagian $subBagian)
{
    // Cek apakah masih dipakai oleh user
    $digunakanUser = User::where('sub_bagian_id', $subBagian->id)->exists();

    // Cek apakah masih dipakai oleh arsip
    $digunakanArsip = Arsip::where('sub_bagian_id', $subBagian->id)->exists();

    if ($digunakanUser || $digunakanArsip) {

        $pesan = 'Sub Bagian tidak dapat dihapus karena masih digunakan';

        if ($digunakanUser && $digunakanArsip) {
            $pesan .= ' oleh data User dan Arsip.';
        } elseif ($digunakanUser) {
            $pesan .= ' oleh data User.';
        } elseif ($digunakanArsip) {
            $pesan .= ' oleh data Arsip.';
        }

        return redirect()
            ->back()
            ->with('error', $pesan);
    }

    $subBagian->delete();

    return redirect()
        ->back()
        ->with('success', 'Sub Bagian berhasil dihapus.');
}
}
