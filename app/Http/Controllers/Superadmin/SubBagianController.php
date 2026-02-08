<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\SubBagian;
use Illuminate\Http\Request;

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
        $subBagian->delete();
        return redirect()->back()->with('success', 'Sub Bagian berhasil dihapus');
    }
}
