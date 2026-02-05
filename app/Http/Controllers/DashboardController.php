<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik arsip berdasarkan status_arsip
        $totalArsip = Arsip::count();
        
        // PERHATIKAN: Gunakan value yang sesuai dengan ENUM di database
        // Cek nilai ENUM di database untuk status_arsip
        $arsipAktif = Arsip::where('status_arsip', 'AKTIF')->count(); // 'AKTIF' bukan 'aktif'
        $arsipInaktif = Arsip::where('status_arsip', 'INAKTIF')->count(); // 'INAKTIF' bukan 'inaktif'
        
        // Di database Anda: status_arsip enum('AKTIF', 'UMSUL_MUSNAH', 'PERMANEN')
        // 'UMSUL_MUSNAH' bukan 'MUSNAH'
        $arsipUsulMusnah = Arsip::where('status_arsip', 'USUL_MUSNAH')->count();
        $arsipMusnah = Arsip::where('status_arsip', 'MUSNAH')->count();
        $arsipPermanen = Arsip::where('status_arsip', 'PERMANEN')->count();
        
        // Arsip inaktif yang perlu ditindaklanjuti
        $arsipPerluTindak = $arsipInaktif;
        
        // Data untuk chart distribusi tahun
        $arsipPerTahun = Arsip::selectRaw('tahun_arsip as tahun, COUNT(*) as total')
            ->groupBy('tahun_arsip')
            ->orderBy('tahun_arsip')
            ->get();
        
        // Arsip terbaru - PERBAIKI: Tambahkan eager loading dan select kolom yang benar
        $arsipTerbaru = Arsip::with(['kodeKlasifikasi', 'subBagian'])
            ->select('id', 'kode_klasifikasi_id', 'uraian_arsip', 'sub_bagian_id', 'tahun_arsip', 'status_arsip')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();
        
        // Ringkasan per sub bagian
        $arsipPerSubBagian = Arsip::selectRaw('sub_bagian_id, COUNT(*) as total')
            ->with('subBagian')
            ->groupBy('sub_bagian_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        // Data untuk chart status arsip
        $statusData = [
            'AKTIF' => $arsipAktif,
            'INAKTIF' => $arsipInaktif,
            'UMSUL_MUSNAH' => $arsipMusnah, // Sesuai database
            'PERMANEN' => $arsipPermanen,
        ];
        
        return view('dashboard', compact(
            'totalArsip',
            'arsipAktif',
            'arsipInaktif',
            'arsipMusnah',
            'arsipUsulMusnah',
            'arsipPermanen',
            'arsipPerluTindak',
            'arsipPerTahun',
            'arsipTerbaru',
            'arsipPerSubBagian',
            'statusData'
        ));
    }
}