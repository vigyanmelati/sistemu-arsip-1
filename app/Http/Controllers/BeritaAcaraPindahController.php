<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeritaAcaraPindah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LampiranBeritaAcaraExport;

class BeritaAcaraPindahController extends Controller
{
    /**
     * Display list BAP
     */
    public function index()
    {
        $user = Auth::user();

        $baps = BeritaAcaraPindah::where('sub_bagian_id', $user->sub_bagian_id)
                    ->latest()
                    ->paginate(10);

        return view('berita-acara.index', compact('baps'));
    }

    /**
     * Show form create
     */
    public function create()
    {
        return view('berita-acara.create');
    }

    /**
     * Store new BAP
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_bap' => 'required|string|max:100|unique:berita_acara_pindah,nomor_bap',
            'tanggal_bap' => 'required|date',
            'file_bap' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ], [
            'file_bap.required' => 'File Berita Acara wajib diunggah.',
            'file_bap.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_bap.max' => 'Ukuran file maksimal 10 MB.',
            'nomor_bap.required' => 'Nomor BAP wajib diisi.',
            'tanggal_bap.required' => 'Tanggal BAP wajib diisi.',
        ]);

        $file = $request->file('file_bap');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('berita_acara', $fileName, 'public');

        BeritaAcaraPindah::create([
            'nomor_bap'     => $request->nomor_bap,
            'tanggal_bap'   => $request->tanggal_bap,
            'sub_bagian_id' => Auth::user()->sub_bagian_id,
            'created_by'    => Auth::id(),
            'file_bap'      => $fileName,
            'status'        => 'DIAJUKAN'
        ]);

        return redirect()->route('berita-acara.index')
            ->with('success', 'Berita acara berhasil dibuat.');
    }

    /**
     * Show detail BAP
     */
    public function show(BeritaAcaraPindah $berita_acara)
    {
        $this->authorizeAccess($berita_acara);

        return view('berita-acara.show', compact('berita_acara'));
    }

    /**
     * Show form edit
     */
    public function edit(BeritaAcaraPindah $berita_acara)
    {
        $this->authorizeAccess($berita_acara);

        return view('berita-acara.edit', compact('berita_acara'));
    }

    /**
     * Update BAP
     */
    public function update(Request $request, BeritaAcaraPindah $berita_acara)
    {
        $this->authorizeAccess($berita_acara);

        $request->validate([
            'nomor_bap' => 'required|string|max:100|unique:berita_acara_pindah,nomor_bap,' . $berita_acara->id,
            'tanggal_bap' => 'required|date',
            'file_bap' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ], [
            'nomor_bap.required' => 'Nomor BAP wajib diisi.',
            'tanggal_bap.required' => 'Tanggal BAP wajib diisi.',
            'file_bap.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_bap.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        // Update file jika ada
        if ($request->hasFile('file_bap')) {
            // hapus file lama
            if ($berita_acara->file_bap) {
                Storage::disk('public')->delete('berita_acara/' . $berita_acara->file_bap);
            }

            $file = $request->file('file_bap');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('berita_acara', $fileName, 'public');

            $berita_acara->file_bap = $fileName;
        }

        $berita_acara->update([
            'nomor_bap'   => $request->nomor_bap,
            'tanggal_bap' => $request->tanggal_bap,
        ]);

        return redirect()->route('berita-acara.index')
            ->with('success', 'Berita acara berhasil diupdate.');
    }

    /**
     * Delete BAP
     */
    public function destroy(BeritaAcaraPindah $berita_acara)
    {
        $this->authorizeAccess($berita_acara);

        // hapus file
        if ($berita_acara->file_bap) {
            Storage::disk('public')->delete('berita_acara/' . $berita_acara->file_bap);
        }

        $berita_acara->delete();

        return back()->with('success', 'Berita acara berhasil dihapus.');
    }

    /**
     * Authorization helper
     */
    private function authorizeAccess($bap)
    {
        if ($bap->sub_bagian_id != Auth::user()->sub_bagian_id) {
            abort(403, 'Tidak punya akses ke data ini.');
        }
    }

 public function exportLampiran(BeritaAcaraPindah $berita_acara)
{
    $this->authorizeAccess($berita_acara);

    $namaFile = str_replace(
        ['/', '\\'],
        '_',
        $berita_acara->nomor_bap
    );

    return Excel::download(
        new LampiranBeritaAcaraExport($berita_acara),
        'Lampiran_BA_' . $namaFile . '.xlsx'
    );
}
}