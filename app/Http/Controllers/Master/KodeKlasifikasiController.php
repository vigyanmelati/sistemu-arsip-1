<?php
// app/Http/Controllers/Master/KodeKlasifikasiController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\KodeKlasifikasi;
use Illuminate\Http\Request;

class KodeKlasifikasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }
    
    public function index()
    {
        $kodes = KodeKlasifikasi::orderBy('kode')->paginate(20);
        return view('master.kode-klasifikasi.index', compact('kodes'));
    }
    
    public function create()
    {
        return view('master.kode-klasifikasi.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|unique:kode_klasifikasi|max:50',
            'nama' => 'required|max:255',
            'keterangan' => 'nullable',
        ]);
        
        KodeKlasifikasi::create($request->all());
        
        return redirect()->route('master.kode-klasifikasi.index')
            ->with('success', 'Kode klasifikasi berhasil ditambahkan.');
    }
    
    public function show(KodeKlasifikasi $kodeKlasifikasi)
    {
        return view('master.kode-klasifikasi.show', compact('kodeKlasifikasi'));
    }
    
    public function edit(KodeKlasifikasi $kodeKlasifikasi)
    {
        return view('master.kode-klasifikasi.edit', compact('kodeKlasifikasi'));
    }
    
    public function update(Request $request, KodeKlasifikasi $kodeKlasifikasi)
    {
        $request->validate([
            'kode' => 'required|unique:kode_klasifikasi,kode,' . $kodeKlasifikasi->id,
            'nama' => 'required|max:255',
            'keterangan' => 'nullable',
        ]);
        
        $kodeKlasifikasi->update($request->all());
        
        return redirect()->route('master.kode-klasifikasi.index')
            ->with('success', 'Kode klasifikasi berhasil diperbarui.');
    }
    
    public function destroy(KodeKlasifikasi $kodeKlasifikasi)
    {
        // Cek apakah kode digunakan di arsip
        if ($kodeKlasifikasi->arsips()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus kode klasifikasi karena masih digunakan di arsip.');
        }
        
        $kodeKlasifikasi->delete();
        
        return redirect()->route('master.kode-klasifikasi.index')
            ->with('success', 'Kode klasifikasi berhasil dihapus.');
    }
}