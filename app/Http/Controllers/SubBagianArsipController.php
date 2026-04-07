<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ArsipImportSubBagian;
use App\Exports\ArsipExport;
use Carbon\Carbon;
use App\Models\BeritaAcaraPindah;
use App\Models\BeritaAcaraDetail;
use Illuminate\Support\Facades\DB;

class SubBagianArsipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian'])
            ->where('sub_bagian_id', $user->sub_bagian_id) // scope sub bagian
            ->where('status_pindah', 'BELUM');
            

        $tahunOptions = Arsip::select('tahun_arsip')
            ->distinct()
            ->orderBy('tahun_arsip', 'desc')
            ->pluck('tahun_arsip');

        $subBagianOptions = SubBagian::select('id', 'nama_sub_bagian as nama_sub_bagian')
            ->orderBy('nama_sub_bagian')
            ->get();

        // Filter & search sama seperti ArsipController
        if ($request->has('status_arsip') && $request->status_arsip != '') {
            $query->where('status_arsip', $request->status_arsip);
        }
        if ($request->has('tahun_arsip') && $request->tahun_arsip != '') {
            $query->where('tahun_arsip', $request->tahun_arsip);
        }
        if ($request->has('kode_klasifikasi_id') && $request->kode_klasifikasi_id != '') {
            $query->where('kode_klasifikasi_id', $request->kode_klasifikasi_id);
        }
        if ($request->has('keterangan') && $request->keterangan != '') {
            $query->where('keterangan', $request->keterangan);
        }
        if ($request->has('keterangan_jra') && $request->keterangan_jra != '') {
            $query->where('keterangan_jra', $request->keterangan_jra);
        }
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('uraian_arsip', 'like', "%{$request->search}%")
                  ->orWhereHas('kodeKlasifikasi', function($sub) use ($request){
                      $sub->where('kode','like',"%{$request->search}%")
                          ->orWhere('uraian','like',"%{$request->search}%");
                  });
            });
        }
 // Filter duplikat
