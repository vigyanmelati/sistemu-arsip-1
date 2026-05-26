<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SuratMasukExport;
use App\Imports\SuratMasukImport;
use Maatwebsite\Excel\Facades\Excel;



class SubBagianSuratMasukController extends Controller
{
    /**
     * LIST DATA
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $surat = SuratMasuk::with('subBagian')

            // FILTER BERDASARKAN SUB BAGIAN USER LOGIN
            ->when(auth()->user()->role == 'user', function ($query) {
                $query->where(
                    'sub_bagian_id',
                    auth()->user()->sub_bagian_id
                );
            })

            // SEARCH
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_dokumen', 'like', "%{$search}%")
                        ->orWhere('perihal', 'like', "%{$search}%")
                        ->orWhere('instansi_satker', 'like', "%{$search}%");
                });
            })

            ->latest()
            ->paginate(10);

        return view('subbagian.surat_masuk.index', compact('surat'));
    }

    /**
     * FORM CREATE
     */
    public function create()
    {
        $subBagians = SubBagian::all();

        return view('subbagian.surat_masuk.create', compact('subBagians'));
    }

    /**
     * SIMPAN DATA
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'instansi_satker'      => 'required',
                'tanggal_dokumen'      => 'required|date',
                'tanggal_penyelesaian' => 'required|date',
                'nomor_dokumen'        => 'required',
                'nomor_agenda'         => 'required',
                'kepada'               => 'required',
                'perihal'              => 'required',
                'file_input'           => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
                'sub_bagian_id'        => 'nullable',
            ]);

            $fileName = null;

            if ($request->hasFile('file_input')) {

                $fileName = time() . '_' .
                    $request->file('file_input')->getClientOriginalName();

                $request->file('file_input')->storeAs(
                    'surat_masuk',
                    $fileName
                );
            }

            SuratMasuk::create([

                // USER HANYA BISA SIMPAN KE SUB BAGIANNYA SENDIRI
                'sub_bagian_id' => auth()->user()->role == 'user'
                    ? auth()->user()->sub_bagian_id
                    : $request->sub_bagian_id,

                'instansi_satker'      => $request->instansi_satker,
                'tanggal_dokumen'      => $request->tanggal_dokumen,
                'tanggal_penyelesaian' => $request->tanggal_penyelesaian,
                'nomor_dokumen'        => $request->nomor_dokumen,
                'nomor_agenda'         => $request->nomor_agenda,
                'kepada'               => $request->kepada,
                'perihal'              => $request->perihal,
                'catatan'              => $request->catatan,
                'file_input'           => $fileName,
            ]);

            return redirect()
                ->route('subbagian.surat-masuk.index')
                ->with('success', 'Surat masuk berhasil ditambahkan.');

        } catch (\Exception $e) {

            Log::error('Gagal simpan surat masuk: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Data gagal disimpan!');
        }
    }

    /**
     * DETAIL
     */
    public function show($id)
    {
        $surat = SuratMasuk::with('subBagian')->findOrFail($id);

        $this->checkAccess($surat);

        return view('subbagian.surat_masuk.show', compact('surat'));
    }

    /**
     * FORM EDIT
     */
    public function edit($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        $subBagians = SubBagian::all();

        return view('subbagian.surat_masuk.edit', compact('surat', 'subBagians'));
    }

    /**
     * UPDATE
     */
    public function update(Request $request, $id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        $request->validate([
            'instansi_satker'      => 'required',
            'tanggal_dokumen'      => 'required|date',
            'tanggal_penyelesaian' => 'required|date',
            'nomor_dokumen'        => 'required',
            'nomor_agenda'         => 'required',
            'kepada'               => 'required',
            'perihal'              => 'required',
            'file_input'           => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'sub_bagian_id'        => 'nullable',
        ]);

        $fileName = $surat->file_input;

        if ($request->hasFile('file_input')) {

            if ($surat->file_input) {
                Storage::delete('surat_masuk/' . $surat->file_input);
            }

            $fileName = time() . '_' .
                $request->file('file_input')->getClientOriginalName();

            $request->file('file_input')->storeAs(
                'surat_masuk',
                $fileName
            );
        }

        $surat->update([

            // USER TIDAK BISA UBAH SUB BAGIAN
            'sub_bagian_id' => auth()->user()->role == 'user'
                ? auth()->user()->sub_bagian_id
                : $request->sub_bagian_id,

            'instansi_satker'      => $request->instansi_satker,
            'tanggal_dokumen'      => $request->tanggal_dokumen,
            'tanggal_penyelesaian' => $request->tanggal_penyelesaian,
            'nomor_dokumen'        => $request->nomor_dokumen,
            'nomor_agenda'         => $request->nomor_agenda,
            'kepada'               => $request->kepada,
            'perihal'              => $request->perihal,
            'catatan'              => $request->catatan,
            'file_input'           => $fileName,
        ]);

        return redirect()
            ->route('subbagian.surat-masuk.index')
            ->with('success', 'Surat masuk berhasil diperbarui.');
    }

    /**
     * HAPUS
     */
    public function destroy($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        if ($surat->file_input) {
            Storage::delete('surat_masuk/' . $surat->file_input);
        }

        $surat->delete();

        return redirect()
            ->route('subbagian.surat-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }

    /**
     * CETAK DISPOSISI PDF
     */
    public function disposisi($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $this->checkAccess($surat);

        $pdf = Pdf::loadView(
            'subbagian.surat_masuk.disposisi',
            compact('surat')
        );

        return $pdf->stream('disposisi.pdf');
    }

    /**
     * CHECK AKSES
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

        public function export()
{
    return Excel::download(
        new SuratMasukExport,
        'surat_masuk.xlsx'
    );
}

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    Excel::import(
        new SuratMasukImport,
        $request->file('file')
    );

    return redirect()
        ->route('subbagian.surat-masuk.index')
        ->with('success', 'Data berhasil diimport.');
}

}