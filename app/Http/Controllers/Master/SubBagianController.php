<?php
// app/Http/Controllers/Master/SubBagianController.php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\SubBagian;
use Illuminate\Http\Request;

class SubBagianController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
    }
    
    public function index()
    {
        $subBagians = SubBagian::orderBy('nama')->paginate(20);
        return view('master.sub-bagian.index', compact('subBagians'));
    }
    
    public function create()
    {
        return view('master.sub-bagian.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'nullable|max:20',
            'nama' => 'required|max:255',
            'keterangan' => 'nullable',
        ]);
        
        SubBagian::create($request->all());
        
        return redirect()->route('master.sub-bagian.index')
            ->with('success', 'Sub bagian berhasil ditambahkan.');
    }
    
    public function show(SubBagian $subBagian)
    {
        return view('master.sub-bagian.show', compact('subBagian'));
    }
    
    public function edit(SubBagian $subBagian)
    {
        return view('master.sub-bagian.edit', compact('subBagian'));
    }
    
    public function update(Request $request, SubBagian $subBagian)
    {
        $request->validate([
            'kode' => 'nullable|max:20',
            'nama' => 'required|max:255',
            'keterangan' => 'nullable',
        ]);
        
        $subBagian->update($request->all());
        
        return redirect()->route('master.sub-bagian.index')
            ->with('success', 'Sub bagian berhasil diperbarui.');
    }
    
    public function destroy(SubBagian $subBagian)
    {
        // Cek apakah sub bagian digunakan di arsip
        if ($subBagian->arsips()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus sub bagian karena masih digunakan di arsip.');
        }
        
        $subBagian->delete();
        
        return redirect()->route('master.sub-bagian.index')
            ->with('success', 'Sub bagian berhasil dihapus.');
    }
}