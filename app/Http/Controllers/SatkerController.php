<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Satker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SatkerController extends Controller
{
    /**
     * Tampilkan daftar satker.
     */
    public function index()
    {
        $satkers = Satker::orderBy('nama_satker')->paginate(15);

        return view('satkers.index', compact('satkers'));
    }

    /**
     * Form tambah satker.
     */
    public function create()
    {
        return view('satkers.create');
    }

    /**
     * Simpan satker baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_satker' => 'required|string|max:255',
            'kode_satker' => 'nullable|string|max:50|unique:satkers,kode_satker',
            'alamat'      => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        Satker::create($validated);

        return redirect()
            ->route('superadmin.satkers.index')
            ->with('success', 'Satker berhasil ditambahkan.');
    }

    /**
     * Form edit satker.
     */
    public function edit(Satker $satker)
    {
        return view('satkers.edit', compact('satker'));
    }

    /**
     * Update satker.
     */
    public function update(Request $request, Satker $satker): RedirectResponse
    {
        $validated = $request->validate([
            'nama_satker' => 'required|string|max:255',
            'kode_satker' => [
                'nullable', 'string', 'max:50',
                Rule::unique('satkers', 'kode_satker')->ignore($satker->id),
            ],
            'alamat'      => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $satker->update($validated);

        return redirect()
            ->route('superadmin.satkers.index')
            ->with('success', 'Satker berhasil diperbarui.');
    }

    /**
     * Hapus satker.
     */
    public function destroy(Satker $satker): RedirectResponse
    {
        if ($satker->is_active) {
            return redirect()
                ->route('superadmin.satkers.index')
                ->with('error', 'Satker yang sedang aktif tidak bisa dihapus. Aktifkan satker lain dulu.');
        }

        $satker->delete();

        return redirect()
            ->route('superadmin.satkers.index')
            ->with('success', 'Satker berhasil dihapus.');
    }

    /**
     * Jadikan satker ini sebagai satker aktif.
     * Dipanggil dari tombol "Aktifkan" di list, bukan dari form edit biasa.
     */
    public function setActive(Satker $satker): RedirectResponse
    {
        $satker->update(['is_active' => true]);
        // Observer di Model Satker otomatis nonaktifkan yang lain

        return redirect()
            ->route('superadmin.satkers.index')
            ->with('success', "Satker \"{$satker->nama_satker}\" sekarang aktif.");
    }
}