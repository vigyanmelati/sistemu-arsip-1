<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArsipController extends Controller
{
    public function index(Request $request)
    {
        // Mulai query dengan eager loading
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian']);
        
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
        
        $statusOptions = [
            'AKTIF' => 'Aktif',
            'INAKTIF' => 'Inaktif', 
            'USUL_MUSNAH' => 'Usul Musnah',
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        // Default values untuk form
        $defaultValues = [
            'is_isi_keterangan' => 0,
            'status_arsip' => 'AKTIF',
            'jumlah_berkas' => 1,
            'satuan_arsip' => 'LEMBAR',
            'tahun_arsip' => date('Y'),
            'tanggal_arsip' => date('Y-m-d'),
        ];
        
        return view('arsip.create', compact(
            'statusOptions', 
            'kodeKlasifikasiOptions', 
            'subBagianOptions',
            'defaultValues'
        ));
    }

public function store(Request $request)
{
    // Debug data yang dikirim
    \Log::info('Data yang dikirim:', $request->all());
    
    // Validate data
    $validated = $request->validate([
        // WAJIB
        'kode_klasifikasi_id' => 'required|exists:kode_klasifikasis,id',
        'uraian_arsip' => 'required|string|max:500',
'sub_bagian_id' => 'required|exists:sub_bagians,id',
        'tahun_arsip' => 'required|integer|min:2000|max:' . (date('Y') + 1),
        'tanggal_arsip' => 'required|date',
        'jumlah_berkas' => 'required|integer|min:1',
        'satuan_arsip' => 'required|in:BENDEL,LEMBAR',
        
        // MODE PENGISIAN
        'is_isi_keterangan' => 'required|boolean',
        
        // MODE 1: Validasi jika tidak isi keterangan
        'aktif_tahun' => 'nullable|integer|min:0',
        'inaktif_tahun' => 'nullable|integer|min:0',
        
        // MODE 2: Validasi jika isi keterangan
        'aktif_keterangan' => 'nullable|string|max:255',
        'inaktif_keterangan' => 'nullable|string|max:255',
        'tanggal_referensi' => 'nullable|date',
        
        // KETERANGAN JRA (untuk kedua mode)
        'keterangan_jra' => 'nullable|in:MUSNAH,PERMANEN',
        
        // HASIL PERHITUNGAN
        'aktif_sampai' => 'nullable|date',
        'inaktif_sampai' => 'nullable|date',
        
        // OPTIONAL LAINNYA
        'nomor_rak' => 'nullable|string|max:50',
        'nomor_box' => 'nullable|string|max:50',
        'nomor_sampul' => 'nullable|string|max:100',
        'tingkat_perkembangan' => 'nullable|in:ASLI,COPY,SALINAN',
        'keterangan' => 'nullable|in:BAIK,RUSAK,HILANG',
        'file_dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);
    
    \Log::info('Data yang lolos validasi:', $validated);
    
    try {
        // Pastikan tahun_arsip dalam format string
        if (isset($validated['tahun_arsip'])) {
            $validated['tahun_arsip'] = (string) $validated['tahun_arsip'];
        }
        
        // Tambahkan created_by jika ada user login
        if (auth()->check()) {
            $validated['created_by'] = auth()->id();
        }
        
        // Tambahkan tanggal_masuk (otomatis hari ini)
        $validated['tanggal_masuk'] = now()->format('Y-m-d');
        
        // Set default untuk kolom yang mungkin null tapi required di DB
        $defaults = [
            'is_isi_keterangan' => 0,
            'aktif_tahun' => 0,
            'inaktif_tahun' => 0,
            'nomor_rak' => '',
            'nomor_box' => '',
            'nomor_sampul' => '',
            'keterangan_jra' => 'MUSNAH',
            'keterangan' => 'BAIK',
            'tingkat_perkembangan' => 'ASLI',
        ];
        
        foreach ($defaults as $field => $defaultValue) {
            if (!isset($validated[$field]) || $validated[$field] === '') {
                $validated[$field] = $defaultValue;
            }
        }
        
        // Konversi is_isi_keterangan ke integer
        $validated['is_isi_keterangan'] = (int) $validated['is_isi_keterangan'];
        
        // Konversi nilai numerik
        $validated['aktif_tahun'] = (int) $validated['aktif_tahun'];
        $validated['inaktif_tahun'] = (int) $validated['inaktif_tahun'];
        
        // Handle file upload
        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('arsip', $fileName, 'public');
            $validated['file_dokumen'] = $fileName;
        }
        
        // Hitung aktif_sampai dan inaktif_sampai jika belum ada
        if (empty($validated['aktif_sampai'])) {
            // Mode 1: Hitung dari tahun
            if ($validated['is_isi_keterangan'] == 0 && $validated['aktif_tahun'] > 0) {
                $tanggalArsip = \Carbon\Carbon::parse($validated['tanggal_arsip']);
                $validated['aktif_sampai'] = $tanggalArsip->addYears($validated['aktif_tahun'])->format('Y-m-d');
                
                if ($validated['inaktif_tahun'] > 0) {
                    $validated['inaktif_sampai'] = $tanggalArsip->addYears($validated['inaktif_tahun'])->format('Y-m-d');
                } else {
                    // Jika inaktif tahun 0, set inaktif_sampai sama dengan aktif_sampai
                    $validated['inaktif_sampai'] = $validated['aktif_sampai'];
                }
            }
            // Mode 2: Hitung dari tanggal referensi
            elseif ($validated['is_isi_keterangan'] == 1 && !empty($validated['tanggal_referensi'])) {
                // Ekstrak angka dari keterangan
                $aktifTahun = $this->extractYearFromDescription($validated['aktif_keterangan'] ?? '');
                $inaktifTahun = $this->extractYearFromDescription($validated['inaktif_keterangan'] ?? '');
                
                if ($aktifTahun > 0) {
                    $tanggalRef = \Carbon\Carbon::parse($validated['tanggal_referensi']);
                    $validated['aktif_sampai'] = $tanggalRef->addYears($aktifTahun)->format('Y-m-d');
                    
                    if ($inaktifTahun > 0) {
                        $validated['inaktif_sampai'] = $tanggalRef->addYears($inaktifTahun)->format('Y-m-d');
                    } else {
                        $validated['inaktif_sampai'] = $validated['aktif_sampai'];
                    }
                }
            }
        }
        
        // Jika aktif_sampai atau inaktif_sampai masih kosong, set default
        if (empty($validated['aktif_sampai'])) {
            $validated['aktif_sampai'] = now()->format('Y-m-d');
        }
        if (empty($validated['inaktif_sampai'])) {
            $validated['inaktif_sampai'] = $validated['aktif_sampai'];
        }
        
        \Log::info('Data sebelum disimpan:', $validated);
        
        // Simpan data
        $arsip = Arsip::create($validated);
        
        \Log::info('Arsip berhasil dibuat dengan ID: ' . $arsip->id);
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil ditambahkan.');
            
    } catch (\Exception $e) {
        \Log::error('Gagal menyimpan arsip: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return back()->withInput()
            ->with('error', 'Gagal menyimpan arsip: ' . $e->getMessage());
    }
}

    // Fungsi bantuan untuk ekstrak tahun dari keterangan
    private function extractYearFromDescription($description)
    {
        if (empty($description)) return null;
        
        // Cari angka di awal string
        if (preg_match('/^(\d+)/', $description, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
}

    public function show(Arsip $arsip)
    {
        // Load relasi untuk ditampilkan
        $arsip->load(['kodeKlasifikasi', 'subBagian']);
        
        // Hitung status saat ini untuk ditampilkan (jika perlu)
        $arsip->hitungStatusArsip();
        
        return view('arsip.show', compact('arsip'));
    }

    public function edit(Arsip $arsip)
    {
        // Ambil data untuk dropdown
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        
        $statusOptions = [
            'AKTIF' => 'Aktif',
            'INAKTIF' => 'Inaktif', 
            'USUL_MUSNAH' => 'Usul Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        $keteranganJraOptions = [
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        return view('arsip.edit', compact(
            'arsip', 
            'statusOptions', 
            'kodeKlasifikasiOptions', 
            'subBagianOptions',
            'keteranganJraOptions'
        ));
    }

    public function update(Request $request, Arsip $arsip)
    {
        $validated = $request->validate([
            'kode_klasifikasi_id' => 'required|exists:kode_klasifikasi,id',
            'uraian_arsip' => 'required',
            'sub_bagian_id' => 'required|exists:sub_bagians,id',
            'tahun_arsip' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'tanggal_arsip' => 'required|date',
            'nomor_rak' => 'required',
            'nomor_box' => 'required',
            'aktif_tahun' => 'required|integer|min:1',
            'inaktif_tahun' => 'required|integer|gt:aktif_tahun',
            'keterangan_jra' => 'nullable|in:MUSNAH,PERMANEN',
            'jumlah_berkas' => 'nullable|integer|min:1',
            'satuan_arsip' => 'nullable|in:BENDEL,LEMBAR',
            'tingkat_perkembangan' => 'nullable|in:ASLI,COPY,SALINAN',
            'keterangan' => 'required|in:BAIK,RUSAK,HILANG',
            'no_sampul' => 'nullable',
            'file_dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Handle file upload
        if ($request->hasFile('file_dokumen')) {
            // Hapus file lama jika ada
            if ($arsip->file_dokumen && Storage::disk('public')->exists('arsip/' . $arsip->file_dokumen)) {
                Storage::disk('public')->delete('arsip/' . $arsip->file_dokumen);
            }
            
            $file = $request->file('file_dokumen');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('arsip', $fileName, 'public');
            $validated['file_dokumen'] = $fileName;
        }
        
        // Status arsip akan dihitung otomatis di model
        // Tidak perlu diupdate manual
        
        $arsip->update($validated);
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil diperbarui.');
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
            $arsip->hitungStatusArsip();
            if ($arsip->isDirty('status_arsip')) {
                $arsip->save();
                $updated++;
            }
        }
        
        return back()->with('success', "Status {$updated} arsip berhasil diperbarui.");
    }
}