if ($request->show_duplicates == 1) {

            // RESET hanya yang BUKAN NON_ARSIP
            // RESET (hanya sub bagian user)
DB::table('arsips')
    ->where('sub_bagian_id', $user->sub_bagian_id)
    ->update([
        'is_duplicate' => 0,
        'duplicate_reason' => null
    ]);

// DETEKSI DUPLIKAT (judul + tahun + sub bagian)
$duplicateGroups = DB::table('arsips')
    ->select(
        DB::raw('LOWER(TRIM(REPLACE(uraian_arsip, "  ", " "))) as uraian_arsip'),
        'tahun_arsip'
    )
    ->where('status_arsip', '!=', 'NON_ARSIP')
    ->where('sub_bagian_id', $user->sub_bagian_id)
    ->groupBy(
        DB::raw('LOWER(TRIM(REPLACE(uraian_arsip, "  ", " ")))'),
        'tahun_arsip'
    )
    ->havingRaw('COUNT(*) > 1')
    ->get();

// UPDATE FLAG
foreach ($duplicateGroups as $group) {
    DB::table('arsips')
        ->where('sub_bagian_id', $user->sub_bagian_id)
        ->where('tahun_arsip', $group->tahun_arsip)
        ->whereRaw(
            'LOWER(TRIM(REPLACE(uraian_arsip, "  ", " "))) = ?',
            [$group->uraian_arsip]
        )
        ->update([
            'is_duplicate' => 1,
            'duplicate_reason' => 'Duplikat otomatis'
        ]);
}

            $query->where('is_duplicate', 1);
        }
        $arsips = $query->orderBy('id','desc')->paginate(15);

        // Filter dropdown options
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $statusOptions = ['AKTIF'=>'Aktif','INAKTIF'=>'Inaktif','HABIS_RETENSI'=>'HABIS RETENSI','MUSNAH'=>'Musnah','PERMANEN'=>'Permanen'];
        $kondisiOptions = ['BAIK'=>'Baik','RUSAK'=>'Rusak','HILANG'=>'Hilang'];
        $keteranganJraOptions = ['MUSNAH'=>'Musnah','PERMANEN'=>'Permanen'];

        return view('subbagian.arsip.index', compact(
            'arsips','kodeKlasifikasiOptions','statusOptions','kondisiOptions','keteranganJraOptions', 'tahunOptions','subBagianOptions'
        ));
    }

    public function create()
    {
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        $defaultValues = [
            'status_arsip' => 'AKTIF',
            'jumlah_berkas' => 1,
            'satuan_arsip' => 'LEMBAR',
            'tahun_arsip' => date('Y'),
            'tanggal_arsip' => date('Y-m-d'),
            'keterangan_jra' => 'MUSNAH',
        ];
        return view('subbagian.arsip.create', compact('kodeKlasifikasiOptions','defaultValues','subBagianOptions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'kode_klasifikasi_id'=>'required|exists:kode_klasifikasis,id',
            'uraian_arsip'=>'required|string|max:500',
            'tahun_arsip'=>'required|integer|min:2000|max:'.(date('Y')+1),
            'tanggal_arsip'=>'required|date',
            'jumlah_berkas'=>'required|integer|min:1',
            'satuan_arsip'=>'required|in:BENDEL,LEMBAR',
            // 'aktif_tahun'=>'nullable|string|max:100',
            // 'inaktif_tahun'=>'nullable|string|max:100',
            // 'tanggal_referensi'=>'nullable|date',
            // 'keterangan_jra'=>'nullable|in:PERMANEN,MUSNAH',
            'nomor_rak'=>'nullable|string|max:50',
            'nomor_box'=>'nullable|string|max:50',
            'nomor_sampul'=>'nullable|string|max:100',
            'lokasi_arsip' => 'nullable|in:SUB_BAGIAN,RECORD_CENTER_PERMANEN,RECORD_CENTER_INAKTIF',
            'tingkat_perkembangan'=>'nullable|in:ASLI,COPY,SALINAN',
            'keterangan'=>'nullable|in:BAIK,RUSAK,HILANG',
            'media_arsip'=>'nullable|string|max:255',
            'file_dokumen'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $validated['sub_bagian_id'] = $user->sub_bagian_id;
        $validated['created_by'] = $user->id;
        $validated['tanggal_masuk'] = now()->format('Y-m-d');
        $validated['status_pindah'] = 'BELUM';
        // $validated['lokasi_arsip'] = 'SUB_BAGIAN';

        $user = auth()->user();

        $namaSub = $user->subBagian->nama_sub_bagian ?? null;

        $mapLokasi = [
            'Sub Bagian Umum dan Logistik' => 'RUANG_SUBBAGIAN_UMUM_LOGISTIK',
            'Sub Bagian Partisipasi, Hubungan Masyarakat dan SDM' => 'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM',
            'Sub Bagian Keuangan' => 'RUANG_SUBBAGIAN_KEUANGAN',
            'Sub Bagian Perencanaan, Data, dan Informasi' => 'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI',
            'Sub Bagian Teknis Penyelenggaraan Pemilu' => 'RUANG_SUBBAGIAN_TEKNIS',
            'Sub Bagian Hukum' => 'RUANG_SUBBAGIAN_HUKUM',
        ];

        $validated['lokasi_arsip'] = $mapLokasi[$namaSub] ?? null;

        if($request->hasFile('file_dokumen')){
            $file=$request->file('file_dokumen');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->storeAs('arsip',$fileName,'public');
            $validated['file_dokumen'] = $fileName;
        }

        // $perhitungan = (new \App\Http\Controllers\ArsipController)->hitungRetensi(
        //     $validated['aktif_tahun'] ?? null,
        //     $validated['inaktif_tahun'] ?? null,
        //     $validated['keterangan_jra'],
        //     $validated['tanggal_arsip'],
        //     $validated['tanggal_referensi'] ?? null
        // );

        // $validated['aktif_sampai']=$perhitungan['aktif_sampai'];
        // $validated['inaktif_sampai']=$perhitungan['inaktif_sampai'];
        // $validated['status_arsip']=$perhitungan['status_arsip'];

        Arsip::create($validated);

        return redirect()->route('subbagian.arsip.index')->with('success','Arsip berhasil ditambahkan.');
    }

    public function edit(Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get(); // tambahkan ini
        return view('subbagian.arsip.edit', compact('arsip','kodeKlasifikasiOptions','subBagianOptions'));
    }

    public function show(Request $request, Arsip $arsip)
    {
        return view('subbagian.arsip.show', [
            'arsip' => $arsip,
            'returnUrl' => $request->get('return')
        ]);
    }

    public function update(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        $validated = $request->validate([
            'kode_klasifikasi_id'=>'required|exists:kode_klasifikasis,id',
            'uraian_arsip'=>'required|string|max:500',
            'tahun_arsip'=>'required|integer|min:2000|max:'.(date('Y')+1),
            'tanggal_arsip'=>'required|date',
            'jumlah_berkas'=>'required|integer|min:1',
            'satuan_arsip'=>'required|in:BENDEL,LEMBAR',
            // 'aktif_tahun'=>'required|string|max:100',
            // 'inaktif_tahun'=>'required|string|max:100',
            // 'tanggal_referensi'=>'nullable|date',
            // 'keterangan_jra'=>'required|in:PERMANEN,MUSNAH',
            'nomor_rak'=>'nullable|string|max:50',
            'nomor_box'=>'nullable|string|max:50',
            'nomor_sampul'=>'nullable|string|max:100',
            'lokasi_arsip' => 'nullable|string|max:100',
            'tingkat_perkembangan'=>'nullable|in:ASLI,COPY,SALINAN',
            'keterangan'=>'nullable|in:BAIK,RUSAK,HILANG',
            'media_arsip'=>'nullable|string|max:255',
            'file_dokumen'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'hapus_file'=>'nullable|in:0,1',
            'tangani_duplikat' => 'nullable|in:1',
            'duplicate_reason' => 'nullable|string|max:1000',
        ]);
        $validated['lokasi_arsip'] = $this->getLokasiArsip($user);
        if(($request->hapus_file??'0')=='1' && $arsip->file_dokumen){
            Storage::disk('public')->delete('arsip/'.$arsip->file_dokumen);
            $validated['file_dokumen']=null;
        }

        if($request->hasFile('file_dokumen')){
            if($arsip->file_dokumen) Storage::disk('public')->delete('arsip/'.$arsip->file_dokumen);
            $file=$request->file('file_dokumen');
            $fileName = time().'_'.$file->getClientOriginalName();
            $file->storeAs('arsip',$fileName,'public');
            $validated['file_dokumen']=$fileName;
        }

        // $perhitungan = (new \App\Http\Controllers\ArsipController)->hitungRetensi(
        //     $validated['aktif_tahun'] ?? null,
        //     $validated['inaktif_tahun'] ?? null,
        //     $validated['keterangan_jra'],
        //     $validated['tanggal_arsip'],
        //     $validated['tanggal_referensi'] ?? null
        // );
        // $validated['aktif_sampai']=$perhitungan['aktif_sampai'];
        // $validated['inaktif_sampai']=$perhitungan['inaktif_sampai'];
        // $validated['status_arsip']=$perhitungan['status_arsip'];
        // =========================
        // PENANGANAN DUPLIKAT
        // =========================
        $validated['duplicate_reason'] = $request->duplicate_reason;
       if ($request->tangani_duplikat == '1') {

            if (!$request->duplicate_reason) {
                return back()
                    ->withInput()
                    ->with('error', 'Alasan wajib diisi untuk penanganan duplikat.');
            }

            // Arsip ini jadi NON ARSIP
            $validated['status_pindah'] = 'NON_ARSIP';

            // Hilangkan flag duplikat
            $validated['is_duplicate'] = 0;

            // Cari arsip lain yang sama → jadikan bukan duplikat juga
            Arsip::where('uraian_arsip', $arsip->uraian_arsip)
                ->where('tahun_arsip', $arsip->tahun_arsip)
                ->where('id', '!=', $arsip->id)
                ->update([
                    'is_duplicate' => 0
                ]);

            $validated['duplicate_reason'] = $request->duplicate_reason;
        }
        // unset($validated['status_pindah']);


        $arsip->update($validated);

        return redirect()->route('subbagian.arsip.index')->with('success','Arsip berhasil diperbarui.');
    }

    public function destroy(Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        if($arsip->file_dokumen) Storage::disk('public')->delete('arsip/'.$arsip->file_dokumen);
        $arsip->delete();
        return redirect()->route('subbagian.arsip.index')->with('success','Arsip berhasil dihapus.');
    }

     public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ArsipImportSubBagian, $request->file('file_excel'));

            return redirect()->route('subbagian.arsip.index')
                ->with('success', 'Data arsip berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
    // public function export(Request $request)
    // {
    //     $columns = $request->input('columns',[]);
    //     if(count($columns)==0) return back()->with('error','Pilih minimal satu kolom.');

    //     return Excel::download(
    //         new ArsipExport($request,$columns,Auth::user()->sub_bagian_id),
    //         'arsip_subbagian_'.date('Y-m-d-H-i-s').'.xlsx'
    //     );
    // }

public function export(Request $request)
{
    $columns = $request->input('columns', []);
    if (count($columns) == 0) {
        return back()->with('error', 'Pilih minimal satu kolom.');
    }

    // Ubah 'jumlah_berkas' menjadi 'jumlah' karena di Export class menggunakan key 'jumlah'
    $columns = array_map(function($col) {
        return $col === 'jumlah_berkas' ? 'jumlah' : $col;
    }, $columns);

    return Excel::download(
        new ArsipExport($request, $columns, []), // $selectedIds = []
        'arsip_subbagian_' . date('Y-m-d-H-i-s') . '.xlsx'
    );
}

    public function ajukanPindah(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        if($arsip->sub_bagian_id != $user->sub_bagian_id) abort(403);

        $request->validate([
            'file_berita_acara'=>'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $file = $request->file('file_berita_acara');
        $fileName = time().'_'.$file->getClientOriginalName();
        $file->storeAs('arsip',$fileName,'public');
        $arsip->file_berita_acara = $fileName;
        $arsip->status_pindah='DIAJUKAN';
        $arsip->save();

        return back()->with('success','Arsip berhasil diajukan pemindahannya.');
    }

    // Tambahkan method ini di ArsipController
    // public function ajukanPindahMultiple(Request $request)
    // {
    //     $user = Auth::user();
        
    //     $request->validate([
    //         'arsip_ids' => 'required|array',
    //         'arsip_ids.*' => 'exists:arsips,id',
    //         'file_berita_acara' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
    //     ]);

    //     // Pastikan semua arsip milik sub bagian user
    //     $arsips = Arsip::whereIn('id', $request->arsip_ids)
    //                     ->where('sub_bagian_id', $user->sub_bagian_id)
    //                     ->get();

    //     if ($arsips->count() != count($request->arsip_ids)) {
    //         return back()->with('error', 'Beberapa arsip tidak ditemukan atau tidak memiliki akses.');
    //     }

    //     // Simpan file berita acara
    //     $file = $request->file('file_berita_acara');
    //     $fileName = time().'_'.$file->getClientOriginalName();
    //     $file->storeAs('arsip', $fileName, 'public');

    //     // Update setiap arsip
    //     foreach ($arsips as $arsip) {
    //         $arsip->file_berita_acara = $fileName;
    //         $arsip->status_pindah = 'DIAJUKAN';
    //         $arsip->save();
    //     }

    //     return back()->with('success', count($request->arsip_ids) . ' arsip berhasil diajukan pemindahannya.');
    // }

    public function ajukanPindahMultiple(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'arsip_ids'          => 'required|array',
            'arsip_ids.*'        => 'exists:arsips,id',
            'nomor_bap'          => 'required|string|max:100|unique:berita_acara_pindah,nomor_bap',
            'tanggal_bap'        => 'required|date',
            'file_berita_acara'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        // Pastikan semua arsip milik sub bagian user
        $arsips = Arsip::whereIn('id', $request->arsip_ids)
                        ->where('sub_bagian_id', $user->sub_bagian_id)
                        ->get();

        if ($arsips->count() != count($request->arsip_ids)) {
            return back()->with('error', 'Beberapa arsip tidak ditemukan atau tidak memiliki akses.');
        }

        // Simpan file berita acara
        $file = $request->file('file_berita_acara');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('berita_acara', $fileName, 'public');

        DB::beginTransaction();
        try {
            // Buat header BAP
            $bap = BeritaAcaraPindah::create([
                'nomor_bap'      => $request->nomor_bap,
                'tanggal_bap'    => $request->tanggal_bap,
                'sub_bagian_id'  => $user->sub_bagian_id,
                'created_by'     => $user->id,
                'file_bap'       => $fileName,
                'status'         => 'DIAJUKAN',
            ]);

            // Simpan detail dan update arsip
            foreach ($arsips as $arsip) {
                BeritaAcaraDetail::create([
                    'bap_id'   => $bap->id,
                    'arsip_id' => $arsip->id,
                    'status'   => 'DIAJUKAN',
                ]);

                // Update arsip
                $arsip->status_pindah = 'DIAJUKAN';
                $arsip->file_berita_acara = $fileName; // boleh diisi untuk referensi cepat
                $arsip->save();
            }

            DB::commit();

            return redirect()->route('subbagian.arsip.index')
                ->with('success', count($request->arsip_ids) . ' arsip berhasil diajukan dengan Nomor BAP: ' . $request->nomor_bap);
        } catch (\Exception $e) {
            DB::rollBack();
            Storage::disk('public')->delete('berita_acara/' . $fileName);
            return back()->with('error', 'Gagal mengajukan pemindahan: ' . $e->getMessage());
        }
    }

    public function duplicate(Arsip $arsip)
{
    $user = Auth::user();

    // pastikan arsip milik sub bagian user
    if ($arsip->sub_bagian_id != $user->sub_bagian_id) {
        abort(403);
    }

    // duplikat data
    $newArsip = $arsip->replicate();

    // reset beberapa field penting
    $newArsip->created_by = $user->id;
    $newArsip->tanggal_masuk = now()->format('Y-m-d');
    $newArsip->status_pindah = 'BELUM';

    // opsional: kasih penanda biar keliatan duplikat
    $newArsip->uraian_arsip = $arsip->uraian_arsip . ' (Copy)';

    // kalau mau reset file biar tidak ikut
    // $newArsip->file_dokumen = null;

    $newArsip->save();

    return redirect()->route('subbagian.arsip.index')
        ->with('success', 'Arsip berhasil diduplikasi.');
}

private function getLokasiArsip($user)
{
    $namaSub = $user->subBagian->nama_sub_bagian ?? null;

    $mapLokasi = [
        'Sub Bagian Umum dan Logistik' => 'RUANG_SUBBAGIAN_UMUM_LOGISTIK',
        'Sub Bagian Partisipasi, Hubungan Masyarakat dan SDM' => 'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM',
        'Sub Bagian Keuangan' => 'RUANG_SUBBAGIAN_KEUANGAN',
        'Sub Bagian Perencanaan, Data, dan Informasi' => 'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI',
        'Sub Bagian Teknis Penyelenggaraan Pemilu' => 'RUANG_SUBBAGIAN_TEKNIS',
        'Sub Bagian Hukum' => 'RUANG_SUBBAGIAN_HUKUM',
    ];

    return $mapLokasi[$namaSub] ?? null;
}
}
