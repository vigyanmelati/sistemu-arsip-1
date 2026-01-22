<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Http\Request;

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
        
        // Filter berdasarkan sub bagian (ubah menjadi sub_bagian_id)
        if ($request->has('sub_bagian_id') && $request->sub_bagian_id != '') {
            $query->where('sub_bagian_id', $request->sub_bagian_id);
        }
        
        // Filter berdasarkan kode klasifikasi
        if ($request->has('kode_klasifikasi_id') && $request->kode_klasifikasi_id != '') {
            $query->where('kode_klasifikasi_id', $request->kode_klasifikasi_id);
        }
        
        // Search - Perbaikan: Search melalui relasi
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->whereHas('kodeKlasifikasi', function($subQuery) use ($request) {
                    $subQuery->where('kode', 'like', "%{$request->search}%")
                            ->orWhere('nama', 'like', "%{$request->search}%");
                })
                ->orWhere('uraian_arsip', 'like', "%{$request->search}%")
                ->orWhereHas('subBagian', function($subQuery) use ($request) {
                    $subQuery->where('nama', 'like', "%{$request->search}%");
                });
            });
        }
        
        $arsips = $query->orderBy('id', 'desc')->paginate(15);
        
        // Data untuk filter
        $tahunOptions = Arsip::select('tahun_arsip')
            ->distinct()
            ->orderBy('tahun_arsip', 'desc')
            ->pluck('tahun_arsip');
            
        // Ambil dari tabel sub_bagians
        $subBagianOptions = SubBagian::select('id', 'nama_sub_bagian as nama_sub_bagian')
            ->orderBy('nama_sub_bagian')
            ->get();
        
        // Ambil dari tabel kode_klasifikasi
        $kodeKlasifikasiOptions = KodeKlasifikasi::select('id', 'kode', 'uraian')
            ->orderBy('kode')
            ->get();
        
        $statusOptions = [
            'AKTIF' => 'Aktif',
            'INAKTIF' => 'Inaktif', 
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        return view('arsip.index', compact('arsips', 'tahunOptions', 'subBagianOptions', 'kodeKlasifikasiOptions', 'statusOptions'));
    }

    public function create()
    {
        // Ambil data untuk dropdown
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama_sub_bagian')->get();
        
        $statusOptions = [
            'AKTIF' => 'Aktif',
            'INAKTIF' => 'Inaktif', 
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        return view('arsip.create', compact('statusOptions', 'kodeKlasifikasiOptions', 'subBagianOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_klasifikasi_id' => 'required|exists:kode_klasifikasi,id',
            'uraian_arsip' => 'required',
            'sub_bagian_id' => 'required|exists:sub_bagians,id',
            'tahun_arsip' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'nomor_rak' => 'required',
            'nomor_box' => 'required',
            'aktif_tahun' => 'required|integer',
            'inaktif_tahun' => 'required|integer|gt:aktif_tahun',
            'status_arsip' => 'required|in:AKTIF,INAKTIF,MUSNAH,PERMANEN',
            // Tambahkan validasi untuk kolom lain sesuai kebutuhan
        ]);
        
        // Tambahkan kolom created_by jika ada user login
        if (auth()->check()) {
            $validated['created_by'] = auth()->id();
        }
        
        Arsip::create($validated);
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil ditambahkan.');
    }

    public function show(Arsip $arsip)
    {
        // Load relasi untuk ditampilkan
        $arsip->load(['kodeKlasifikasi', 'subBagian']);
        return view('arsip.show', compact('arsip'));
    }

    public function edit(Arsip $arsip)
    {
        // Ambil data untuk dropdown
        $kodeKlasifikasiOptions = KodeKlasifikasi::orderBy('kode')->get();
        $subBagianOptions = SubBagian::orderBy('nama')->get();
        
        $statusOptions = [
            'AKTIF' => 'Aktif',
            'INAKTIF' => 'Inaktif', 
            'MUSNAH' => 'Musnah',
            'PERMANEN' => 'Permanen'
        ];
        
        return view('arsip.edit', compact('arsip', 'statusOptions', 'kodeKlasifikasiOptions', 'subBagianOptions'));
    }

    public function update(Request $request, Arsip $arsip)
    {
        $validated = $request->validate([
            'kode_klasifikasi_id' => 'required|exists:kode_klasifikasi,id',
            'uraian_arsip' => 'required',
            'sub_bagian_id' => 'required|exists:sub_bagians,id',
            'tahun_arsip' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'nomor_rak' => 'required',
            'nomor_box' => 'required',
            'aktif_tahun' => 'required|integer',
            'inaktif_tahun' => 'required|integer|gt:aktif_tahun',
            'status_arsip' => 'required|in:AKTIF,INAKTIF,MUSNAH,PERMANEN',
        ]);
        
        $arsip->update($validated);
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil diperbarui.');
    }

    public function destroy(Arsip $arsip)
    {
        $arsip->delete();
        
        return redirect()->route('arsip.index')
            ->with('success', 'Arsip berhasil dihapus.');
    }
}