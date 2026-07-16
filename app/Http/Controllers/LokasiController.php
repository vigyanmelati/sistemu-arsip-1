<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;
use App\Models\MasterRak;
use App\Models\MasterBox;

class LokasiController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:admin,super_admin');
    // }

       public function index()
    {
        // Ambil semua key lokasi arsip dari Model
        $ruangans = Arsip::getLokasiArsipKeys();
        
        // Atau jika hanya ingin yang ada data di database:
        // $ruangans = Arsip::select('lokasi_arsip')
        //     ->whereNotNull('lokasi_arsip')
        //     ->where('lokasi_arsip', '!=', '')
        //     ->distinct()
        //     ->pluck('lokasi_arsip')
        //     ->toArray();

        // Label ruangan dari Model
        $ruanganLabels = Arsip::getLokasiArsipLabels();

        // Hitung arsip yang belum punya ruangan
        $arsipTanpaRuangan = Arsip::where(function ($query) {
            $query->whereNull('lokasi_arsip')
                ->orWhere('lokasi_arsip', '');
        })
        ->whereIn('status_pindah', ['LANGSUNG', 'DIPINDAHKAN'])
        ->count();

        return view('manajemen-lokasi.index', compact('ruangans', 'ruanganLabels', 'arsipTanpaRuangan'));
    }

    // Layer 2: Rak dalam ruangan
 public function listRak($ruangan)
{
    if (empty($ruangan)) {
        return redirect()->route('manajemen-lokasi.index')
            ->with('error', 'Ruangan tidak valid.');
    }

    // Ambil objek lengkap (nomor_rak dan keterangan)
    $raks = MasterRak::where('lokasi_arsip', $ruangan)
    ->orderBy('nomor_rak', 'asc')
    ->get(); // ← ambil dua kolom
//  dd($raks);
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

  $rakModel = MasterRak::where('lokasi_arsip', $ruangan)
        ->where('nomor_rak', $rak)
        ->first();

    if (!$rakModel) {
        abort(404, 'Rak tidak ditemukan');
    }

    $boxes = MasterBox::where('rak_id', $rakModel->id)
        ->orderBy('nomor_box', 'asc')
        ->get();

    $rakId = $rakModel->id; // ← tambahkan ini

    $ruanganLabel = $this->getLabelRuangan($ruangan);

    return view('manajemen-lokasi.box', compact('ruangan', 'ruanganLabel', 'rak', 'boxes', 'rakModel', 'rakId'));
}
    // Layer 4: Daftar arsip dalam box
    public function listArsip($ruangan, $rak, $box)
{
    if (empty($ruangan) || empty($rak) || empty($box)) {
        return redirect()->route('manajemen-lokasi.index')
            ->with('error', 'Parameter tidak valid.');
    }

    // Cari rak berdasarkan ruangan dan nomor rak
    $rakModel = MasterRak::where('lokasi_arsip', $ruangan)
        ->where('nomor_rak', $rak)
        ->first();

    if (!$rakModel) {
        abort(404, 'Rak tidak ditemukan');
    }

    // Cari box berdasarkan rak_id dan nomor box
    $boxModel = MasterBox::where('rak_id', $rakModel->id)
        ->where('nomor_box', $box)
        ->first();

    if (!$boxModel) {
        abort(404, 'Box tidak ditemukan');
    }

    // Ambil arsip berdasarkan box_id
    $arsips = Arsip::where('box_id', $boxModel->id)
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
        'kapasitas' => $request->kapasitas,
        'keterangan' => $request->keterangan,
    ]);

    return redirect()->back()->with('success', 'Box berhasil ditambahkan.');
}
// Edit form (bisa pakai modal atau halaman terpisah, di sini kita pakai modal, tapi kita akan buat modal di view)
public function editRak($id)
{
    $rak = MasterRak::findOrFail($id);
    // Kembalikan data JSON untuk modal edit
    return response()->json($rak);
}

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

    if ($rak->boxes()->exists()) {
        return redirect()->back()->with(
            'error',
            'Rak tidak dapat dihapus karena masih memiliki box di dalamnya. Silakan hapus seluruh box terlebih dahulu.'
        );
    }

    $rak->delete();

    return redirect()->back()->with(
        'success',
        'Rak berhasil dihapus.'
    );
}

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