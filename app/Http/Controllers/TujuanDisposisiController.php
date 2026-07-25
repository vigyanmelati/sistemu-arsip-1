<?php

namespace App\Http\Controllers;

use App\Models\TujuanDisposisi;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TujuanDisposisiController extends Controller
{
    public function index()
    {
        return view('surat_masuk.master.tujuan', ['items' => TujuanDisposisi::orderBy('nama_tujuan')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nama_tujuan' => 'required|string|max:255|unique:tujuan_disposisis,nama_tujuan']);
        TujuanDisposisi::create($data + ['aktif' => true, 'created_by' => auth()->id()]);
        return back()->with('success', 'Tujuan disposisi berhasil ditambahkan.');
    }

    public function update(Request $request, TujuanDisposisi $tujuan)
    {
        $data = $request->validate([
            'nama_tujuan' => ['required', 'string', 'max:255', Rule::unique('tujuan_disposisis', 'nama_tujuan')->ignore($tujuan)],
            'aktif' => 'nullable|boolean',
        ]);
        $tujuan->update(['nama_tujuan' => $data['nama_tujuan'], 'aktif' => $request->boolean('aktif')]);
        return back()->with('success', 'Tujuan disposisi berhasil diperbarui.');
    }
}
