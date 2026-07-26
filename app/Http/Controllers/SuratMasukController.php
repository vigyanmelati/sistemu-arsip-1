<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\SuratInstansi;
use App\Models\TujuanDisposisi;
use App\Models\SinarV1Document;
use App\Models\SinarV1Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SuratMasukExport;
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

    $query = SuratMasuk::with(['instansi', 'tujuanDisposisis']);

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

    $jumlahHistorisV1 = SinarV1Document::where('legacy_category_id', 12)->count();
    $jumlahSudahDiimport = SuratMasuk::whereNotNull('sinar_v1_document_id')->count();
    $jumlahInstansiHistoris = SinarV1Instansi::count();

    return view(
        'surat_masuk.index',
        compact(
            'surat',
            'duplicateCounts',
            'jumlahDuplikat'
            , 'jumlahHistorisV1'
            , 'jumlahSudahDiimport'
            , 'jumlahInstansiHistoris'
        )
    );
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $instansis = SuratInstansi::where('aktif', true)->orderBy('nama_instansi')->get();
        $tujuanDisposisis = TujuanDisposisi::where('aktif', true)->orderBy('nama_tujuan')->get();
        return view('surat_masuk.create', compact('instansis', 'tujuanDisposisis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'instansi_id'          => 'required|exists:surat_instansis,id',
                'tujuan_disposisi_ids' => 'nullable|array',
                'tujuan_disposisi_ids.*' => 'distinct|exists:tujuan_disposisis,id',
                'tanggal_dokumen'      => 'required|date',
                'tanggal_penyelesaian' => 'required|date',
                'nomor_dokumen'        => 'required',
                'nomor_agenda'         => 'required',
                'kepada'               => 'required',
                'perihal'              => 'required',
                'file_input'           => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
                'submit_action'        => 'nullable|in:save,save_print',
                'allow_duplicate'      => 'nullable|boolean',
            ]);

            $instansi = SuratInstansi::findOrFail($request->instansi_id);
            $duplicate = $this->findPotentialDuplicate($request);
            if ($duplicate && ! $request->boolean('allow_duplicate')) {
                return back()->withInput()->withErrors([
                    'duplicate' => 'Surat yang berpotensi sama sudah tercatat. Periksa data tersebut atau centang konfirmasi untuk tetap menyimpan.',
                ])->with('potential_duplicate', $duplicate);
            }

            $fileName = null;

            if ($request->hasFile('file_input')) {

                $fileName = time() . '_' .
                    $request->file('file_input')->getClientOriginalName();

                $request->file('file_input')->storeAs(
                    'surat_masuk',
                    $fileName
                );
            }

            $surat = SuratMasuk::create([
                'sub_bagian_id'        => null,
                'instansi_id'          => $instansi->id,
                'instansi_satker'      => $instansi->nama_instansi,
                'tanggal_dokumen'      => $request->tanggal_dokumen,
                'tanggal_penyelesaian' => $request->tanggal_penyelesaian,
                'nomor_dokumen'        => $request->nomor_dokumen,
                'nomor_agenda'         => $request->nomor_agenda,
                'kepada'               => $request->kepada,
                'perihal'              => $request->perihal,
                'catatan'              => $request->catatan,
                'file_input'           => $fileName,
            ]);
            $surat->tujuanDisposisis()->sync($request->input('tujuan_disposisi_ids', []));

            if ($request->input('submit_action') === 'save_print') {
                return redirect()->route('surat-masuk.disposisi', $surat->id);
            }

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

    public function checkPotentialDuplicate(Request $request)
    {
        $request->validate([
            'instansi_id' => 'required|exists:surat_instansis,id',
            'tanggal_dokumen' => 'required|date',
            'nomor_dokumen' => 'required|string|max:255',
        ]);

        $duplicate = $this->findPotentialDuplicate($request);

        return response()->json([
            'duplicate' => (bool) $duplicate,
            'data' => $duplicate ? [
                'id' => $duplicate->id,
                'nomor_agenda' => $duplicate->nomor_agenda,
                'nomor_dokumen' => $duplicate->nomor_dokumen,
                'tanggal_dokumen' => optional($duplicate->tanggal_dokumen)->format('d-m-Y'),
                'instansi' => $duplicate->instansi_satker,
                'perihal' => $duplicate->perihal,
                'detail_url' => route('surat-masuk.show', $duplicate->id),
            ] : null,
        ]);
    }

    private function findPotentialDuplicate(Request $request): ?SuratMasuk
    {
        return SuratMasuk::where('instansi_id', $request->input('instansi_id'))
            ->whereDate('tanggal_dokumen', $request->input('tanggal_dokumen'))
            ->whereRaw('LOWER(TRIM(nomor_dokumen)) = ?', [mb_strtolower(trim((string) $request->input('nomor_dokumen')))])
            ->first();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $surat = SuratMasuk::with(['subBagian', 'instansi', 'tujuanDisposisis'])->findOrFail($id);

        return view('surat_masuk.show', compact('surat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $instansis = SuratInstansi::where('aktif', true)
            ->when($surat->instansi_id, fn ($query, $instansiId) => $query->orWhere('id', $instansiId))
            ->orderBy('nama_instansi')
            ->get();
        $tujuanDisposisis = TujuanDisposisi::where('aktif', true)
            ->orWhereIn('id', $surat->tujuanDisposisis()->pluck('tujuan_disposisis.id'))
            ->orderBy('nama_tujuan')->get();
        return view('surat_masuk.edit', compact('surat', 'instansis', 'tujuanDisposisis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $surat = SuratMasuk::findOrFail($id);

        $request->validate([
            'instansi_id'          => 'required|exists:surat_instansis,id',
            'tujuan_disposisi_ids' => 'nullable|array',
            'tujuan_disposisi_ids.*' => 'distinct|exists:tujuan_disposisis,id',
            'tanggal_dokumen'      => 'required|date',
            'tanggal_penyelesaian' => 'required|date',
            'nomor_dokumen'        => 'required',
            'nomor_agenda'         => 'required',
            'kepada'               => 'required',
            'perihal'              => 'required',
            'file_input'           => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $instansi = SuratInstansi::findOrFail($request->instansi_id);

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
            'instansi_id'          => $instansi->id,
            'instansi_satker'      => $instansi->nama_instansi,
            'tanggal_dokumen'      => $request->tanggal_dokumen,
            'tanggal_penyelesaian' => $request->tanggal_penyelesaian,
            'nomor_dokumen'        => $request->nomor_dokumen,
            'nomor_agenda'         => $request->nomor_agenda,
            'kepada'               => $request->kepada,
            'perihal'              => $request->perihal,
            'catatan'              => $request->catatan,
            'file_input'           => $fileName,
        ]);
        $surat->tujuanDisposisis()->sync($request->input('tujuan_disposisi_ids', []));

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
        $surat = SuratMasuk::with('tujuanDisposisis')->findOrFail($id);

        return view('surat_masuk.disposisi_preview', compact('surat'));
    }

    public function disposisiPdf($id)
    {
        $surat = SuratMasuk::with('tujuanDisposisis')->findOrFail($id);

        $pdf = Pdf::loadView('surat_masuk.disposisi', compact('surat'));

        return $pdf->stream('disposisi-surat-'.$surat->id.'.pdf');
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
    $request->validate(['confirmation' => 'accepted']);

    $stats = ['instansi_baru' => 0, 'surat_baru' => 0, 'surat_diperbarui' => 0, 'file_disalin' => 0];

    try {
        DB::transaction(function () use (&$stats) {
            SinarV1Instansi::orderBy('id')->chunk(200, function ($items) use (&$stats) {
                foreach ($items as $legacy) {
                    $instansi = SuratInstansi::firstOrNew(['nama_instansi' => trim($legacy->nama_instansi)]);
                    $isNew = ! $instansi->exists;
                    foreach (['alamat', 'telepon', 'fax', 'email', 'website'] as $field) {
                        if ($legacy->{$field} !== null && $legacy->{$field} !== '') {
                            $instansi->{$field} = $legacy->{$field};
                        }
                    }
                    $instansi->aktif = true;
                    $instansi->created_by ??= auth()->id();
                    $instansi->save();
                    $stats['instansi_baru'] += $isNew ? 1 : 0;
                }
            });

            SinarV1Document::where('legacy_category_id', 12)->orderBy('id')->chunk(200, function ($documents) use (&$stats) {
                foreach ($documents as $legacy) {
                    $namaInstansi = trim($legacy->instansi_satker ?: 'Instansi tidak tercatat');
                    $instansi = SuratInstansi::firstOrCreate(
                        ['nama_instansi' => $namaInstansi],
                        ['aktif' => true, 'created_by' => auth()->id()]
                    );

                    $surat = SuratMasuk::firstOrNew(['sinar_v1_document_id' => $legacy->id]);
                    $isNew = ! $surat->exists;
                    $fileName = $surat->file_input;
                    if ($legacy->file_path && Storage::disk('local')->exists($legacy->file_path)) {
                        $fileName = 'sinar_v1_'.$legacy->id.'_'.basename($legacy->file_name_original ?: $legacy->file_path);
                        Storage::disk('public')->put('surat_masuk/'.$fileName, Storage::disk('local')->get($legacy->file_path));
                        $stats['file_disalin']++;
                    }

                    $tanggalDokumen = $legacy->tanggal_dokumen ?: $legacy->tanggal_penyelesaian ?: $legacy->legacy_created_at?->toDateString() ?: now()->toDateString();
                    $surat->fill([
                        'sub_bagian_id' => $legacy->sub_bagian_id,
                        'instansi_id' => $instansi->id,
                        'instansi_satker' => $instansi->nama_instansi,
                        'tanggal_dokumen' => $tanggalDokumen,
                        'tanggal_penyelesaian' => $legacy->tanggal_penyelesaian ?: $tanggalDokumen,
                        'nomor_dokumen' => $legacy->nomor_dokumen ?: 'TANPA-NOMOR-V1-'.$legacy->legacy_id,
                        'nomor_agenda' => $legacy->nomor_agenda ?: 'V1-'.$legacy->legacy_id,
                        'kepada' => $legacy->kepada ?: 'KPU Provinsi Bali',
                        'perihal' => $legacy->perihal ?: 'Surat Masuk SINAR V1',
                        'catatan' => $legacy->catatan,
                        'file_input' => $fileName,
                    ])->save();

                    $stats[$isNew ? 'surat_baru' : 'surat_diperbarui']++;
                }
            });
        });

        return redirect()->route('surat-masuk.index')->with('success',
            "Import selesai: {$stats['instansi_baru']} instansi baru, {$stats['surat_baru']} surat baru, {$stats['surat_diperbarui']} surat diperbarui, dan {$stats['file_disalin']} lampiran disalin."
        );
    } catch (\Throwable $e) {
        Log::error('Import Surat Masuk SINAR V1 gagal', ['exception' => $e]);
        return back()->with('error', 'Import gagal: '.$e->getMessage());
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
