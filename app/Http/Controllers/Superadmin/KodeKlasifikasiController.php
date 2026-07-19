<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\KodeKlasifikasi;
use Illuminate\Http\Request;
use App\Models\Arsip;

class KodeKlasifikasiController extends Controller
{
    public function index()
    {
        $data = KodeKlasifikasi::all();
        return view('superadmin.kode_klasifikasis.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:50',
            'uraian' => 'required|string'
        ]);

        KodeKlasifikasi::create($request->all());

        return redirect()->back()->with('success', 'Kode klasifikasi berhasil ditambahkan');
    }

    public function update(Request $request, KodeKlasifikasi $kodeKlasifikasi)
    {
        $request->validate([
            'kode' => 'required|string|max:50',
            'uraian' => 'required|string'
        ]);

        $kodeKlasifikasi->update($request->all());

        return redirect()->back()->with('success', 'Kode klasifikasi berhasil diupdate');
    }

    public function destroy(KodeKlasifikasi $kodeKlasifikasi)
{
    // Cek apakah masih digunakan oleh arsip
    $digunakanArsip = Arsip::where(
        'kode_klasifikasi_id',
        $kodeKlasifikasi->id
    )->exists();

    if ($digunakanArsip) {
        return redirect()
            ->back()
            ->with(
                'error',
                'Kode klasifikasi tidak dapat dihapus karena masih digunakan oleh data arsip.'
            );
    }

    $kodeKlasifikasi->delete();

    return redirect()
        ->back()
        ->with(
            'success',
            'Kode klasifikasi berhasil dihapus.'
        );
}
}
