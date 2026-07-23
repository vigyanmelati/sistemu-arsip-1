<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\SubBagian;
use App\Models\HistoryPindah;
use App\Models\BeritaAcaraDetail;
use App\Models\BeritaAcaraPindah;
use App\Models\User;
use App\Models\MasterRak;
use App\Models\MasterBox;
use App\Models\KodeKlasifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminArsipMasukController extends Controller
{

     public function index(Request $request)
    {
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian', 'creator', 'rak', 'box'])
            ->where('status_pindah', 'DIAJUKAN')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan sub bagian
        if ($request->has('sub_bagian_id') && $request->sub_bagian_id != '') {
            $query->where('sub_bagian_id', $request->sub_bagian_id);
        }

        // Filter berdasarkan tahun
        if ($request->has('tahun_arsip') && $request->tahun_arsip != '') {
            $query->where('tahun_arsip', $request->tahun_arsip);
        }

        // Filter berdasarkan kode klasifikasi
        if ($request->has('kode_klasifikasi_id') && $request->kode_klasifikasi_id != '') {
            $query->where('kode_klasifikasi_id', $request->kode_klasifikasi_id);
        }

        // Filter berdasarkan status arsip
        if ($request->has('status_arsip') && $request->status_arsip != '') {
            $query->where('status_arsip', $request->status_arsip);
        }

        if ($request->status_lokasi == 'belum') {
            $query->whereNull('tanggal_diverifikasi');
        }

        if ($request->status_lokasi == 'sudah') {
            $query->whereNotNull('tanggal_diverifikasi');
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('uraian_arsip', 'like', "%{$request->search}%")
                  ->orWhereHas('kodeKlasifikasi', function($sub) use ($request){
                      $sub->where('kode', 'like', "%{$request->search}%")
                          ->orWhere('uraian', 'like', "%{$request->search}%");
                  })
                  ->orWhereHas('subBagian', function($sub) use ($request){
                      $sub->where('nama_sub_bagian', 'like', "%{$request->search}%");
                  });
            });
        }

        $arsips = $query->paginate(15);

        // Data untuk filter
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        $tahunOptions = Arsip::select('tahun_arsip')
            ->distinct()
            ->orderBy('tahun_arsip', 'desc')
            ->pluck('tahun_arsip');

        // Hitung total arsip masuk untuk badge
        $arsipMasukCount = Arsip::where('status_pindah', 'DIAJUKAN')->count();

        // ===================== TAMBAHKAN INI =====================
        // Ambil data rak dan box untuk dropdown di modal proses multiple
        $rakOptions = MasterRak::select('id', 'nomor_rak', 'lokasi_arsip')
            ->orderBy('nomor_rak')
            ->get();

        $boxOptions = MasterBox::select('id', 'nomor_box', 'rak_id')
            ->orderBy('nomor_box')
            ->get();
        // =======================================================

       $kodeKlasifikasiOptions = KodeKlasifikasi::select('id', 'kode', 'uraian')
        ->orderBy('kode')
        ->get();

    return view('arsip-masuk.index', compact(
        'arsips', 
        'subBagianOptions',
        'tahunOptions',
        'arsipMasukCount',
        'rakOptions',
        'boxOptions',
        'kodeKlasifikasiOptions' // <-- TAMBAHKAN INI
    ));
    }

    /**
     * Display the specified arsip.
     */



public function show(Arsip $arsip)
{
    // Load SEMUA relasi yang diperlukan
    $arsip->load([
        'kodeKlasifikasi',
        'subBagian',
        'creator',
        'rak',
        'box',
        'historyPindah' => function($query) {
            $query->orderBy('tanggal_pindah', 'desc');
        },
        'beritaAcaraPindah' => function($query) {
            $query->latest();
        }
    ]);

    // Tambahkan data verifikator jika ada
    if ($arsip->diverifikasi_oleh) {
        $arsip->load('verifikator');
    }

    // Ambil data rak dan box untuk dropdown
    $rakOptions = MasterRak::select('id', 'nomor_rak', 'lokasi_arsip')
        ->orderBy('nomor_rak')
        ->get();

    $boxOptions = MasterBox::select('id', 'nomor_box', 'rak_id')
        ->orderBy('nomor_box')
        ->get();

    // Debug - cek data (hapus setelah selesai)
    \Log::info('Show Arsip Data:', [
        'id' => $arsip->id,
        'kode_klasifikasi' => $arsip->kodeKlasifikasi ? $arsip->kodeKlasifikasi->kode : 'NULL',
        'sub_bagian' => $arsip->subBagian ? $arsip->subBagian->nama_sub_bagian : 'NULL',
        'rak' => $arsip->rak ? $arsip->rak->nomor_rak : 'NULL',
        'box' => $arsip->box ? $arsip->box->nomor_box : 'NULL',
        'status_arsip' => $arsip->status_arsip,
        'aktif_sampai' => $arsip->aktif_sampai,
        'inaktif_sampai' => $arsip->inaktif_sampai,
    ]);

    // Kirim data ke view
    return view('arsip-masuk.show', compact('arsip', 'rakOptions', 'boxOptions'));
}

/**
 * Terima pengajuan pemindahan arsip.
 */
