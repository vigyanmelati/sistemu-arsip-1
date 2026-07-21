<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\MasterRak;
use App\Models\MasterBox;
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
        7 => 'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI', // ganti dari 4 ke 7
        5 => 'RUANG_SUBBAGIAN_TEKNIS',
        6 => 'RUANG_SUBBAGIAN_HUKUM',
    ];
    return $mapping[$subBagianId] ?? null;
}

    private function getSubBagianIdByRuangan($ruangan)
    {
        $mapping = [
            'RUANG_SUBBAGIAN_UMUM_LOGISTIK' => 1,
            'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM' => 2,
            'RUANG_SUBBAGIAN_KEUANGAN' => 3,
            'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI' => 4,
            'RUANG_SUBBAGIAN_TEKNIS' => 5,
            'RUANG_SUBBAGIAN_HUKUM' => 6,
        ];
        return $mapping[$ruangan] ?? null;
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
            ->where('status_pindah', 'BELUM')
            ->count();

        return view('subbagian.manajemen-lokasi.index', compact('ruangans', 'ruanganLabels', 'arsipTanpaRuangan'));
    }

    // Layer 2: Rak
   public function listRak($ruangan)
{
    $subBagianId = Auth::user()->sub_bagian_id;
    $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

    if ($ruangan !== $ruanganUser) {
        return redirect()->route('subbagian.manajemen-lokasi.index')
            ->with('error', 'Akses ditolak.');
    }

    // Ambil objek rak (bukan pluck)
    $raks = MasterRak::where('lokasi_arsip', $ruangan)
        ->orderBy('nomor_rak', 'asc')
        ->get(); // ← object

    $ruanganLabel = $this->getLabelRuangan($ruangan);

    return view('subbagian.manajemen-lokasi.rak', compact('ruangan', 'ruanganLabel', 'raks'));
}

public function listBox($ruangan, $rak)
{
    $subBagianId = Auth::user()->sub_bagian_id;
    $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

    if ($ruangan !== $ruanganUser) {
        return redirect()->route('subbagian.manajemen-lokasi.index')
            ->with('error', 'Akses ditolak.');
    }

    $rakModel = MasterRak::where('lokasi_arsip', $ruangan)
        ->where('nomor_rak', $rak)
        ->first();

    if (!$rakModel) {
        abort(404, 'Rak tidak ditemukan');
    }

    $boxes = MasterBox::where('rak_id', $rakModel->id)
        ->orderBy('nomor_box', 'asc')
        ->get(); // ← object

    $ruanganLabel = $this->getLabelRuangan($ruangan);

    return view('subbagian.manajemen-lokasi.box', compact('ruangan', 'ruanganLabel', 'rak', 'boxes', 'rakModel'));
}

    // Layer 4: Arsip
    public function listArsip($ruangan, $rak, $box)
    {
        $subBagianId = Auth::user()->sub_bagian_id;
        $ruanganUser = $this->getRuanganBySubBagian($subBagianId);

        if ($ruangan !== $ruanganUser) {
            return redirect()->route('subbagian.manajemen-lokasi.index')
                ->with('error', 'Akses ditolak.');
        }

        // Cari box model
        $rakModel = MasterRak::where('lokasi_arsip', $ruangan)
            ->where('nomor_rak', $rak)
            ->first();

        if (!$rakModel) {
            abort(404, 'Rak tidak ditemukan');
        }

        $boxModel = MasterBox::where('rak_id', $rakModel->id)
            ->where('nomor_box', $box)
            ->first();

        if (!$boxModel) {
            abort(404, 'Box tidak ditemukan');
        }

        // Ambil arsip berdasarkan box_id, dan pastikan sub_bagian_id sesuai
        $arsips = Arsip::where('box_id', $boxModel->id)
            ->where('sub_bagian_id', $subBagianId)
            ->orderBy('tahun_arsip', 'desc')
            ->get();

        $ruanganLabel = $this->getLabelRuangan($ruangan);

        return view('subbagian.manajemen-lokasi.arsip', compact('ruangan', 'ruanganLabel', 'rak', 'box', 'arsips'));
    }

    // Tambah Rak
    public function storeRak(Request $request)
    {
        $request->validate([
            'lokasi_arsip' => 'required|string',
            'nomor_rak' => 'required|string|unique:master_raks,nomor_rak,NULL,id,lokasi_arsip,' . $request->lokasi_arsip,
            'keterangan' => 'nullable|string',
        ]);

        MasterRak::create([
            'lokasi_arsip' => $request->lokasi_arsip,
            'nomor_rak' => $request->nomor_rak,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Rak berhasil ditambahkan.');
    }

    // Tambah Box
    public function storeBox(Request $request)
    {
        $request->validate([
            'rak_id' => 'required|exists:master_raks,id',
            'nomor_box' => 'required|string|unique:master_box,nomor_box,NULL,id,rak_id,' . $request->rak_id,
            'keterangan' => 'nullable|string',
        ]);

        MasterBox::create([
            'rak_id' => $request->rak_id,
            'nomor_box' => $request->nomor_box,
            // 'kapasitas' => $request->kapasitas,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Box berhasil ditambahkan.');
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
        ];
        return $labels[$key] ?? $key;
    }

    // ===================== UPDATE & DELETE RAK =====================
public function updateRak(Request $request, $id)
{
    $rak = MasterRak::findOrFail($id);
    $request->validate([
        'nomor_rak' => 'required|string|unique:master_raks,nomor_rak,' . $id . ',id,lokasi_arsip,' . $rak->lokasi_arsip,
        'keterangan' => 'nullable|string',
    ]);
    $rak->update([
        'nomor_rak' => $request->nomor_rak,
        'keterangan' => $request->keterangan,
    ]);
    return redirect()->back()->with('success', 'Rak berhasil diperbarui.');
}

public function destroyRak($id)
{
    $rak = MasterRak::findOrFail($id);
    // Cek apakah ada box di rak ini
    if ($rak->boxes()->exists()) {
        return redirect()->back()->with('error', 'Rak tidak dapat dihapus karena masih memiliki box. Hapus box terlebih dahulu.');
    }
    $rak->delete();
    return redirect()->back()->with('success', 'Rak berhasil dihapus.');
}

// ===================== UPDATE & DELETE BOX =====================
public function updateBox(Request $request, $id)
{
    $box = MasterBox::findOrFail($id);
    $request->validate([
        'nomor_box' => 'required|string|unique:master_box,nomor_box,' . $id . ',id,rak_id,' . $box->rak_id,
        'keterangan' => 'nullable|string',
    ]);
    $box->update([
        'nomor_box' => $request->nomor_box,
        'keterangan' => $request->keterangan,
    ]);
    return redirect()->back()->with('success', 'Box berhasil diperbarui.');
}

public function destroyBox($id)
{
    $box = MasterBox::findOrFail($id);
    // Cek apakah ada arsip yang menggunakan box ini
    if ($box->arsips()->exists()) {
        return redirect()->back()->with('error', 'Box tidak dapat dihapus karena masih memiliki arsip di dalamnya.');
    }
    $box->delete();
    return redirect()->back()->with('success', 'Box berhasil dihapus.');
}
}