<?php
// app/Http/Controllers/SubBagianDashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arsip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubBagianDashboardController extends Controller
{
    /**
     * Tampilkan dashboard sub-bagian
     */
    public function index()
    {
        $user = Auth::user();

        // Pastikan user punya sub_bagian_id
        if (!$user->sub_bagian_id) {
            return redirect()->route('home')
                ->with('error', 'Sub Bagian tidak ditemukan.');
        }

        // Query arsip milik sub-bagian
        $arsipQuery = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->whereIn('status_pindah', [
                'DIPINDAHKAN',
                'DITOLAK',
                'DIAJUKAN',
                'BELUM'
            ]);

        // Jumlah total arsip
        $totalArsip = $arsipQuery->count();

        // Jumlah berdasarkan status_pindah
        $arsipDipindahkan = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'DIPINDAHKAN')
            ->count();

        $arsipDiajukan = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'DIAJUKAN')
            ->count();

        $arsipDitolak = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'DITOLAK')
            ->count();

        $arsipBelumDipindahkan = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'BELUM')
            ->count();

        // ========== AMBIL DATA ARSIP DITOLAK DENGAN CATATAN ==========
        $arsipDitolakList = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'DITOLAK')
            ->whereNotNull('catatan_verifikasi')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get(['id', 'uraian_arsip', 'catatan_verifikasi', 'updated_at', 'kode_klasifikasi_id']);

        // Load relasi kode klasifikasi
        $arsipDitolakList->load('kodeKlasifikasi');

        // ========== HITUNG JUMLAH ARSIP DITOLAK YANG BELUM DIPERBAIKI ==========
        $arsipDitolakBelumDiperbaiki = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'DITOLAK')
            ->whereNull('catatan_perbaikan')
            ->count();

        // Jumlah arsip per status_arsip
        $statusOptions = ['AKTIF', 'INAKTIF', 'HABIS_RETENSI', 'PERMANEN', 'MUSNAH'];
        $arsipPerStatus = [];
        foreach ($statusOptions as $status) {
            $arsipPerStatus[$status] = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
                ->where('status_arsip', $status)
                ->count();
        }

        // Data untuk chart distribusi per tahun
        $arsipPerTahun = Arsip::where('sub_bagian_id', Auth::user()->sub_bagian_id)
            ->whereIn('status_pindah', ['BELUM', 'DIAJUKAN', 'DIPINDAHKAN', 'DITOLAK'])
            ->select('tahun_arsip', DB::raw('COUNT(*) as total'))
            ->groupBy('tahun_arsip')
            ->orderBy('tahun_arsip', 'asc')
            ->get();

        // Arsip terbaru untuk tabel (opsional)
        $arsipTerbaru = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->whereIn('status_pindah', ['BELUM', 'DIAJUKAN', 'DITOLAK'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
// Arsip yang belum memiliki file dokumen maupun link foto
$arsipBelumUploadDokumen = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
    ->whereIn('status_pindah', ['BELUM', 'DIAJUKAN', 'DIPINDAHKAN'])
    ->where('status_arsip', '!=', 'NON_ARSIP')
    ->where(function ($query) {
        $query->whereNull('file_dokumen')
              ->orWhere('file_dokumen', '');
    })
    ->where(function ($query) {
        $query->whereNull('link_foto')
              ->orWhere('link_foto', '');
    })
    ->count();
        return view('subbagian.dashboard', [
            'totalArsip' => $totalArsip,
            'arsipDipindahkan' => $arsipDipindahkan,
            'arsipDiajukan' => $arsipDiajukan,
            'arsipDitolak' => $arsipDitolak,
            'arsipBelumDipindahkan' => $arsipBelumDipindahkan,
            'arsipPerStatus' => $arsipPerStatus,
            'arsipPerTahun' => $arsipPerTahun,
            'arsipTerbaru' => $arsipTerbaru,
            'arsipDitolakList' => $arsipDitolakList,
            'arsipDitolakBelumDiperbaiki' => $arsipDitolakBelumDiperbaiki,
            'arsipBelumUploadDokumen' => $arsipBelumUploadDokumen,
        ]);
    }
}