public function terima(Request $request, Arsip $arsip)
{
    $request->validate([
        'rak_id_baru' => 'required|exists:master_raks,id',
        'box_id_baru' => 'required|exists:master_box,id',
        'catatan' => 'nullable|string|max:500',
        'lokasi_tujuan' => 'required|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
        'catatan_persetujuan' => 'nullable|string|max:500', // <-- TAMBAHKAN INI
    ]);

    if ($arsip->status_pindah !== 'DIAJUKAN') {
        return back()->with('error', 'Status arsip tidak valid.');
    }

    DB::beginTransaction();
    try {
        // Simpan lokasi lama
        $dariRak = $arsip->rak_id;
        $dariBox = $arsip->box_id;
        $dariRakNomor = $arsip->rak ? $arsip->rak->nomor_rak : null;
        $dariBoxNomor = $arsip->box ? $arsip->box->nomor_box : null;

        // Ambil data rak dan box baru
        $rakBaru = MasterRak::find($request->rak_id_baru);
        $boxBaru = MasterBox::find($request->box_id_baru);

        // Gabungkan catatan
        $catatanVerifikasi = $request->catatan;
        if ($request->filled('catatan_persetujuan')) {
            $catatanVerifikasi = ($catatanVerifikasi ? $catatanVerifikasi . "\n\n" : '') 
                . "✅ DISETUJUI: " . $request->catatan_persetujuan;
        }

        // Update arsip
        $arsip->rak_id = $request->rak_id_baru;
        $arsip->box_id = $request->box_id_baru;
        $arsip->lokasi_arsip = $request->lokasi_tujuan;
        $arsip->tanggal_diverifikasi = now();
        $arsip->diverifikasi_oleh = auth()->id();
        $arsip->catatan_verifikasi = $catatanVerifikasi;
        $arsip->status_pindah = 'DITERIMA'; // <-- Set status DITERIMA dulu

        $arsip->skipHistory = true;
        $arsip->save();

        // Catat history
        HistoryPindah::create([
            'arsip_id' => $arsip->id,
            'dari_rak' => $dariRakNomor,
            'dari_box' => $dariBoxNomor,
            'ke_rak' => $rakBaru ? $rakBaru->nomor_rak : null,
            'ke_box' => $boxBaru ? $boxBaru->nomor_box : null,
            'tanggal_pindah' => now(),
            'alasan_pindah' => '✅ DISETUJUI: ' . ($request->catatan_persetujuan ?? 'Verifikasi lokasi arsip dari Sub Bagian ke Unit Kearsipan'),
            'user_id' => auth()->id()
        ]);

        DB::commit();

        return redirect()
            ->route('arsip-masuk.index')
            ->with('success', 'Arsip berhasil disetujui. Status: DITERIMA. Silakan lanjutkan ke proses pemindahan.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

/**
 * Tolak pengajuan pemindahan arsip.
 */
public function tolak(Request $request, Arsip $arsip)
{
    $request->validate([
        'alasan' => 'required|string|max:500'
    ]);

    if ($arsip->status_pindah !== 'DIAJUKAN') {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Status arsip tidak valid untuk ditolak.'
            ], 400);
        }
        return back()->with('error', 'Status arsip tidak valid untuk ditolak.');
    }

    DB::beginTransaction();
    try {
        // Format catatan penolakan
        $catatanPenolakan = "❌ DITOLAK: " . $request->alasan;

        $arsip->status_pindah = 'DITOLAK';
        $arsip->tanggal_diverifikasi = now();
        $arsip->diverifikasi_oleh = auth()->id();
        $arsip->catatan_verifikasi = $catatanPenolakan;
        $arsip->save();

        // Catat history penolakan
        HistoryPindah::create([
            'arsip_id' => $arsip->id,
            'tanggal_pindah' => now(),
            'alasan_pindah' => '❌ DITOLAK: ' . $request->alasan,
            'user_id' => auth()->id()
        ]);

        DB::commit();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Arsip telah ditolak.'
            ]);
        }

        return redirect()->route('arsip-masuk.index')
            ->with('success', 'Arsip telah ditolak.');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

/**
 * Proses pemindahan arsip ke master arsip (setelah DITERIMA).
 */
