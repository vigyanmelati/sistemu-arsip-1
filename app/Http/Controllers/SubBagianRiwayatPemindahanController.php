<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class SubBagianRiwayatPemindahanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ambil arsip dengan status DIPINDAHKAN atau DITOLAK
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian'])
            ->where('sub_bagian_id', $user->sub_bagian_id)
            ->whereIn('status_pindah', ['DIPINDAHKAN', 'DITOLAK','DIAJUKAN','DIPERBAIKI'])
            ->orderBy('updated_at', 'desc');

        // Filter berdasarkan status
        if ($request->has('status_pindah') && $request->status_pindah != '') {
            $query->where('status_pindah', $request->status_pindah);
        }

        // Filter berdasarkan tahun
        if ($request->has('tahun') && $request->tahun != '') {
            $query->whereYear('updated_at', $request->tahun);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('uraian_arsip', 'like', "%{$request->search}%")
                  ->orWhereHas('kodeKlasifikasi', function($sub) use ($request){
                      $sub->where('kode','like',"%{$request->search}%")
                          ->orWhere('uraian','like',"%{$request->search}%");
                  });
            });
        }

        $arsips = $query->paginate(15);

        // Data untuk filter
        $tahunOptions = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->whereIn('status_pindah', ['DIPINDAHKAN', 'DITOLAK'])
            ->selectRaw('YEAR(updated_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $statusOptions = [
            'DIPINDAHKAN' => 'Dipindahkan',
            'DITOLAK' => 'Ditolak'
        ];

        return view('subbagian.riwayat-pemindahan.index', compact(
            'arsips', 'tahunOptions', 'statusOptions'
        ));
    }

    public function show(Arsip $arsip)
    {
        $user = Auth::user();
        
        // Pastikan arsip milik sub bagian user
        if ($arsip->sub_bagian_id != $user->sub_bagian_id) {
            abort(403);
        }

        // Pastikan arsip memiliki status DIPINDAHKAN atau DITOLAK
        if (!in_array($arsip->status_pindah, ['DIPINDAHKAN', 'DITOLAK', 'DIAJUKAN','DIPERBAIKI'])) {
            abort(404);
        }

        // Load verifikator jika ada
        // $arsip->load(['verifikator' => function($query) {
        //     $query->select('id', 'name');
        // }]);

        return view('subbagian.riwayat-pemindahan.show', compact('arsip'));
    }

    public function perbaikiArsip(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        
        // Validasi akses
        if ($arsip->sub_bagian_id != $user->sub_bagian_id || $arsip->status_pindah != 'DITOLAK') {
            abort(403);
        }

        // Simpan catatan perbaikan
        // $validated = $request->validate([
        //     'catatan_perbaikan' => 'required|string|max:1000'
        // ]);

        $arsip->update([
            // 'catatan_perbaikan' => $validated['catatan_perbaikan'],
            'status_pindah' => 'DIPERBAIKI'
        ]);

        return redirect()->route('subbagian.riwayat-pemindahan.index')
            ->with('success', 'Arsip telah ditandai sebagai diperbaiki. Silakan ajukan kembali untuk verifikasi.');
    }

    // public function ajukanKembali(Request $request, Arsip $arsip)
    // {
    //     $user = Auth::user();
        
    //     // Validasi akses
    //     if ($arsip->sub_bagian_id != $user->sub_bagian_id) {
    //         abort(403);
    //     }

    //     // Validasi file berita acara
    //     $request->validate([
    //         'file_berita_acara_baru' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
    //     ]);

    //     // Simpan file berita acara baru
    //     $file = $request->file('file_berita_acara_baru');
    //     $fileName = time().'_'.$file->getClientOriginalName();
    //     $file->storeAs('arsip', $fileName, 'public');

    //     // Update arsip
    //     $arsip->update([
    //         'file_berita_acara' => $fileName,
    //         'status_pindah' => 'DIAJUKAN',
    //         'catatan_perbaikan' => null,
    //         'updated_at' => now()
    //     ]);

    //     return redirect()->route('subbagian.riwayat-pemindahan.index')
    //         ->with('success', 'Arsip berhasil diajukan kembali untuk proses pemindahan.');
    // }
    public function editPerbaikan(Arsip $arsip)
    {
        $user = Auth::user();
        
        // Validasi akses
        if ($arsip->sub_bagian_id != $user->sub_bagian_id || $arsip->status_pindah != 'DITOLAK') {
            abort(403);
        }

        // Load data yang diperlukan
        $arsip->load(['kodeKlasifikasi', 'subBagian']);
        $kodeKlasifikasis = KodeKlasifikasi::orderBy('kode')->get();

        return view('subbagian.riwayat-pemindahan.edit-perbaikan', compact('arsip', 'kodeKlasifikasis'));
    }

    public function updatePerbaikan(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        
        // Validasi akses
        if ($arsip->sub_bagian_id != $user->sub_bagian_id || $arsip->status_pindah != 'DITOLAK') {
            abort(403);
        }

        // Validasi data
        $validated = $request->validate([
            'kode_klasifikasi_id' => 'required|exists:kode_klasifikasis,id',
            'uraian_arsip' => 'required|string|max:500',
            'tahun_arsip' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'tanggal_arsip' => 'required|date',
            'jumlah_berkas' => 'required|integer|min:1',
            'satuan_arsip' => 'required|string|max:20',
            
            // Data retensi (jika diperlukan, uncomment)
            // 'aktif_tahun' => 'required',
            // 'inaktif_tahun' => 'required',
            // 'tanggal_referensi' => 'nullable|date',
            // 'keterangan_jra' => 'required|in:MUSNAH,PERMANEN,BELUM DITENTUKAN',
            
            // Lokasi
            'nomor_rak' => 'nullable|string|max:50',
            'nomor_box' => 'nullable|string|max:50',
            'nomor_sampul' => 'nullable|string|max:50',
            
            // Kondisi
            'tingkat_perkembangan' => 'required|string|max:50',
            'keterangan' => 'required|string|max:100',
            'media_arsip' => 'required|string|max:50',
            
            // Berita acara baru (opsional)
            'file_berita_acara_baru' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // ← perbaikan typo
            
            // Catatan perbaikan (jika diperlukan)
            // 'catatan_perbaikan' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Upload file berita acara baru (jika ada)
            if ($request->hasFile('file_berita_acara_baru')) {
                // Hapus file lama jika ada
                if ($arsip->file_berita_acara) {
                    Storage::disk('public')->delete('arsip/' . $arsip->file_berita_acara);
                }
                
                $file = $request->file('file_berita_acara_baru');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('arsip', $fileName, 'public');
                $validated['file_berita_acara'] = $fileName;
            }

            // Update status
            $validated['status_pindah'] = 'DIPERBAIKI';
            $validated['updated_at'] = now();

            // Hapus field yang tidak ada di database
            unset($validated['file_berita_acara_baru']);

            // Update arsip
            $arsip->update($validated);

            DB::commit();

            return redirect()->route('subbagian.riwayat-pemindahan.show', $arsip->id)
                ->with('success', 'Arsip berhasil diperbaiki. Silakan ajukan kembali untuk verifikasi.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function ajukanKembali(Request $request, Arsip $arsip)
    {
        $user = Auth::user();
        
        // Validasi akses dan status
        if ($arsip->sub_bagian_id != $user->sub_bagian_id || $arsip->status_pindah != 'DIPERBAIKI') {
            abort(403);
        }

        // Validasi bahwa arsip sudah diperbaiki
        // if (!$arsip->catatan_perbaikan) {
        //     return back()->with('error', 'Arsip harus diperbaiki terlebih dahulu sebelum diajukan kembali.');
        // }

        DB::beginTransaction();
        try {
            // Update status menjadi DIAJUKAN
            $arsip->update([
                'status_pindah' => 'DIAJUKAN',
                'catatan_verifikasi' => null, // Reset catatan verifikasi lama
                'diverifikasi_oleh' => null,
                'tanggal_diverifikasi' => null,
                'updated_at' => now()
            ]);

            // Log aktivitas
            // activity()
            //     ->causedBy($user)
            //     ->performedOn($arsip)
            //     ->withProperties([
            //         'status_sebelumnya' => 'DIPERBAIKI',
            //         // 'catatan_perbaikan' => $arsip->catatan_perbaikan
            //     ])
                // ->log('Mengajukan kembali arsip setelah perbaikan');

            DB::commit();

            return redirect()->route('subbagian.riwayat-pemindahan.index')
                ->with('success', 'Arsip berhasil diajukan kembali untuk proses verifikasi.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}