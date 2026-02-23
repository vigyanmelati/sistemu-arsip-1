<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\SubBagian;
use App\Models\HistoryPindah;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminArsipMasukController extends Controller
{
    /**
     * Display a listing of arsip masuk.
     */
    public function index(Request $request)
    {
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian', 'creator'])
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

        return view('arsip-masuk.index', compact(
            'arsips', 
            'subBagianOptions',
            'tahunOptions',
            'arsipMasukCount'
        ));
    }

    /**
     * Display the specified arsip.
     */
    public function show(Arsip $arsip)
    {
        // Pastikan arsip sudah diajukan pemindahan
        if ($arsip->status_pindah !== 'DIAJUKAN') {
            return redirect()->route('arsip-masuk.index')
                ->with('error', 'Arsip belum diajukan pemindahan atau sudah diproses.');
        }

        // Load history pindah dan berita acara
        $arsip->load([
            'historyPindah' => function($query) {
                $query->orderBy('tanggal_pindah', 'desc');
            },
            'beritaAcaraPindah' => function($query) {
                $query->latest();
            }
        ]);

        return view('arsip-masuk.show', compact('arsip'));
    }

    /**
     * Terima pengajuan pemindahan arsip.
     */
    public function terima(Request $request, Arsip $arsip)
    {
        $request->validate([
            'nomor_rak_baru' => 'required|string|max:50',
            'nomor_box_baru' => 'required|string|max:50',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($arsip->status_pindah !== 'DIAJUKAN') {
            return back()->with('error', 'Status arsip tidak valid.');
        }

        DB::beginTransaction();
        try {
            // Simpan lokasi lama
            $dariRak = $arsip->nomor_rak;
            $dariBox = $arsip->nomor_box;

            // SIMPAN lokasi baru TANPA mengubah status
            // $arsip->nomor_rak = $request->nomor_rak_baru;
            // $arsip->nomor_box = $request->nomor_box_baru;
            // $arsip->tanggal_diverifikasi = now();
            // $arsip->diverifikasi_oleh = auth()->id();
            // $arsip->catatan_verifikasi = $request->catatan;
            // // status_pindah tetap DIAJUKAN
            // $arsip->skipHistory = true;
            // $arsip->save();

            $arsip->nomor_rak = $request->nomor_rak_baru;
            $arsip->nomor_box = $request->nomor_box_baru;
            $arsip->tanggal_diverifikasi = now();
            $arsip->diverifikasi_oleh = auth()->id();
            $arsip->catatan_verifikasi = $request->catatan;

            $arsip->skipHistory = true;
            $arsip->save();

            // Catat history (opsional tapi bagus)
            HistoryPindah::create([
                'arsip_id' => $arsip->id,
                'dari_rak' => $dariRak,
                'dari_box' => $dariBox,
                'ke_rak' => $request->nomor_rak_baru,
                'ke_box' => $request->nomor_box_baru,
                'tanggal_pindah' => now(),
                'alasan_pindah' => 'Verifikasi lokasi arsip (belum dipindahkan)',
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return redirect()
                ->route('arsip-masuk.index')
                ->with('success', 'Verifikasi berhasil. Lokasi baru disimpan, arsip belum dipindahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tolak pengajuan pemindahan arsip.
     */
    // App/Http/Controllers/AdminArsipMasukController.php
    public function tolak(Request $request, Arsip $arsip)
    {
        $request->validate([
            'alasan' => 'required|string|max:500'
        ]);

        // Debug: lihat data yang masuk
        \Log::info('Data penolakan diterima:', [
            'arsip_id' => $arsip->id,
            'alasan' => $request->alasan,
            'user_id' => auth()->id()
        ]);

        // Pastikan arsip masih dalam status DIAJUKAN
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
            // Update status arsip - gunakan update langsung
            $updateData = [
                'status_pindah' => 'DITOLAK',
                'tanggal_diverifikasi' => now(),
                'diverifikasi_oleh' => auth()->id(),
                'catatan_verifikasi' => $request->alasan // Pastikan ini ada
            ];
            
            \Log::info('Data yang akan diupdate:', $updateData);
            
            // Coba dengan cara berbeda
            $arsip->catatan_verifikasi = $request->alasan;
            $arsip->status_pindah = 'DITOLAK';
            $arsip->tanggal_diverifikasi = now();
            $arsip->diverifikasi_oleh = auth()->id();
            $arsip->save();

            DB::commit();

            \Log::info('Arsip berhasil ditolak:', ['arsip_id' => $arsip->id]);

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
            \Log::error('Error menolak arsip:', [
                'arsip_id' => $arsip->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Proses pemindahan arsip ke master arsip.
     */
    public function pindahkan(Request $request, Arsip $arsip)
    {
        $request->validate([
            'status_arsip_setelah_pindah' => 'required|in:AKTIF,INAKTIF,PERMANEN,MUSNAH',
            'nomor_rak_pemindahan' => 'nullable|string|max:50',
            'nomor_box_pemindahan' => 'nullable|string|max:50'
        ]);

        // Pastikan arsip sudah DIPINDAHKAN
        if ($arsip->status_pindah !== 'DIPINDAHKAN') {
            return back()->with('error', 'Arsip harus DIPINDAHKAN terlebih dahulu sebelum dipindahkan.');
        }

        DB::beginTransaction();
        try {
            // Simpan nilai lama sebelum diubah
            $dariRak = $arsip->nomor_rak;
            $dariBox = $arsip->nomor_box;
            $tanggalPindah = now();

            // Jika ada lokasi pemindahan baru, update
            if ($request->filled('nomor_rak_pemindahan') || $request->filled('nomor_box_pemindahan')) {
                if ($request->nomor_rak_pemindahan) {
                    $arsip->nomor_rak = $request->nomor_rak_pemindahan;
                }
                if ($request->nomor_box_pemindahan) {
                    $arsip->nomor_box = $request->nomor_box_pemindahan;
                }
                
                // Catat history perpindahan
                HistoryPindah::create([
                    'arsip_id' => $arsip->id,
                    'dari_rak' => $dariRak,
                    'dari_box' => $dariBox,
                    'ke_rak' => $arsip->nomor_rak,
                    'ke_box' => $arsip->nomor_box,
                    'tanggal_pindah' => $tanggalPindah,
                    'alasan_pindah' => 'Pemindahan internal Unit Kearsipan - Status: ' . $request->status_arsip_setelah_pindah,
                    'user_id' => auth()->id()
                ]);
            }

            // Update status arsip
            $arsip->status_pindah = 'DIPINDAHKAN';
            $arsip->tanggal_dipindahkan = $tanggalPindah;
            $arsip->status_arsip = $request->status_arsip_setelah_pindah;
            $arsip->skipHistory = true;
            $arsip->save();

            // Log aktivitas
            activity()
                ->causedBy(auth()->user())
                ->performedOn($arsip)
                ->withProperties([
                    'status_arsip_setelah_pindah' => $request->status_arsip_setelah_pindah,
                    'lokasi_baru' => $arsip->nomor_rak . '/' . $arsip->nomor_box
                ])
                ->log('Memindahkan arsip ke master arsip');

            DB::commit();

            return redirect()->route('arsip-masuk.index')
                ->with('success', 'Arsip berhasil dipindahkan ke master arsip.' . 
                       ($request->filled('nomor_rak_pemindahan') ? ' History perpindahan dicatat.' : ''));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Proses multiple arsip sekaligus.
     */
    public function prosesMultiple(Request $request)
    {
        $request->validate([
            'arsip_ids'   => 'required|array',
            'arsip_ids.*' => 'exists:arsips,id',
            'action'      => 'required|in:set_lokasi,pindahkan',
            'nomor_rak'   => 'nullable|string|max:50',
            'nomor_box'   => 'nullable|string|max:50',
            'catatan'     => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $arsips = Arsip::whereIn('id', $request->arsip_ids)
                ->lockForUpdate()
                ->get();

            foreach ($arsips as $arsip) {

                /* ============================
                 | AKSI 1 : SET LOKASI
                 | ============================ */
                if ($request->action === 'set_lokasi') {

                    if (!$request->nomor_rak || !$request->nomor_box) {
                        throw new \Exception('Nomor rak dan box wajib diisi.');
                    }
                    $arsip->skipHistory = true;
                    $arsip->update([
                        'nomor_rak' => $request->nomor_rak,
                        'nomor_box' => $request->nomor_box,
                    ]);

                    HistoryPindah::create([
                        'arsip_id' => $arsip->id,
                        'aksi'     => 'SET_LOKASI',
                        'dari_rak' => $arsip->getOriginal('nomor_rak'),
                        'dari_box' => $arsip->getOriginal('nomor_box'),
                        'ke_rak'   => $request->nomor_rak,
                        'ke_box'   => $request->nomor_box,
                        'tanggal_pindah' => now(),
                        'catatan'  => $request->catatan ?? 'Verifikasi lokasi arsip',
                        'user_id'  => auth()->id(),
                    ]);
                }

                /* ============================
                 | AKSI 2 : PINDAHKAN KE MASTER
                 | ============================ */
                if ($request->action === 'pindahkan') {

                    // WAJIB SUDAH ADA LOKASI
                    if (!$arsip->nomor_rak || !$arsip->nomor_box) {
                        throw new \Exception(
                            'Arsip belum memiliki nomor rak dan box.'
                        );
                    }
                    $arsip->skipHistory = true;
                    $arsip->update([
                        'status_pindah'       => 'DIPINDAHKAN',
                        'tanggal_dipindahkan' => now(),
                    ]);

                    HistoryPindah::create([
                        'arsip_id' => $arsip->id,
                        'aksi'     => 'PINDAHKAN',
                        'dari_rak' => $arsip->nomor_rak,
                        'dari_box' => $arsip->nomor_box,
                        'ke_rak'   => $arsip->nomor_rak,
                        'ke_box'   => $arsip->nomor_box,
                        'tanggal_pindah' => now(),
                        'catatan'  => $request->catatan ?? 'Arsip dipindahkan ke Unit Kearsipan',
                        'user_id'  => auth()->id(),
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Proses arsip berhasil.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ... (kode lainnya tetap sama) ...

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
}