public function pindahkan(Request $request, Arsip $arsip)
{
    $request->validate([
        'status_arsip_setelah_pindah' => 'required|in:AKTIF,INAKTIF,PERMANEN,MUSNAH',
        'nomor_rak_pemindahan' => 'nullable|string|max:50',
        'nomor_box_pemindahan' => 'nullable|string|max:50',
        'lokasi_tujuan' => 'nullable|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
        'catatan_pemindahan' => 'nullable|string|max:500', // <-- TAMBAHKAN INI
    ]);

    // Pastikan arsip sudah DITERIMA
    if ($arsip->status_pindah !== 'DITERIMA') {
        return back()->with('error', 'Arsip harus DITERIMA terlebih dahulu sebelum dipindahkan.');
    }

    DB::beginTransaction();
    try {
        // Simpan nilai lama sebelum diubah
        $dariRak = $arsip->rak ? $arsip->rak->nomor_rak : null;
        $dariBox = $arsip->box ? $arsip->box->nomor_box : null;
        $tanggalPindah = now();

        // Jika ada lokasi pemindahan baru, update
        if ($request->filled('nomor_rak_pemindahan') || $request->filled('nomor_box_pemindahan')) {
            // Cari atau buat rak baru
            if ($request->filled('nomor_rak_pemindahan')) {
                $rakBaru = MasterRak::firstOrCreate(
                    ['nomor_rak' => $request->nomor_rak_pemindahan],
                    ['lokasi_arsip' => $request->lokasi_tujuan ?? $arsip->lokasi_arsip]
                );
                $arsip->rak_id = $rakBaru->id;
            }
            
            if ($request->filled('nomor_box_pemindahan')) {
                $boxBaru = MasterBox::firstOrCreate(
                    ['nomor_box' => $request->nomor_box_pemindahan],
                    ['rak_id' => $arsip->rak_id]
                );
                $arsip->box_id = $boxBaru->id;
            }
        }

        // Update status arsip
        $arsip->status_pindah = 'DIPINDAHKAN';
        $arsip->tanggal_dipindahkan = $tanggalPindah;
        $arsip->status_arsip = $request->status_arsip_setelah_pindah;
        $arsip->lokasi_arsip = $request->lokasi_tujuan ?? $arsip->lokasi_arsip;
        
        // Tambahkan catatan pemindahan ke catatan verifikasi yang sudah ada
        if ($request->filled('catatan_pemindahan')) {
            $catatanBaru = $arsip->catatan_verifikasi 
                ? $arsip->catatan_verifikasi . "\n\n📦 PINDAH: " . $request->catatan_pemindahan
                : "📦 PINDAH: " . $request->catatan_pemindahan;
            $arsip->catatan_verifikasi = $catatanBaru;
        }
        
        $arsip->skipHistory = true;
        $arsip->save();

        // Catat history pemindahan
        HistoryPindah::create([
            'arsip_id' => $arsip->id,
            'dari_rak' => $dariRak,
            'dari_box' => $dariBox,
            'ke_rak' => $arsip->rak ? $arsip->rak->nomor_rak : null,
            'ke_box' => $arsip->box ? $arsip->box->nomor_box : null,
            'tanggal_pindah' => $tanggalPindah,
            'alasan_pindah' => '📦 DIPINDAHKAN: ' . ($request->catatan_pemindahan ?? 'Pemindahan ke Unit Kearsipan'),
            'user_id' => auth()->id()
        ]);

        DB::commit();

        return redirect()->route('arsip-masuk.index')
            ->with('success', 'Arsip berhasil dipindahkan ke Unit Kearsipan.');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
    /**
     * Terima pengajuan pemindahan arsip.
     */
    // public function terima(Request $request, Arsip $arsip)
    // {
    //     $request->validate([
    //         'rak_id_baru' => 'required|exists:master_raks,id',
    //         'box_id_baru' => 'required|exists:master_box,id',
    //         'catatan' => 'nullable|string|max:500',
    //         'lokasi_tujuan' => 'required|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
    //     ]);

    //     if ($arsip->status_pindah !== 'DIAJUKAN') {
    //         return back()->with('error', 'Status arsip tidak valid.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Simpan lokasi lama
    //         $dariRak = $arsip->rak_id;
    //         $dariBox = $arsip->box_id;
    //         $dariRakNomor = $arsip->rak ? $arsip->rak->nomor_rak : null;
    //         $dariBoxNomor = $arsip->box ? $arsip->box->nomor_box : null;

    //         // Ambil data rak dan box baru
    //         $rakBaru = MasterRak::find($request->rak_id_baru);
    //         $boxBaru = MasterBox::find($request->box_id_baru);

    //         // Update arsip
    //         $arsip->rak_id = $request->rak_id_baru;
    //         $arsip->box_id = $request->box_id_baru;
    //         $arsip->lokasi_arsip = $request->lokasi_tujuan;
    //         $arsip->tanggal_diverifikasi = now();
    //         $arsip->diverifikasi_oleh = auth()->id();
    //         $arsip->catatan_verifikasi = $request->catatan;

    //         $arsip->skipHistory = true;
    //         $arsip->save();

    //         // Catat history
    //         HistoryPindah::create([
    //             'arsip_id' => $arsip->id,
    //             'dari_rak' => $dariRakNomor,
    //             'dari_box' => $dariBoxNomor,
    //             'ke_rak' => $rakBaru ? $rakBaru->nomor_rak : null,
    //             'ke_box' => $boxBaru ? $boxBaru->nomor_box : null,
    //             'tanggal_pindah' => now(),
    //             'alasan_pindah' => 'Verifikasi lokasi arsip dari Sub Bagian ke Unit Kearsipan',
    //             'user_id' => auth()->id()
    //         ]);

    //         DB::commit();

    //         return redirect()
    //             ->route('arsip-masuk.index')
    //             ->with('success', 'Verifikasi berhasil. Lokasi baru disimpan, arsip belum dipindahkan.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }


    // /**
    //  * Tolak pengajuan pemindahan arsip.
    //  */
    // // App/Http/Controllers/AdminArsipMasukController.php
    // public function tolak(Request $request, Arsip $arsip)
    // {
    //     $request->validate([
    //         'alasan' => 'required|string|max:500'
    //     ]);

    //     // Debug: lihat data yang masuk
    //     \Log::info('Data penolakan diterima:', [
    //         'arsip_id' => $arsip->id,
    //         'alasan' => $request->alasan,
    //         'user_id' => auth()->id()
    //     ]);

    //     // Pastikan arsip masih dalam status DIAJUKAN
    //     if ($arsip->status_pindah !== 'DIAJUKAN') {
    //         if ($request->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Status arsip tidak valid untuk ditolak.'
    //             ], 400);
    //         }
    //         return back()->with('error', 'Status arsip tidak valid untuk ditolak.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Update status arsip - gunakan update langsung
    //         $updateData = [
    //             'status_pindah' => 'DITOLAK',
    //             'tanggal_diverifikasi' => now(),
    //             'diverifikasi_oleh' => auth()->id(),
    //             'catatan_verifikasi' => $request->alasan // Pastikan ini ada
    //         ];
            
    //         \Log::info('Data yang akan diupdate:', $updateData);
            
    //         // Coba dengan cara berbeda
    //         $arsip->catatan_verifikasi = $request->alasan;
    //         $arsip->status_pindah = 'DITOLAK';
    //         $arsip->tanggal_diverifikasi = now();
    //         $arsip->diverifikasi_oleh = auth()->id();
    //         $arsip->save();

    //         DB::commit();

    //         \Log::info('Arsip berhasil ditolak:', ['arsip_id' => $arsip->id]);

    //         if ($request->expectsJson()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Arsip telah ditolak.'
    //             ]);
    //         }

    //         return redirect()->route('arsip-masuk.index')
    //             ->with('success', 'Arsip telah ditolak.');

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         \Log::error('Error menolak arsip:', [
    //             'arsip_id' => $arsip->id,
    //             'error' => $e->getMessage()
    //         ]);
            
    //         if ($request->expectsJson()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    //             ], 500);
    //         }
            
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Proses pemindahan arsip ke master arsip.
    //  */
    // public function pindahkan(Request $request, Arsip $arsip)
    // {
    //     $request->validate([
    //         'status_arsip_setelah_pindah' => 'required|in:AKTIF,INAKTIF,PERMANEN,MUSNAH',
    //         'nomor_rak_pemindahan' => 'nullable|string|max:50',
    //         'nomor_box_pemindahan' => 'nullable|string|max:50',
    //          'lokasi_tujuan' => 'nullable|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
    //     ]);

    //     // Pastikan arsip sudah DIPINDAHKAN
    //     if ($arsip->status_pindah !== 'DIPINDAHKAN') {
    //         return back()->with('error', 'Arsip harus DIPINDAHKAN terlebih dahulu sebelum dipindahkan.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Simpan nilai lama sebelum diubah
    //         $dariRak = $arsip->nomor_rak;
    //         $dariBox = $arsip->nomor_box;
    //         $tanggalPindah = now();

    //         // Jika ada lokasi pemindahan baru, update
    //         if ($request->filled('nomor_rak_pemindahan') || $request->filled('nomor_box_pemindahan')) {
    //             if ($request->nomor_rak_pemindahan) {
    //                 $arsip->nomor_rak = $request->nomor_rak_pemindahan;
    //             }
    //             if ($request->nomor_box_pemindahan) {
    //                 $arsip->nomor_box = $request->nomor_box_pemindahan;
    //             }
                
    //             // Catat history perpindahan
    //             HistoryPindah::create([
    //                 'arsip_id' => $arsip->id,
    //                 'dari_rak' => $dariRak,
    //                 'dari_box' => $dariBox,
    //                 'ke_rak' => $arsip->nomor_rak,
    //                 'ke_box' => $arsip->nomor_box,
    //                 'tanggal_pindah' => $tanggalPindah,
    //                 'alasan_pindah' => 'Pemindahan internal Unit Kearsipan - Status: ' . $request->status_arsip_setelah_pindah,
    //                 'user_id' => auth()->id()
    //             ]);
    //         }

    //         // $lokasiArsip = match ($request->status_arsip_setelah_pindah) {
    //         //     'INAKTIF' => 'RECORD_CENTER_INAKTIF',
    //         //     'PERMANEN' => 'RECORD_CENTER_PERMANEN',
    //         //     default => null
    //         // };


    //         // Update status arsip
    //         $arsip->status_pindah = 'DIPINDAHKAN';
    //         $arsip->tanggal_dipindahkan = $tanggalPindah;
    //         $arsip->status_arsip = $request->status_arsip_setelah_pindah;
    //        $arsip->lokasi_arsip = $request->lokasi_tujuan ?? $arsip->lokasi_arsip;
    //         $arsip->skipHistory = true;
    //         $arsip->save();

    //         // Log aktivitas
    //         activity()
    //             ->causedBy(auth()->user())
    //             ->performedOn($arsip)
    //             ->withProperties([
    //                 'status_arsip_setelah_pindah' => $request->status_arsip_setelah_pindah,
    //                 'lokasi_baru' => $arsip->nomor_rak . '/' . $arsip->nomor_box
    //             ])
    //             ->log('Memindahkan arsip ke master arsip');

    //         DB::commit();

    //         return redirect()->route('arsip-masuk.index')
    //             ->with('success', 'Arsip berhasil dipindahkan ke master arsip.' . 
    //                    ($request->filled('nomor_rak_pemindahan') ? ' History perpindahan dicatat.' : ''));

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    /**
     * Proses multiple arsip sekaligus.
     */
   /**
 * Proses multiple arsip sekaligus.
 */
// public function prosesMultiple(Request $request)
// {
//     $request->validate([
//         'arsip_ids'   => 'required|array',
//         'arsip_ids.*' => 'exists:arsips,id',
//         'action'      => 'required|in:set_lokasi,pindahkan',
//         'rak_id'      => 'nullable|exists:master_raks,id',
//         'box_id'      => 'nullable|exists:master_box,id',
//         'catatan'     => 'nullable|string|max:500',
//     ]);

//     DB::beginTransaction();

//     try {
//         $arsips = Arsip::whereIn('id', $request->arsip_ids)
//             ->lockForUpdate()
//             ->get();

//         foreach ($arsips as $arsip) {

//             /* ============================
//              | AKSI 1 : SET LOKASI
//              | ============================ */
//             if ($request->action === 'set_lokasi') {

//                 if (!$request->rak_id || !$request->box_id) {
//                     throw new \Exception('Rak dan box wajib dipilih.');
//                 }

//                 $dariRak = $arsip->rak_id;
//                 $dariBox = $arsip->box_id;
//                 $dariRakNomor = $arsip->rak ? $arsip->rak->nomor_rak : null;
//                 $dariBoxNomor = $arsip->box ? $arsip->box->nomor_box : null;

//                 $rakBaru = MasterRak::find($request->rak_id);
//                 $boxBaru = MasterBox::find($request->box_id);

//                 $arsip->skipHistory = true;
//                 $arsip->update([
//                     'rak_id'               => $request->rak_id,
//                     'box_id'               => $request->box_id,
//                     'lokasi_arsip'         => $rakBaru ? $rakBaru->lokasi_arsip : $arsip->lokasi_arsip,
//                     'tanggal_diverifikasi' => now(),
//                     'diverifikasi_oleh'    => auth()->id(),
//                     'catatan_verifikasi'   => $request->catatan,
//                 ]);

//                 HistoryPindah::create([
//                     'arsip_id' => $arsip->id,
//                     'aksi' => 'SET_LOKASI',
//                     'dari_rak' => $dariRakNomor,
//                     'dari_box' => $dariBoxNomor,
//                     'ke_rak' => $rakBaru ? $rakBaru->nomor_rak : null,
//                     'ke_box' => $boxBaru ? $boxBaru->nomor_box : null,
//                     'tanggal_pindah' => now(),
//                     'alasan_pindah' => $request->catatan ?? 'Verifikasi lokasi arsip',
//                     'user_id' => auth()->id(),
//                 ]);
//             }

//             /* ============================
//              | AKSI 2 : PINDAHKAN KE MASTER
//              | ============================ */
//             if ($request->action === 'pindahkan') {

//                 if (!$arsip->rak_id || !$arsip->box_id) {
//                     throw new \Exception('Arsip belum memiliki rak dan box.');
//                 }

//                 $arsip->skipHistory = true;
//                 $arsip->update([
//                     'status_pindah'       => 'DIPINDAHKAN',
//                     'tanggal_dipindahkan' => now(),
//                 ]);

//                 HistoryPindah::create([
//                     'arsip_id' => $arsip->id,
//                     'aksi'     => 'PINDAHKAN',
//                     'dari_rak' => $arsip->rak ? $arsip->rak->nomor_rak : null,
//                     'dari_box' => $arsip->box ? $arsip->box->nomor_box : null,
//                     'ke_rak'   => $arsip->rak ? $arsip->rak->nomor_rak : null,
//                     'ke_box'   => $arsip->box ? $arsip->box->nomor_box : null,
//                     'tanggal_pindah' => now(),
//                     'alasan_pindah'  => $request->catatan ?? 'Arsip dipindahkan ke Unit Kearsipan',
//                     'user_id'  => auth()->id(),
//                 ]);
//             }
//         }

//         DB::commit();
//         return back()->with('success', 'Proses arsip berhasil.');

//     } catch (\Throwable $e) {
//         DB::rollBack();
//         return back()->with('error', $e->getMessage());
//     }
// }


public function prosesMultiple(Request $request)
{
    $request->validate([
        'arsip_ids'   => 'required|array',
        'arsip_ids.*' => 'exists:arsips,id',
        'action'      => 'required|in:setujui,tolak',
        // 'status_arsip_setelah_pindah' => 'nullable|required_if:action,setujui|in:AKTIF,INAKTIF,PERMANEN,MUSNAH',
        'lokasi_tujuan' => 'nullable|required_if:action,setujui|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
        'rak_id'      => 'nullable|required_if:action,setujui|exists:master_raks,id',
        'box_id'      => 'nullable|required_if:action,setujui|exists:master_box,id',
        'alasan'      => 'nullable|required_if:action,tolak|string|max:500',
        'catatan'     => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();

    try {
        $arsips = Arsip::whereIn('id', $request->arsip_ids)
            ->lockForUpdate()
            ->get();

        $bapIds = [];

        foreach ($arsips as $arsip) {

            if ($arsip->status_pindah !== 'DIAJUKAN') {
                continue;
            }

            $bapDetail = BeritaAcaraDetail::where('arsip_id', $arsip->id)->first();
            if ($bapDetail) {
                $bapIds[] = $bapDetail->bap_id;
            }

            if ($request->action === 'setujui') {

                $dariRak = $arsip->rak_id;
                $dariBox = $arsip->box_id;
                $dariRakNomor = $arsip->rak ? $arsip->rak->nomor_rak : null;
                $dariBoxNomor = $arsip->box ? $arsip->box->nomor_box : null;

                $rakBaru = MasterRak::find($request->rak_id);
                $boxBaru = MasterBox::find($request->box_id);

                $catatanVerifikasi = '✅ DISETUJUI: ' . ($request->catatan ?? 'Arsip diverifikasi dan dipindahkan ke Unit Kearsipan.');

                $arsip->skipHistory = true;
                $arsip->update([
                    'rak_id'                => $request->rak_id,
                    'box_id'                => $request->box_id,
                    'lokasi_arsip'           => $request->lokasi_tujuan,
                    // 'status_arsip'           => $request->status_arsip_setelah_pindah,
                    'status_pindah'          => 'DIPINDAHKAN',
                    'tanggal_diverifikasi'   => now(),
                    'diverifikasi_oleh'      => auth()->id(),
                    'tanggal_dipindahkan'    => now(),
                    'catatan_verifikasi'     => $catatanVerifikasi,
                ]);

                if ($bapDetail) {
                    $bapDetail->update(['status' => BeritaAcaraDetail::STATUS_DITERIMA]);
                }

                HistoryPindah::create([
                    'arsip_id' => $arsip->id,
                    'aksi' => 'SETUJUI_PINDAHKAN',
                    'dari_rak' => $dariRakNomor,
                    'dari_box' => $dariBoxNomor,
                    'ke_rak' => $rakBaru ? $rakBaru->nomor_rak : null,
                    'ke_box' => $boxBaru ? $boxBaru->nomor_box : null,
                    'tanggal_pindah' => now(),
                    'alasan_pindah' => $catatanVerifikasi,
                    'user_id' => auth()->id(),
                ]);
            }

            if ($request->action === 'tolak') {

                $catatanPenolakan = '❌ DITOLAK: ' . $request->alasan;

                $arsip->status_pindah = 'DITOLAK';
                $arsip->tanggal_diverifikasi = now();
                $arsip->diverifikasi_oleh = auth()->id();
                $arsip->catatan_verifikasi = $catatanPenolakan;
                $arsip->save();

                if ($bapDetail) {
                    $bapDetail->update(['status' => BeritaAcaraDetail::STATUS_DITOLAK]);
                }

                HistoryPindah::create([
                    'arsip_id' => $arsip->id,
                    'aksi' => 'TOLAK',
                    'tanggal_pindah' => now(),
                    'alasan_pindah' => $catatanPenolakan,
                    'user_id' => auth()->id(),
                ]);
            }
        }

        $uniqueBapIds = array_unique($bapIds);
        foreach ($uniqueBapIds as $bapId) {
            $bap = BeritaAcaraPindah::find($bapId);
            if ($bap) {
                $allDiterima = BeritaAcaraDetail::where('bap_id', $bapId)
                    ->whereHas('arsip', function($q) {
                        $q->where('status_pindah', '!=', 'DIPINDAHKAN');
                    })
                    ->doesntExist();

                if ($allDiterima) {
                    $bap->status = BeritaAcaraPindah::STATUS_DISETUJUI;
                    $bap->save();
                }
            }
        }

        DB::commit();

        $message = $request->action === 'setujui'
            ? 'Semua arsip yang dipilih berhasil disetujui dan dipindahkan ke Unit Kearsipan.'
            : 'Semua arsip yang dipilih berhasil ditolak.';

        return back()->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}
    /**
     * History perpindahan arsip.
     */
    public function history($id)
    {
        $arsip = Arsip::with(['historyPindah.user', 'subBagian'])->findOrFail($id);
        $history = $arsip->historyPindah()->orderBy('tanggal_pindah', 'desc')->get();
        
        return view('arsip-masuk.history', compact('arsip', 'history'));
    }

    public function downloadBeritaAcara(Arsip $arsip)
    {
        // Cari BAP terakhir yang terkait dengan arsip ini
        $bap = $arsip->beritaAcaraPindah()->latest()->first();
        
        if (!$bap || !$bap->file_bap) {
            return back()->with('error', 'File berita acara tidak ditemukan.');
        }

        $path = storage_path('app/public/berita_acara/' . $bap->file_bap);
        
        if (!file_exists($path)) {
            return back()->with('error', 'File fisik tidak ditemukan di server.');
        }

        // Format nama file untuk download
        $filename = 'BAP_' . str_replace('/', '_', $bap->nomor_bap) . '.pdf';
        
        return response()->download($path, $filename);
    }



// app/Http/Controllers/AdminArsipMasukController.php

// public function verifikasi(Request $request, Arsip $arsip)
// {
//     // Debug - lihat data yang masuk
//     \Log::info('Verifikasi data:', $request->all());
    
//   $request->validate([
//     'tindakan' => 'required|in:setujui,tolak',
//     'alasan' => 'nullable|required_if:tindakan,tolak|string|max:500',
//     'lokasi_tujuan' => 'nullable|required_if:tindakan,setujui|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
//     'rak_id_baru' => 'nullable|required_if:tindakan,setujui|exists:master_raks,id',
//     'box_id_baru' => 'nullable|required_if:tindakan,setujui|exists:master_box,id',
// ]);
//     if ($arsip->status_pindah !== 'DIAJUKAN') {
//         return back()->with('error', 'Status arsip tidak valid.');
//     }

//     DB::beginTransaction();
//     try {
//         if ($request->tindakan === 'setujui') {
//             // ========== SETUJUI ==========
//             $dariRak = $arsip->rak_id;
//             $dariBox = $arsip->box_id;
//             $dariRakNomor = $arsip->rak ? $arsip->rak->nomor_rak : null;
//             $dariBoxNomor = $arsip->box ? $arsip->box->nomor_box : null;

//             $rakBaru = MasterRak::find($request->rak_id_baru);
//             $boxBaru = MasterBox::find($request->box_id_baru);

//             $arsip->rak_id = $request->rak_id_baru;
//             $arsip->box_id = $request->box_id_baru;
//             $arsip->lokasi_arsip = $request->lokasi_tujuan;
//             $arsip->tanggal_diverifikasi = now();
//             $arsip->diverifikasi_oleh = auth()->id();
//             $arsip->status_pindah = 'DIPINDAHKAN';
//             $arsip->catatan_verifikasi = '✅ DISETUJUI: Arsip diverifikasi dan diterima.';

//             $arsip->skipHistory = true;
//             $arsip->save();

//             HistoryPindah::create([
//                 'arsip_id' => $arsip->id,
//                 'dari_rak' => $dariRakNomor,
//                 'dari_box' => $dariBoxNomor,
//                 'ke_rak' => $rakBaru ? $rakBaru->nomor_rak : null,
//                 'ke_box' => $boxBaru ? $boxBaru->nomor_box : null,
//                 'tanggal_pindah' => now(),
//                 'alasan_pindah' => '✅ DISETUJUI: Arsip diterima dan siap dipindahkan',
//                 'user_id' => auth()->id()
//             ]);

//             DB::commit();

//             return redirect()
//                 ->route('arsip-masuk.index')
//                 ->with('success', 'Arsip berhasil disetujui. Status: DITERIMA.');

//         } else {
//             // ========== TOLAK ==========
//             $arsip->status_pindah = 'DITOLAK';
//             $arsip->tanggal_diverifikasi = now();
//             $arsip->diverifikasi_oleh = auth()->id();
//             $arsip->catatan_verifikasi = '❌ DITOLAK: ' . $request->alasan;
//             $arsip->save();

//             HistoryPindah::create([
//                 'arsip_id' => $arsip->id,
//                 'tanggal_pindah' => now(),
//                 'alasan_pindah' => '❌ DITOLAK: ' . $request->alasan,
//                 'user_id' => auth()->id()
//             ]);

//             DB::commit();

//             return redirect()
//                 ->route('arsip-masuk.index')
//                 ->with('success', 'Arsip telah ditolak.');
//         }

//     } catch (\Exception $e) {
//          dd([
//             'error_message' => $e->getMessage(),
//             'error_file' => $e->getFile(),
//             'error_line' => $e->getLine(),
//             'request_data' => $request->all(),
//             'trace' => $e->getTraceAsString()
//         ]);
//         DB::rollBack();
//         \Log::error('Verifikasi error:', ['error' => $e->getMessage()]);
//         return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
//     }
// }



public function verifikasi(Request $request, Arsip $arsip)
{
    \Log::info('Verifikasi data:', $request->all());
    
    $request->validate([
        'tindakan' => 'required|in:setujui,tolak',
        'alasan' => 'nullable|required_if:tindakan,tolak|string|max:500',
        'lokasi_tujuan' => 'nullable|required_if:tindakan,setujui|in:RECORD_CENTER_INAKTIF,RECORD_CENTER_PERMANEN',
        'rak_id_baru' => 'nullable|required_if:tindakan,setujui|exists:master_raks,id',
        'box_id_baru' => 'nullable|required_if:tindakan,setujui|exists:master_box,id',
    ]);

    if ($arsip->status_pindah !== 'DIAJUKAN') {
        return back()->with('error', 'Status arsip tidak valid.');
    }

    DB::beginTransaction();
    try {
        if ($request->tindakan === 'setujui') {
            // ========== SETUJUI ==========
            $dariRak = $arsip->rak_id;
            $dariBox = $arsip->box_id;
            $dariRakNomor = $arsip->rak ? $arsip->rak->nomor_rak : null;
            $dariBoxNomor = $arsip->box ? $arsip->box->nomor_box : null;

            $rakBaru = MasterRak::find($request->rak_id_baru);
            $boxBaru = MasterBox::find($request->box_id_baru);

            // Update arsip
            $arsip->rak_id = $request->rak_id_baru;
            $arsip->box_id = $request->box_id_baru;
            $arsip->lokasi_arsip = $request->lokasi_tujuan;
            $arsip->tanggal_diverifikasi = now();
            $arsip->diverifikasi_oleh = auth()->id();
            $arsip->status_pindah = 'DIPINDAHKAN'; // Set DIPINDAHKAN
            $arsip->catatan_verifikasi = '✅ DISETUJUI: Arsip diverifikasi dan diterima.';

            $arsip->skipHistory = true;
            $arsip->save();

            // Catat history
            HistoryPindah::create([
                'arsip_id' => $arsip->id,
                'dari_rak' => $dariRakNomor,
                'dari_box' => $dariBoxNomor,
                'ke_rak' => $rakBaru ? $rakBaru->nomor_rak : null,
                'ke_box' => $boxBaru ? $boxBaru->nomor_box : null,
                'tanggal_pindah' => now(),
                'alasan_pindah' => '✅ DISETUJUI: Arsip diterima di Unit Kearsipan',
                'user_id' => auth()->id()
            ]);

            // ========== UPDATE BAP DETAIL STATUS ==========
            // Update detail BAP menjadi DITERIMA
            BeritaAcaraDetail::where('arsip_id', $arsip->id)
                ->update(['status' => BeritaAcaraDetail::STATUS_DITERIMA]);

            // ========== UPDATE STATUS BAP ==========
            // Cari BAP yang terkait
            $bapDetail = BeritaAcaraDetail::where('arsip_id', $arsip->id)->first();
            if ($bapDetail) {
                $bap = $bapDetail->beritaAcara;
                
                // Cek apakah semua arsip di BAP sudah DITERIMA
                $allDiterima = BeritaAcaraDetail::where('bap_id', $bap->id)
                    ->whereHas('arsip', function($q) {
                        $q->where('status_pindah', '!=', 'DITERIMA');
                    })
                    ->doesntExist();
                
                // Jika semua arsip sudah DITERIMA, update BAP menjadi DISETUJUI
                if ($allDiterima) {
                    $bap->status = BeritaAcaraPindah::STATUS_DISETUJUI;
                    // $bap->tanggal_disetujui = now();
                    // $bap->disetujui_by = auth()->id();
                    $bap->save();
                }
            }

            DB::commit();

            return redirect()
                ->route('arsip-masuk.index')
                ->with('success', 'Arsip berhasil disetujui dan diterima.');

        } else {
            // ========== TOLAK ==========
            $arsip->status_pindah = 'DITOLAK';
            $arsip->tanggal_diverifikasi = now();
            $arsip->diverifikasi_oleh = auth()->id();
            $arsip->catatan_verifikasi = '❌ DITOLAK: ' . $request->alasan;
            $arsip->save();

            // Update BAP detail status
            $bapDetail = BeritaAcaraDetail::where('arsip_id', $arsip->id)->first();
            if ($bapDetail) {
                $bapDetail->status = BeritaAcaraDetail::STATUS_DITOLAK;
                $bapDetail->save();
            }

            HistoryPindah::create([
                'arsip_id' => $arsip->id,
                'tanggal_pindah' => now(),
                'alasan_pindah' => '❌ DITOLAK: ' . $request->alasan,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return redirect()
                ->route('arsip-masuk.index')
                ->with('success', 'Arsip telah ditolak.');
        }

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Verifikasi error:', ['error' => $e->getMessage()]);
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

// public function edit($id)
// {
//     $arsip = ArsipMasuk::with(['kodeKlasifikasi', 'subBagian', 'rak', 'box'])->findOrFail($id);
    
//     if (request()->ajax()) {
//         return response()->json([
//             'success' => true,
//             'data' => $arsip
//         ]);
//     }
    
//     return view('arsip-masuk.edit', compact('arsip'));
// }

// public function update(Request $request, $id)
// {
//     $arsip = Arsip::findOrFail($id);
    
//     $validated = $request->validate([
//         'kode_klasifikasi_id' => 'nullable|exists:kode_klasifikasi,id',
//         'sub_bagian_id' => 'nullable|exists:sub_bagian,id',
//         'uraian_arsip' => 'required|string|max:1000',
//         'tahun_arsip' => 'required|integer|min:1900|max:2100',
//         'jumlah_berkas' => 'required|integer|min:1',
//         'satuan_arsip' => 'required|string|max:50',
//         'rak_id' => 'nullable|exists:rak,id',
//         'box_id' => 'nullable|exists:box,id',
//         'catatan' => 'nullable|string|max:500',
//         'aktif_tahun' => 'nullable|integer|min:0',
//     'inaktif_tahun' => 'nullable|integer|min:0',
//     'keterangan_jra' => 'nullable|string|max:100',
//     'aktif_sampai' => 'nullable|date',
//     'inaktif_sampai' => 'nullable|date',
//     'status' => 'nullable|string|in:AKTIF,INAKTIF,PERMANEN,MUSNAH,HABIS RETENSI',
//     ]);
    
//     $arsip->update($validated);
//     $arsip->load(['kodeKlasifikasi', 'subBagian', 'rak', 'box']);
    
//     if (request()->ajax()) {
//         return response()->json([
//             'success' => true,
//             'message' => 'Arsip berhasil diperbarui',
//             'data' => $arsip
//         ]);
//     }
    
//     return redirect()->route('arsip-masuk.index')->with('success', 'Arsip berhasil diperbarui');
// }

//  public function updateField(Request $request, $id)
// {
//     try {
//         $request->validate([
//             'field' => 'required|string|in:kode_klasifikasi,aktif_tahun,inaktif_tahun,keterangan_jra',
//             'value' => 'nullable|string'
//         ]);

//         $arsip = Arsip::findOrFail($id);
//         $field = $request->field;
//         $value = $request->value;

//         // Mapping field ke database
//         $fieldMapping = [
//             'kode_klasifikasi' => 'kode_klasifikasi_id',
//             'aktif_tahun' => 'aktif_tahun',
//             'inaktif_tahun' => 'inaktif_tahun',
//             'keterangan_jra' => 'keterangan_jra'
//         ];

//         $dbField = $fieldMapping[$field];

//         // Jika value kosong atau '-', set ke null
//         if ($value === '' || $value === '-' || $value === null) {
//             $value = null;
//         }

//         // Update field
//         $arsip->$dbField = $value;
        
//         // Recalculate retensi (hitung ulang aktif_sampai, inaktif_sampai, status)
//         $arsip->recalculateRetensi();
//         $arsip->save();

//         // Load fresh data
//         $arsip->load('kodeKlasifikasi');

//         // Format response
//         return response()->json([
//             'success' => true,
//             'message' => 'Data berhasil diperbarui',
//             'data' => [
//                 'id' => $arsip->id,
//                 'aktif_tahun' => $arsip->aktif_tahun ?? '-',
//                 'inaktif_tahun' => $arsip->inaktif_tahun ?? '-',
//                 'keterangan_jra' => $arsip->keterangan_jra ?? '-',
//                 'aktif_sampai' => $arsip->aktif_sampai ? Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-',
//                 'inaktif_sampai' => $arsip->inaktif_sampai ? Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-',
//                 'status_arsip' => $arsip->status_arsip ?? 'AKTIF',
//                 'kode_klasifikasi' => $arsip->kodeKlasifikasi->kode ?? 'N/A'
//             ]
//         ]);

//     } catch (\Exception $e) {
//         \Log::error('Update Field Error:', [
//             'id' => $id,
//             'field' => $request->field,
//             'value' => $request->value,
//             'error' => $e->getMessage()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Terjadi kesalahan: ' . $e->getMessage()
//         ], 500);
//     }
// }

    /**
     * Edit - untuk modal edit (AJAX)
     */
    public function edit($id)
    {
        $arsip = Arsip::with(['kodeKlasifikasi', 'subBagian', 'rak', 'box'])->findOrFail($id);
        
        // Ambil data untuk dropdown
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        $rakOptions = MasterRak::orderBy('nomor_rak')->get();
        $boxOptions = MasterBox::orderBy('nomor_box')->get();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $arsip
            ]);
        }
        
        return view('arsip-masuk.edit', compact('arsip', 'kodeKlasifikasiOptions', 'subBagianOptions', 'rakOptions', 'boxOptions'));
    }

    /**
     * Update - untuk modal edit
     */
    public function update(Request $request, $id)
    {
        try {
            $arsip = Arsip::findOrFail($id);
            
            $validated = $request->validate([
                'kode_klasifikasi_id' => 'nullable|exists:kode_klasifikasis,id',
                'sub_bagian_id' => 'nullable|exists:sub_bagians,id',
                'uraian_arsip' => 'required|string|min:30',
                'tahun_arsip' => 'required|integer|min:1900|max:2100',
                'jumlah_berkas' => 'required|integer|min:1',
                'satuan_arsip' => 'required|string|max:50',
                'rak_id' => 'nullable|exists:master_raks,id',
                'box_id' => 'nullable|exists:master_box,id',
                'catatan' => 'nullable|string|max:500',
                'aktif_tahun' => 'nullable|string|max:100',
                'inaktif_tahun' => 'nullable|string|max:100',
                'keterangan_jra' => 'nullable|string|max:100',
                'aktif_sampai' => 'nullable|date',
                'inaktif_sampai' => 'nullable|date',
                'status' => 'nullable|string|in:AKTIF,INAKTIF,PERMANEN,MUSNAH,HABIS_RETENSI',
            ]);
            
            // Update data
            foreach ($validated as $key => $value) {
                $arsip->$key = $value;
            }
            
            // Recalculate retensi
            $arsip->recalculateRetensi();
            $arsip->save();
            
            $arsip->load(['kodeKlasifikasi', 'subBagian', 'rak', 'box']);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Arsip berhasil diperbarui',
                    'data' => $arsip
                ]);
            }
            
            return redirect()->route('arsip-masuk.index')->with('success', 'Arsip berhasil diperbarui');
            
        } catch (\Exception $e) {
            \Log::error('Update Error:', ['id' => $id, 'error' => $e->getMessage()]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }




public function downloadFile($id)
{
    try {
        $arsip = Arsip::find($id);
        
        if (!$arsip) {
            return redirect()->route('arsip-masuk.index')
                ->with('error', 'Arsip tidak ditemukan.');
        }

        if (!$arsip->file_dokumen) {
            return redirect()->back()
                ->with('error', 'File dokumen tidak ditemukan.');
        }

        // Coba beberapa kemungkinan path
        $paths = [
            storage_path('app/public/arsip/' . $arsip->file_dokumen),
            storage_path('app/arsip/' . $arsip->file_dokumen),
            public_path('storage/arsip/' . $arsip->file_dokumen),
        ];

        $filePath = null;
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath) {
            \Log::error('File tidak ditemukan di semua lokasi:', [
                'arsip_id' => $arsip->id,
                'file' => $arsip->file_dokumen,
                'paths' => $paths
            ]);
            return redirect()->back()
                ->with('error', 'File fisik tidak ditemukan di server.');
        }

        // Ambil ekstensi file
        $extension = pathinfo($arsip->file_dokumen, PATHINFO_EXTENSION);
        $filename = 'Dokumen_' . ($arsip->kodeKlasifikasi->kode ?? 'arsip') . '_' . $arsip->id . '.' . $extension;

        // Return download dengan header yang benar
        return response()->download($filePath, $filename, [
            'Content-Type' => $this->getContentType($extension),
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error download file:', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        return redirect()->back()
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

/**
 * Get Content Type based on file extension
 */
private function getContentType($extension)
{
    $types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
    ];
    
    return $types[$extension] ?? 'application/octet-stream';
}



public function updateField(Request $request, $id)
{
    try {
        $request->validate([
            'field' => 'required|string|in:kode_klasifikasi_id,rak_id,box_id,aktif_tahun,inaktif_tahun,keterangan_jra',
            'value' => 'nullable|string'
        ]);

        $arsip = Arsip::findOrFail($id);
        $field = $request->field;
        $value = $request->value;

        // Jika value kosong, set ke null
        if ($value === '' || $value === '-' || $value === null) {
            $value = null;
        }

        // Untuk numeric fields (kode_klasifikasi_id, rak_id, box_id)
        if (in_array($field, ['kode_klasifikasi_id', 'rak_id', 'box_id'])) {
            if ($value !== null) {
                $value = (int) $value;
                // Validasi foreign key
                $tableMap = [
                    'kode_klasifikasi_id' => 'kode_klasifikasis',
                    'rak_id' => 'master_raks',
                    'box_id' => 'master_box',
                ];
                if (!DB::table($tableMap[$field])->where('id', $value)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data referensi tidak ditemukan.'
                    ], 422);
                }
            }
        }

        // Untuk aktif_tahun dan inaktif_tahun (string)
        if (in_array($field, ['aktif_tahun', 'inaktif_tahun']) && $value !== null) {
            // Pastikan mengandung angka
            if (!preg_match('/\d+/', $value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tahun harus mengandung angka.'
                ], 422);
            }
        }

        // Update field
        $arsip->$field = $value;
        
        // ====== RECALCULATE RETENSI ======
        // Panggil method recalculateRetensi di model
        $arsip->recalculateRetensi();
        $arsip->save();

        // Load fresh data
        $arsip->load(['kodeKlasifikasi', 'rak', 'box']);

        // Format response
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => [
                'id' => $arsip->id,
                'kode_klasifikasi_id' => $arsip->kode_klasifikasi_id,
                'kode_klasifikasi' => $arsip->kodeKlasifikasi ? $arsip->kodeKlasifikasi->kode . ' - ' . $arsip->kodeKlasifikasi->uraian : 'N/A',
                'rak_id' => $arsip->rak_id,
                'rak_nomor' => $arsip->rak ? $arsip->rak->nomor_rak : null,
                'box_id' => $arsip->box_id,
                'box_nomor' => $arsip->box ? $arsip->box->nomor_box : null,
                'aktif_tahun' => $arsip->aktif_tahun ?? '-',
                'inaktif_tahun' => $arsip->inaktif_tahun ?? '-',
                'keterangan_jra' => $arsip->keterangan_jra ?? '-',
                'aktif_sampai' => $arsip->aktif_sampai ? Carbon::parse($arsip->aktif_sampai)->format('d/m/Y') : '-',
                'inaktif_sampai' => $arsip->inaktif_sampai ? Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y') : '-',
                'status_arsip' => $arsip->status_arsip ?? 'AKTIF',
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Update Field Error:', [
            'id' => $id,
            'field' => $request->field,
            'value' => $request->value,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}
}