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
use Illuminate\Support\Facades\DB;

class SuratMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index(Request $request)
{
    $search = $request->input('search');
    $filter = $request->filter;

    $query = SuratMasuk::query();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('nomor_dokumen', 'like', "%{$search}%")
                ->orWhere('perihal', 'like', "%{$search}%")
                ->orWhere('instansi_satker', 'like', "%{$search}%");
        });
    }

    // Ambil semua nomor dokumen yang duplikat
    $duplicateNomor = SuratMasuk::select('nomor_dokumen')
        ->groupBy('nomor_dokumen')
        ->havingRaw('COUNT(*) > 1')
        ->pluck('nomor_dokumen');

    // Filter hanya data duplikat
    if ($filter == 'duplikasi') {
        $query->whereIn('nomor_dokumen', $duplicateNomor);
    }

    $surat = $query
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    // jumlah data yang duplikat
    $jumlahDuplikat = SuratMasuk::whereIn(
        'nomor_dokumen',
        $duplicateNomor
    )->count();

    // total masing-masing nomor dokumen
    $duplicateCounts = SuratMasuk::selectRaw(
        'nomor_dokumen, COUNT(*) as total'
    )
        ->groupBy('nomor_dokumen')
        ->pluck('total', 'nomor_dokumen');

    return view(
        'surat_masuk.index',
        compact(
            'surat',
            'duplicateCounts',
            'jumlahDuplikat'
        )
    );
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
                'sub_bagian_id'        => 'required',
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
                ->route('surat-masuk.index')
                ->with('success', 'Surat masuk berhasil ditambahkan.');

        } catch (\Exception $e) {

            Log::error('Gagal simpan surat masuk: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Data gagal disimpan!');
        }
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
            'instansi_satker'      => 'required',
            'tanggal_dokumen'      => 'required|date',
            'tanggal_penyelesaian' => 'required|date',
            'nomor_dokumen'        => 'required',
            'nomor_agenda'         => 'required',
            'kepada'               => 'required',
            'perihal'              => 'required',
            'file_input'           => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'sub_bagian_id'        => 'required',
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

        if ($surat->file_input) {
            Storage::delete('surat_masuk/' . $surat->file_input);
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

        $pdf = Pdf::loadView('surat_masuk.disposisi', compact('surat'));

        return $pdf->stream('disposisi.pdf');
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

    try {

        /*
        |--------------------------------------------------------------------------
        | BACA EXCEL
        |--------------------------------------------------------------------------
        */

        $rows = Excel::toArray(
            new SuratMasukImport(),
            $request->file('file')
        );
// dd($rows);
        $rows = $rows[0];

        $validatorImport = new SuratMasukImport();

        $validatorImport->validateExcel($rows);
   

        /*
        |--------------------------------------------------------------------------
        | ADA ERROR
        |--------------------------------------------------------------------------
        */

      if (!empty($validatorImport->errors)) {

    return redirect()
        ->back()
        ->withInput()
        ->with([
            'error' => 'Import dibatalkan karena terdapat data yang tidak valid.',
            'import_errors' => $validatorImport->errors,
        ]);
}

        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA ERROR
        |--------------------------------------------------------------------------
        */

        $import = new SuratMasukImport();

        Excel::import(
            $import,
            $request->file('file')
        );

        return redirect()
            ->route('surat-masuk.index')
            ->with(
                'success',
                'Data surat masuk berhasil diimport.'
            );

    } catch (\Exception $e) {

        \Log::error($e->getMessage());

        return back()->with(
            'error',
            'Import gagal : ' . $e->getMessage()
        );
    }
}

public function cekDuplikasi()
{
    $duplicates = SuratMasuk::select(
            'nomor_dokumen',
            'tanggal_dokumen',
            DB::raw('COUNT(*) as total')
        )
        ->groupBy(
            'nomor_dokumen',
            'tanggal_dokumen'
        )
        ->having('total', '>', 1)
        ->get();

    $duplicateData = [];

    foreach ($duplicates as $duplicate) {

        $surats = SuratMasuk::with('subBagian')
            ->where('nomor_dokumen', $duplicate->nomor_dokumen)
            ->whereDate(
                'tanggal_dokumen',
                $duplicate->tanggal_dokumen
            )
            ->get();

        $duplicateData[] = [
            'nomor_dokumen' => $duplicate->nomor_dokumen,
            'tanggal_dokumen' => $duplicate->tanggal_dokumen,
            'total' => $duplicate->total,
            'data' => $surats
        ];
    }

    return view(
        'surat_masuk.cek_duplikasi',
        compact('duplicateData')
    );
}


}