<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth()->user()->role == 'user') {

            $surat = SuratMasuk::with('subBagian')
                ->where('sub_bagian_id', auth()->user()->sub_bagian_id)
                ->latest()
                ->get();

        } else {

            $surat = SuratMasuk::with('subBagian')
                ->latest()
                ->get();
        }

        return view('surat_masuk.index', compact('surat'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subBagians = SubBagian::all();

        return view('surat_masuk.create', compact('subBagians'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat'     => 'required',
            'tanggal_surat'   => 'required|date',
            'pengirim'        => 'required',
            'perihal'         => 'required',
            'file_surat'      => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'sub_bagian_id'   => 'nullable',
        ]);

        $fileName = null;

        if ($request->hasFile('file_surat')) {
            $fileName = time() . '_' . $request->file('file_surat')->getClientOriginalName();

            $request->file('file_surat')->storeAs(
                'public/surat_masuk',
                $fileName
            );
        }

        SuratMasuk::create([
            'nomor_surat'   => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'pengirim'      => $request->pengirim,
            'perihal'       => $request->perihal,
            'keterangan'    => $request->keterangan,

            // AUTO SUB BAGIAN
            'sub_bagian_id' => auth()->user()->role == 'user'
                ? auth()->user()->sub_bagian_id
                : $request->sub_bagian_id,

            'file_surat'    => $fileName,
        ]);

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $surat = SuratMasuk::with('subBagian')->findOrFail($id);

        $this->checkAccess($surat);

        return view('surat_masuk.show', compact('surat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        $subBagians = SubBagian::all();

        return view('surat_masuk.edit', compact('surat', 'subBagians'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        $request->validate([
            'nomor_surat'     => 'required',
            'tanggal_surat'   => 'required|date',
            'pengirim'        => 'required',
            'perihal'         => 'required',
            'file_surat'      => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'sub_bagian_id'   => 'nullable',
        ]);

        $fileName = $surat->file_surat;

        if ($request->hasFile('file_surat')) {

            // hapus file lama
            if ($surat->file_surat) {
                Storage::delete('public/surat_masuk/' . $surat->file_surat);
            }

            $fileName = time() . '_' . $request->file('file_surat')->getClientOriginalName();

            $request->file('file_surat')->storeAs(
                'public/surat_masuk',
                $fileName
            );
        }

        $surat->update([
            'nomor_surat'   => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'pengirim'      => $request->pengirim,
            'perihal'       => $request->perihal,
            'keterangan'    => $request->keterangan,

            // AUTO SUB BAGIAN
            'sub_bagian_id' => auth()->user()->role == 'user'
                ? auth()->user()->sub_bagian_id
                : $request->sub_bagian_id,

            'file_surat'    => $fileName,
        ]);

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        if ($surat->file_surat) {
            Storage::delete('public/surat_masuk/' . $surat->file_surat);
        }

        $surat->delete();

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }

    /**
     * Disposisi surat
     */
    public function disposisi($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        return view('surat_masuk.disposisi', compact('surat'));
    }

    /**
     * CHECK ACCESS
     */
    private function checkAccess($surat)
    {
        if (
            auth()->user()->role == 'user' &&
            $surat->sub_bagian_id != auth()->user()->sub_bagian_id
        ) {
            abort(403);
        }
    }
}