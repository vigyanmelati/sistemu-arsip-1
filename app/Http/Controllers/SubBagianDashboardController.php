<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Arsip;
use Illuminate\Support\Facades\Auth;

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
            'BELUM'
        ]);
        

        // Jumlah total arsip
        $totalArsip = $arsipQuery->count();

        // Jumlah berdasarkan status_pindah
        $arsipDipindahkan = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'dipindahkan')
            ->count();

        $arsipDitolak = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'ditolak')
            ->count();

        $arsipBelumDipindahkan = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->where('status_pindah', 'belum')
            ->count();

        // Jumlah arsip per status_arsip
        $statusOptions = ['AKTIF', 'INAKTIF', 'USUL_MUSNAH', 'PERMANEN', 'MUSNAH'];
        $arsipPerStatus = [];
        foreach ($statusOptions as $status) {
            $arsipPerStatus[$status] = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
                ->where('status_arsip', $status)
                ->count();
        }

        // Data untuk chart distribusi per tahun (opsional)
        $arsipPerTahun = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->selectRaw('YEAR(tanggal_arsip) as tahun, COUNT(*) as total')
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get();

        // Arsip terbaru untuk tabel (opsional)
        $arsipTerbaru = Arsip::where('sub_bagian_id', $user->sub_bagian_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('subbagian.dashboard', [
            'totalArsip' => $totalArsip,
            'arsipDipindahkan' => $arsipDipindahkan,
            'arsipDitolak' => $arsipDitolak,
            'arsipBelumDipindahkan' => $arsipBelumDipindahkan,
            'arsipPerStatus' => $arsipPerStatus,
            'arsipPerTahun' => $arsipPerTahun,
            'arsipTerbaru' => $arsipTerbaru,
        ]);
    }
}