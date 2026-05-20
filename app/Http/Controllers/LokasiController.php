<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:admin,super_admin');
    // }

    // Layer 1: Tampilkan semua ruangan (hanya yang memiliki nilai)
    public function index()
    {
        // Ambil ruangan yang tidak null dan tidak kosong
        $ruangans = Arsip::select('lokasi_arsip')
            ->whereIn('lokasi_arsip', [
                'RECORD_CENTER_PERMANEN',
                'RECORD_CENTER_INAKTIF'
            ])
            ->distinct()
            ->pluck('lokasi_arsip')
            ->toArray();

        // Hitung arsip yang belum punya ruangan
        $arsipTanpaRuangan = Arsip::where(function ($query) {
            $query->whereNull('lokasi_arsip')
                ->orWhere('lokasi_arsip', '');
        })
        ->whereIn('status_pindah', ['LANGSUNG', 'DIPINDAHKAN'])
        ->count();

        $ruanganLabels = [
            'RECORD_CENTER_PERMANEN' => 'Record Center Permanen',
            'RECORD_CENTER_INAKTIF' => 'Record Center Inaktif',
        ];

        return view('manajemen-lokasi.index', compact('ruangans', 'ruanganLabels', 'arsipTanpaRuangan'));
    }

    // Layer 2: Rak dalam ruangan
    public function listRak($ruangan)
    {
        if (empty($ruangan)) {
            return redirect()->route('manajemen-lokasi.index')
                ->with('error', 'Ruangan tidak valid.');
        }

        $raks = Arsip::where('lokasi_arsip', $ruangan)
            ->whereNotNull('nomor_rak')
            ->where('nomor_rak', '!=', '')
            ->distinct()
            ->pluck('nomor_rak')
            ->toArray();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('manajemen-lokasi.rak', compact('ruangan', 'ruanganLabel', 'raks'));
    }

    // Layer 3: Box dalam rak
    public function listBox($ruangan, $rak)
    {
        if (empty($ruangan) || empty($rak)) {
            return redirect()->route('manajemen-lokasi.index')
                ->with('error', 'Parameter tidak valid.');
        }

        $boxes = Arsip::where('lokasi_arsip', $ruangan)
            ->where('nomor_rak', $rak)
            ->whereNotNull('nomor_box')
            ->where('nomor_box', '!=', '')
            ->distinct()
            ->pluck('nomor_box')
            ->toArray();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('manajemen-lokasi.box', compact('ruangan', 'ruanganLabel', 'rak', 'boxes'));
    }

    // Layer 4: Daftar arsip dalam box
    public function listArsip($ruangan, $rak, $box)
    {
        if (empty($ruangan) || empty($rak) || empty($box)) {
            return redirect()->route('manajemen-lokasi.index')
                ->with('error', 'Parameter tidak valid.');
        }

        $arsips = Arsip::where('lokasi_arsip', $ruangan)
            ->where('nomor_rak', $rak)
            ->where('nomor_box', $box)
            ->orderBy('tahun_arsip', 'desc')
            ->get();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('manajemen-lokasi.arsip', compact('ruangan', 'ruanganLabel', 'rak', 'box', 'arsips'));
    }

    private function getLabelRuangan($key)
    {
        $labels = [
            'RUANG_SUBBAGIAN_UMUM_LOGISTIK' => 'Subbagian Umum & Logistik',
            'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM' => 'Subbagian Parmas & SDM',
            'RUANG_SUBBAGIAN_KEUANGAN' => 'Subbagian Keuangan',
            'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI' => 'Subbagian Perencanaan, Data & Informasi',
            'RUANG_SUBBAGIAN_TEKNIS' => 'Subbagian Teknis',
            'RUANG_SUBBAGIAN_HUKUM' => 'Subbagian Hukum',
            'RECORD_CENTER_PERMANEN' => 'Record Center Permanen',
            'RECORD_CENTER_INAKTIF' => 'Record Center Inaktif',
        ];
        return $labels[$key] ?? $key;
    }
}