<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ArsipImport;
use Carbon\Carbon;
use App\Exports\ArsipExport;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query dengan eager loading
        // $query = Arsip::with(['kodeKlasifikasi', 'subBagian']);
      $query = Arsip::with(['kodeKlasifikasi', 'subBagian'])
        ->whereIn('status_pindah', [
            'DIPINDAHKAN',
            'LANGSUNG'
        ]);
        
        // Filter berdasarkan status arsip
        if ($request->has('status_arsip') && $request->status_arsip != '') {
            $query->where('status_arsip', $request->status_arsip);
        }
        
        // Filter berdasarkan tahun
        if ($request->has('tahun_arsip') && $request->tahun_arsip != '') {
            $query->where('tahun_arsip', $request->tahun_arsip);
        }
        
        // Filter berdasarkan sub bagian
        if ($request->has('sub_bagian_id') && $request->sub_bagian_id != '') {
            $query->where('sub_bagian_id', $request->sub_bagian_id);
        }
        
        // Filter berdasarkan kode klasifikasi
        if ($request->has('kode_klasifikasi_id') && $request->kode_klasifikasi_id != '') {
            $query->where('kode_klasifikasi_id', $request->kode_klasifikasi_id);
        }

        // Filter berdasarkan kondisi fisik
        if ($request->has('keterangan') && $request->keterangan != '') {
            $query->where('keterangan', $request->keterangan);
        }

        // Filter berdasarkan keterangan JRA
        if ($request->has('keterangan_jra') && $request->keterangan_jra != '') {
            $query->where('keterangan_jra', $request->keterangan_jra);
        }

        $kondisiOptions = [
            'BAIK' => 'Baik',
            'RUSAK' => 'Rusak',
            'HILANG' => 'Hilang'
        ];
            
        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->whereHas('kodeKlasifikasi', function($subQuery) use ($request) {
                    $subQuery->where('kode', 'like', "%{$request->search}%")
                            ->orWhere('uraian', 'like', "%{$request->search}%");
                })
                ->orWhere('uraian_arsip', 'like', "%{$request->search}%")
                ->orWhereHas('subBagian', function($subQuery) use ($request) {
                    $subQuery->where('nama_sub_bagian', 'like', "%{$request->search}%");
                });
            });
        }
        
        // Sorting
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
        
        if ($sort === 'kode_klasifikasi') {
            $query->join('kode_klasifikasis', 'arsips.kode_klasifikasi_id', '=', 'kode_klasifikasis.id')
                  ->orderBy('kode_klasifikasis.kode', $direction)
                  ->select('arsips.*');
        } else {
            $query->orderBy($sort, $direction);
        }
        
        $arsips = $query->paginate(15);
        
        // Data untuk filter
        $tahunOptions = Arsip::select('tahun_arsip')
            ->distinct()
            ->orderBy('tahun_arsip', 'desc')
            ->pluck('tahun_arsip');
            
        $subBagianOptions = SubBagian::select('id', 'nama_sub_bagian as nama_sub_bagian')
            ->orderBy('nama_sub_bagian')
            ->get();
        
        $kodeKlasifikasiOptions = KodeKlasifikasi::select('id', 'kode', 'uraian')
            ->orderBy('kode')
            ->get();
        
        $statusOptions = [
            'AKTIF' => 'Aktif',
            'INAKTIF' => 'Inaktif', 
            'USUL_MUSNAH' => 'Usul Musnah',
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        $keteranganJraOptions = [
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        return view('arsip.index', compact(
            'arsips', 
            'tahunOptions', 
            'subBagianOptions', 
            'kodeKlasifikasiOptions', 
            'statusOptions',
            'kondisiOptions',
            'keteranganJraOptions'
        ));
    }

    public function create()
    {
        // Ambil data untuk dropdown
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        
        // Default values untuk form
        $defaultValues = [
            'status_arsip' => 'AKTIF',
            'jumlah_berkas' => 1,
            'satuan_arsip' => 'LEMBAR',
            'tahun_arsip' => date('Y'),
            'tanggal_arsip' => date('Y-m-d'),
            'keterangan_jra' => 'MUSNAH',
        ];
        
        return view('arsip.create', compact(
            'kodeKlasifikasiOptions', 
            'subBagianOptions',
            'defaultValues'
        ));
    }

   public function store(Request $request)
{
    // Debug data yang dikirim
    \Log::info('Data yang dikirim:', $request->all());
    
    // Validate data berdasarkan view
    $validated = $request->validate([
        // WAJIB - Data Dasar
        'kode_klasifikasi_id' => 'required|exists:kode_klasifikasis,id',
        'uraian_arsip' => 'required|string|max:500',
        'sub_bagian_id' => 'required|exists:sub_bagians,id',
        'tahun_arsip' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        'tanggal_arsip' => 'required|date',
        'jumlah_berkas' => 'required|integer|min:1',
        'satuan_arsip' => 'required|in:BENDEL,LEMBAR',
        
        // Masa Retensi - BENTUK TEKS LENGKAP
        'aktif_tahun' => 'required|string|max:100',
        'inaktif_tahun' => 'required|string|max:100',
        'tanggal_referensi' => 'nullable|date',
        'keterangan_jra' => 'required|in:PERMANEN,MUSNAH',
        
        // HASIL PERHITUNGAN (opsional dari JS, akan dihitung ulang)
        'aktif_sampai' => 'nullable|date',
        'inaktif_sampai' => 'nullable|date',
        'status_arsip' => 'nullable|in:AKTIF,INAKTIF,MUSNAH,PERMANEN',
        
        // OPTIONAL
        'nomor_rak' => 'nullable|string|max:50',
        'nomor_box' => 'nullable|string|max:50',
        'nomor_sampul' => 'nullable|string|max:100',
        'tingkat_perkembangan' => 'nullable|in:ASLI,COPY,SALINAN',
        'keterangan' => 'nullable|in:BAIK,RUSAK,HILANG',
        'media_arsip' => 'nullable|string|max:255',
        'file_dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);
    
    \Log::info('Data yang lolos validasi:', $validated);
    
    try {
        // Konversi tipe data
        $validated['tahun_arsip'] = (string) $validated['tahun_arsip'];
        
        // Tambahkan created_by jika ada user login
        if (auth()->check()) {
            $validated['created_by'] = auth()->id();
        }
        
        // Tambahkan tanggal_masuk (otomatis hari ini)
        $validated['tanggal_masuk'] = now()->format('Y-m-d');
        
        // Set default untuk kolom yang mungkin null
        $defaults = [
            'nomor_rak' => '',
            'nomor_box' => '',
            'nomor_sampul' => '',
            'keterangan' => 'BAIK',
            'tingkat_perkembangan' => 'ASLI',
            'status_pindah' => 'LANGSUNG'
        ];
        
        foreach ($defaults as $field => $defaultValue) {
            if (!isset($validated[$field]) || $validated[$field] === '') {
                $validated[$field] = $defaultValue;
            }
        }
        
        // Handle file upload
        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('arsip', $fileName, 'public');
            $validated['file_dokumen'] = $fileName;
        }
        
        // ============================================
        // PERHITUNGAN RETENSI OTOMATIS (WAJIB DILAKUKAN)
        // ============================================
        // Selalu hitung ulang dari data input untuk memastikan konsistensi
        $perhitungan = $this->hitungRetensi(
            $validated['aktif_tahun'],
            $validated['inaktif_tahun'],
            $validated['keterangan_jra'],
            $validated['tanggal_arsip'],
            $validated['tanggal_referensi'] ?? null
        );
        
        // Timpa nilai dari JS dengan hasil perhitungan di server
        $validated['aktif_sampai'] = $perhitungan['aktif_sampai'];
        $validated['inaktif_sampai'] = $perhitungan['inaktif_sampai'];
        $validated['status_arsip'] = $perhitungan['status_arsip'];
        
        \Log::info('Hasil perhitungan retensi:', $perhitungan);
        \Log::info('Data sebelum disimpan:', $validated);
        
        // Simpan data
        $arsip = Arsip::create($validated);
        
        \Log::info('Arsip berhasil dibuat dengan ID: ' . $arsip->id);
        \Log::info('Status arsip: ' . $arsip->status_arsip);
        \Log::info('Aktif sampai: ' . $arsip->aktif_sampai);
        \Log::info('Inaktif sampai: ' . $arsip->inaktif_sampai);
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil ditambahkan.');
            
    } catch (\Exception $e) {
        \Log::error('Gagal menyimpan arsip: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return back()->withInput()
            ->with('error', 'Gagal menyimpan arsip: ' . $e->getMessage());
    }
}

    /**
     * Fungsi untuk menghitung retensi berdasarkan input dari view
     */
  public function hitungRetensi($aktif_tahun, $inaktif_tahun, $keterangan_jra, $tanggal_arsip, $tanggal_referensi = null)
{
    $result = [
        'aktif_sampai' => null,
        'inaktif_sampai' => null,
        'status_arsip' => 'AKTIF'
    ];
    
    // Cek apakah mengandung kata SETELAH
    $aktifMengandungSetelah = stripos($aktif_tahun, 'SETELAH') !== false;
    $inaktifMengandungSetelah = stripos($inaktif_tahun, 'SETELAH') !== false;
    
    // Jika mengandung SETELAH tapi tanggal referensi kosong, kembalikan status AKTIF saja
    if (($aktifMengandungSetelah || $inaktifMengandungSetelah) && empty($tanggal_referensi)) {
        $result['status_arsip'] = 'AKTIF';
        return $result;
    }
    
    // Ekstrak angka dari teks
    $aktifTahun = $this->extractNumberFromText($aktif_tahun);
    $inaktifTahun = $this->extractNumberFromText($inaktif_tahun);
    
    if (!$aktifTahun || !$inaktifTahun) {
        $result['status_arsip'] = 'AKTIF';
        return $result;
    }
    
    // Tentukan tanggal dasar perhitungan
    if ($aktifMengandungSetelah || $inaktifMengandungSetelah) {
        $tanggalDasar = Carbon::parse($tanggal_referensi);
    } else {
        $tanggalDasar = Carbon::parse($tanggal_arsip);
    }
    
    // Hitung tanggal aktif sampai
    $aktifSampai = $tanggalDasar->copy()->addYears($aktifTahun);
    
    // Hitung tanggal inaktif sampai (ditambahkan setelah aktif)
    $inaktifSampai = $aktifSampai->copy()->addYears($inaktifTahun);
    
    // Hitung tanggal musnah (untuk keterangan MUSNAH)
    $musnahSampai = $inaktifSampai->copy()->addYears(1);
    
    // Tentukan status arsip berdasarkan tanggal hari ini
    $sekarang = Carbon::now();
    
    if ($keterangan_jra === 'PERMANEN') {
        $result['status_arsip'] = 'PERMANEN';
    } elseif ($keterangan_jra === 'MUSNAH') {
        if ($sekarang <= $aktifSampai) {
            $result['status_arsip'] = 'AKTIF';
        } elseif ($sekarang <= $inaktifSampai) {
            $result['status_arsip'] = 'INAKTIF';
        } elseif ($sekarang <= $musnahSampai) {
            $result['status_arsip'] = 'USUL_MUSNAH';
        } else {
            $result['status_arsip'] = 'USUL_MUSNAH';
        }
    } else {
        if ($sekarang <= $aktifSampai) {
            $result['status_arsip'] = 'AKTIF';
        } elseif ($sekarang <= $inaktifSampai) {
            $result['status_arsip'] = 'INAKTIF';
        } else {
            $result['status_arsip'] = 'INAKTIF';
        }
    }
    
    // Set tanggal hasil perhitungan
    $result['aktif_sampai'] = $aktifSampai->format('Y-m-d');
    $result['inaktif_sampai'] = $inaktifSampai->format('Y-m-d');
    
    return $result;
}

/**
 * Ekstrak angka dari teks (contoh: "2 TAHUN" atau "2 TAHUN SETELAH KEGIATAN")
 */
private function extractNumberFromText($text)
{
    if (preg_match('/\d+/', $text, $matches)) {
        return (int) $matches[0];
    }
    return null;
}

    // public function show(Arsip $arsip)
    // {
    //     $arsip->load(['kodeKlasifikasi', 'subBagian']);
    //     return view('arsip.show', compact('arsip'));
    // }

    // public function show(Request $request, Arsip $arsip)
    // {
    //     return view('arsip.show', [
    //         'arsip' => $arsip,
    //         'returnUrl' => $request->get('return')
    //     ]);
    // }

    public function show(Request $request, Arsip $arsip)
    {
        // Load data riwayat perpindahan
        $riwayatPindah = \App\Models\HistoryPindah::with('user')
            ->where('arsip_id', $arsip->id)
            ->orderBy('tanggal_pindah', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil data berita acara yang terkait dengan arsip ini
        $beritaAcaraDetail = \App\Models\BeritaAcaraDetail::with(['beritaAcara', 'beritaAcara.subBagian'])
            ->where('arsip_id', $arsip->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('arsip.show', [
            'arsip' => $arsip,
            'returnUrl' => $request->get('return'),
            'riwayatPindah' => $riwayatPindah,
            'beritaAcaraDetail' => $beritaAcaraDetail
        ]);
    }

    public function edit(Arsip $arsip)
    {
        // Ambil data untuk dropdown
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        
        return view('arsip.edit', compact(
            'arsip', 
            'kodeKlasifikasiOptions', 
            'subBagianOptions'
        ));
    }

 public function update(Request $request, Arsip $arsip)
{
    // Debug data masuk
    \Log::info('Data update yang dikirim:', $request->all());

    // =========================
    // VALIDASI
    // =========================
    $validated = $request->validate([
        // WAJIB - Data Dasar
        'kode_klasifikasi_id' => 'required|exists:kode_klasifikasis,id',
        'uraian_arsip'        => 'required|string|max:500',
        'sub_bagian_id'       => 'required|exists:sub_bagians,id',
        'tahun_arsip'         => 'required|integer|min:2000|max:' . (date('Y') + 1),
        'tanggal_arsip'       => 'required|date',
        'jumlah_berkas'       => 'required|integer|min:1',
        'satuan_arsip'        => 'required|in:BENDEL,LEMBAR',

        // Masa Retensi
        'aktif_tahun'         => 'required|string|max:100',
        'inaktif_tahun'       => 'required|string|max:100',
        'tanggal_referensi'   => 'nullable|date',
        'keterangan_jra'      => 'required|in:PERMANEN,MUSNAH',

        // Hasil hitung (akan dihitung ulang)
        'aktif_sampai'        => 'nullable|date',
        'inaktif_sampai'      => 'nullable|date',
        'status_arsip'        => 'nullable|in:AKTIF,INAKTIF,USUL_MUSNAH,MUSNAH,PERMANEN',

        // Optional
        'nomor_rak'           => 'nullable|string|max:50',
        'nomor_box'           => 'nullable|string|max:50',
        'nomor_sampul'        => 'nullable|string|max:100',
        'tingkat_perkembangan'=> 'nullable|in:ASLI,COPY,SALINAN',
        'keterangan'          => 'nullable|in:BAIK,RUSAK,HILANG',
        'media_arsip'         => 'nullable|string|max:255',

        // File
        'file_dokumen'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        'hapus_file'          => 'nullable|in:0,1',
    ]);

    try {

        // =========================
        // NORMALISASI DATA
        // =========================
        $validated['tahun_arsip'] = (string) $validated['tahun_arsip'];

        // Default value jika kosong
        $defaults = [
            'nomor_rak' => '',
            'nomor_box' => '',
            'nomor_sampul' => '',
            'keterangan' => 'BAIK',
            'tingkat_perkembangan' => 'ASLI',
        ];

        foreach ($defaults as $field => $default) {
            if (!isset($validated[$field]) || $validated[$field] === '') {
                $validated[$field] = $default;
            }
        }

        // =========================
        // HAPUS FILE LAMA (JIKA DIMINTA)
        // =========================
        if (($request->hapus_file ?? '0') == '1' && $arsip->file_dokumen) {
            Storage::disk('public')->delete('arsip/' . $arsip->file_dokumen);
            $validated['file_dokumen'] = null;
        }

        // =========================
        // UPLOAD FILE BARU
        // =========================
        if ($request->hasFile('file_dokumen')) {
            // hapus file lama
            if ($arsip->file_dokumen) {
                Storage::disk('public')->delete('arsip/' . $arsip->file_dokumen);
            }

            $file = $request->file('file_dokumen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('arsip', $fileName, 'public');

            $validated['file_dokumen'] = $fileName;
        }

        // =========================
        // HITUNG ULANG RETENSI (SERVER SIDE)
        // =========================
        $perhitungan = $this->hitungRetensi(
            $validated['aktif_tahun'],
            $validated['inaktif_tahun'],
            $validated['keterangan_jra'],
            $validated['tanggal_arsip'],
            $validated['tanggal_referensi'] ?? null
        );

        $validated['aktif_sampai']  = $perhitungan['aktif_sampai'];
        $validated['inaktif_sampai']= $perhitungan['inaktif_sampai'];
        $validated['status_arsip'] = $perhitungan['status_arsip'];

        unset($validated['status_pindah']);


        \Log::info('Hasil hitung retensi update:', $perhitungan);

        // =========================
        // UPDATE DATA
        // =========================
        $arsip->update($validated);

        return redirect()
            ->route('arsip.show', $arsip->id)
            ->with('success', 'Arsip berhasil diperbarui.');

    } catch (\Exception $e) {

        \Log::error('Gagal update arsip: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());

        return back()
            ->withInput()
            ->with('error', 'Gagal memperbarui arsip: ' . $e->getMessage());
    }
}


    public function destroy(Arsip $arsip)
    {
        // Hapus file jika ada
        if ($arsip->file_dokumen && Storage::disk('public')->exists('arsip/' . $arsip->file_dokumen)) {
            Storage::disk('public')->delete('arsip/' . $arsip->file_dokumen);
        }
        
        $arsip->delete();
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil dihapus.');
    }
    
    /**
     * Fungsi untuk memperbarui status semua arsip
     * Bisa dijadikan scheduled task atau dijalankan manual
     */
    public function updateStatusAll()
    {
        $arsips = Arsip::all();
        $updated = 0;

        foreach ($arsips as $arsip) {
            // Hitung ulang status berdasarkan data yang ada
            $perhitungan = $this->hitungRetensi(
                $arsip->aktif_tahun,
                $arsip->inaktif_tahun,
                $arsip->keterangan_jra,
                $arsip->tanggal_arsip,
                $arsip->tanggal_referensi
            );
            
            // Update status saja (tanpa mengubah tanggal)
            $arsip->status_arsip = $perhitungan['status_arsip'];
            $arsip->save();
            
            $updated++;
        }

        return back()->with('success', "Status {$updated} arsip berhasil diperbarui.");
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new ArsipImport, $request->file('file_excel'));

            return redirect()->route('arsip.index')
                ->with('success', 'Data arsip berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

   public function export(Request $request)
    {
        $columns = $request->input('columns', []);
        
        if (count($columns) === 0) {
            return back()->with('error', 'Pilih minimal satu kolom untuk export.');
        }
        
        // Debug: cek apa yang diterima
        \Log::info('Export request:', $request->all());
        \Log::info('Columns selected:', $columns);
        
        return Excel::download(
            new ArsipExport($request, $columns),
            'data-arsip-' . date('Y-m-d-H-i-s') . '.xlsx'
        );
    }




}