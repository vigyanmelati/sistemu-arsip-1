<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokasiSubBagianController extends Controller
{
    // Mapping sub_bagian_id -> ruangan
    private function getRuanganBySubBagian($subBagianId)
    {
        $mapping = [
            1 => 'RUANG_SUBBAGIAN_UMUM_LOGISTIK',
            2 => 'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM',
            3 => 'RUANG_SUBBAGIAN_KEUANGAN',
            4 => 'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI',
            5 => 'RUANG_SUBBAGIAN_TEKNIS',
            6 => 'RUANG_SUBBAGIAN_HUKUM',
        ];

        return $mapping[$subBagianId] ?? null;
    }

    // Layer 1: Ruangan (hanya 1 sesuai subbagian login)
    public function index()
    {
        $subBagianId = Auth::user()->sub_bagian_id;

        $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

        if (!$ruanganUser) {
            return redirect()->back()->with('error', 'Subbagian tidak valid');
        }

        $ruangans = [$ruanganUser];

        $ruanganLabels = [
            'RUANG_SUBBAGIAN_UMUM_LOGISTIK' => 'Subbagian Umum & Logistik',
            'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM' => 'Subbagian Parmas & SDM',
            'RUANG_SUBBAGIAN_KEUANGAN' => 'Subbagian Keuangan',
            'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI' => 'Subbagian Perencanaan, Data & Informasi',
            'RUANG_SUBBAGIAN_TEKNIS' => 'Subbagian Teknis',
            'RUANG_SUBBAGIAN_HUKUM' => 'Subbagian Hukum',
        ];

        $arsipTanpaRuangan = Arsip::where('sub_bagian_id', $subBagianId)
            ->where(function ($q) {
                $q->whereNull('lokasi_arsip')
                  ->orWhere('lokasi_arsip', '');
            })
            ->count();

        return view('subbagian.manajemen-lokasi.index', compact('ruangans', 'ruanganLabels', 'arsipTanpaRuangan'));
    }

    // Layer 2: Rak
    public function listRak($ruangan)
    {
        $subBagianId = Auth::user()->sub_bagian_id;
        $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

        if ($ruangan !== $ruanganUser) {
            return redirect()->route('manajemen-lokasi.index')
                ->with('error', 'Akses ditolak.');
        }

        $raks = Arsip::where('sub_bagian_id', $subBagianId)
            ->where('lokasi_arsip', $ruangan)
            ->whereNotNull('nomor_rak')
            ->where('nomor_rak', '!=', '')
            ->distinct()
            ->pluck('nomor_rak')
            ->toArray();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('subbagian.manajemen-lokasi.rak', compact('ruangan', 'ruanganLabel', 'raks'));
    }

    // Layer 3: Box
    public function listBox($ruangan, $rak)
    {
        $subBagianId = Auth::user()->sub_bagian_id;
        $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

        if ($ruangan !== $ruanganUser) {
            return redirect()->route('manajemen-lokasi.index')
                ->with('error', 'Akses ditolak.');
        }

        $boxes = Arsip::where('sub_bagian_id', $subBagianId)
            ->where('lokasi_arsip', $ruangan)
            ->where('nomor_rak', $rak)
            ->whereNotNull('nomor_box')
            ->where('nomor_box', '!=', '')
            ->distinct()
            ->pluck('nomor_box')
            ->toArray();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('subbagian.manajemen-lokasi.box', compact('ruangan', 'ruanganLabel', 'rak', 'boxes'));
    }

    // Layer 4: Arsip
    public function listArsip($ruangan, $rak, $box)
    {
        $subBagianId = Auth::user()->sub_bagian_id;
        $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

        if ($ruangan !== $ruanganUser) {
            return redirect()->route('manajemen-lokasi.index')
                ->with('error', 'Akses ditolak.');
        }

        $arsips = Arsip::where('sub_bagian_id', $subBagianId)
            ->where('lokasi_arsip', $ruangan)
            ->where('nomor_rak', $rak)
            ->where('nomor_box', $box)
            ->orderBy('tahun_arsip', 'desc')
            ->get();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('subbagian.manajemen-lokasi.arsip', compact('ruangan', 'ruanganLabel', 'rak', 'box', 'arsips'));
    }

    // Label ruangan
    private function getLabelRuangan($key)
    {
        $labels = [
            'RUANG_SUBBAGIAN_UMUM_LOGISTIK' => 'Subbagian Umum & Logistik',
            'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM' => 'Subbagian Parmas & SDM',
            'RUANG_SUBBAGIAN_KEUANGAN' => 'Subbagian Keuangan',
            'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI' => 'Subbagian Perencanaan, Data & Informasi',
            'RUANG_SUBBAGIAN_TEKNIS' => 'Subbagian Teknis',
            'RUANG_SUBBAGIAN_HUKUM' => 'Subbagian Hukum',
        ];

        return $labels[$key] ?? $key;
    }
}