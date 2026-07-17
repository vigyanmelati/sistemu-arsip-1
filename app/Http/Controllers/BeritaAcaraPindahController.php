<?php
// app/Http/Controllers/BeritaAcaraPindahController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeritaAcaraPindah;
use App\Models\BeritaAcaraDetail;
use App\Models\Arsip;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LampiranBeritaAcaraExport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BeritaAcaraPindahController extends Controller
{
    /**
     * Display list BAP
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Cek apakah user memiliki sub_bagian_id
        if (!$user->sub_bagian_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke fitur ini.');
        }
        
        $query = BeritaAcaraPindah::where('sub_bagian_id', $user->sub_bagian_id);

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search by nomor BAP
        if ($request->has('search') && $request->search != '') {
            $query->where('nomor_bap', 'like', '%' . $request->search . '%');
        }

        $baps = $query->latest()->paginate(10);

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
        ], [
            'nomor_bap.required' => 'Nomor BAP wajib diisi.',
            'tanggal_bap.required' => 'Tanggal BAP wajib diisi.',
        ]);

        $bap = BeritaAcaraPindah::create([
            'nomor_bap'     => $request->nomor_bap,
            'tanggal_bap'   => $request->tanggal_bap,
            'sub_bagian_id' => Auth::user()->sub_bagian_id,
            'created_by'    => Auth::id(),
            'status'        => BeritaAcaraPindah::STATUS_DRAFT
        ]);

        return redirect()->route('berita-acara.index')
            ->with('success', 'Draft Berita acara berhasil dibuat.');
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
        
        if (!$berita_acara->canEdit()) {
            return redirect()->route('berita-acara.index')
                ->with('error', 'Berita Acara yang sudah diajukan tidak dapat diedit.');
        }

        return view('berita-acara.edit', compact('berita_acara'));
    }

    /**
     * Update BAP
     */
    public function update(Request $request, BeritaAcaraPindah $berita_acara)
    {
        $this->authorizeAccess($berita_acara);

        if (!$berita_acara->canEdit()) {
            return redirect()->route('berita-acara.index')
                ->with('error', 'Berita Acara yang sudah diajukan tidak dapat diedit.');
        }

        $request->validate([
            'nomor_bap' => 'required|string|max:100|unique:berita_acara_pindah,nomor_bap,' . $berita_acara->id,
            'tanggal_bap' => 'required|date',
        ]);

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

        if (!$berita_acara->canDelete()) {
            return back()->with('error', 'Berita Acara yang sudah diajukan tidak dapat dihapus.');
        }

        // Hapus file jika ada
        if ($berita_acara->file_bap) {
            Storage::disk('public')->delete('berita_acara/' . $berita_acara->file_bap);
        }

        $berita_acara->delete();

        return redirect()->route('berita-acara.index')
            ->with('success', 'Berita acara berhasil dihapus.');
    }

    /**
     * Upload file dan kirim ke unit kearsipan
     */
    public function kirim(Request $request, BeritaAcaraPindah $berita_acara)
    {
        // Debug: Log request
        \Log::info('Kirim BAP - Start', ['bap_id' => $berita_acara->id]);
        
        try {
            $this->authorizeAccess($berita_acara);

            if (!$berita_acara->canSend()) {
                \Log::warning('Kirim BAP - Cannot send', ['status' => $berita_acara->status]);
                return redirect()->route('berita-acara.index')
                    ->with('error', 'Berita Acara sudah dikirim atau tidak dapat dikirim.');
            }

            // Validasi
            $validator = \Validator::make($request->all(), [
                'file_bap' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
            ], [
                'file_bap.required' => 'File Berita Acara yang sudah ditandatangani wajib diunggah.',
                'file_bap.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
                'file_bap.max' => 'Ukuran file maksimal 10 MB.',
            ]);

            if ($validator->fails()) {
                \Log::warning('Kirim BAP - Validation failed', ['errors' => $validator->errors()->all()]);
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validasi gagal. Silakan periksa file yang diupload.');
            }

            DB::beginTransaction();

            // Upload file
            $file = $request->file('file_bap');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('berita_acara', $fileName, 'public');
            
            if (!$filePath) {
                throw new \Exception('Gagal upload file.');
            }

            // Update BAP
            $updated = $berita_acara->update([
                'file_bap' => $fileName,
                'status' => BeritaAcaraPindah::STATUS_DIAJUKAN,
                // 'tanggal_kirim' => Carbon::now()
            ]);

            if (!$updated) {
                throw new \Exception('Gagal update BAP.');
            }

            // Update detail BAP menjadi DIAJUKAN
            BeritaAcaraDetail::where('bap_id', $berita_acara->id)
                ->update(['status' => 'DIAJUKAN']);

            // Update status arsip menjadi DIAJUKAN
            foreach ($berita_acara->details as $detail) {
                if ($detail->arsip) {
                    $detail->arsip->update([
                        'status_pindah' => 'DIAJUKAN',
                        'file_berita_acara' => $fileName
                    ]);
                }
            }

            DB::commit();
            
            \Log::info('Kirim BAP - Success', ['bap_id' => $berita_acara->id, 'file' => $fileName]);

            return redirect()->route('berita-acara.show', $berita_acara->id)
                ->with('success', 'Berita Acara berhasil dikirim ke Unit Kearsipan.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Kirim BAP - Validation Exception', ['errors' => $e->errors()]);
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Validasi gagal: ' . $e->getMessage());
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Kirim BAP - Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim Berita Acara: ' . $e->getMessage());
        }
    }

    /**
     * Terima BAP (dari Unit Kearsipan)
     */
    public function terima(Request $request, BeritaAcaraPindah $berita_acara)
    {
        // Hanya unit kearsipan yang bisa menerima
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya Unit Kearsipan yang dapat menerima Berita Acara.');
        }

        if ($berita_acara->status !== BeritaAcaraPindah::STATUS_DIAJUKAN) {
            return back()->with('error', 'Berita Acara tidak dalam status diajukan.');
        }

        $request->validate([
            'masa_retensi_aktif' => 'nullable|integer|min:0',
            'masa_retensi_inaktif' => 'nullable|integer|min:0',
            'tanggal_mulai_retensi' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $berita_acara->update([
                'status' => BeritaAcaraPindah::STATUS_DITERIMA,
                'tanggal_diterima' => Carbon::now(),
                'diterima_by' => Auth::id(),
            ]);

            // Update arsip dengan masa retensi dan status pindah
            foreach ($berita_acara->details as $detail) {
                $arsip = $detail->arsip;
                if ($arsip) {
                    $arsip->status_pindah = 'DITERIMA';
                    $arsip->masa_retensi_aktif = $request->masa_retensi_aktif;
                    $arsip->masa_retensi_inaktif = $request->masa_retensi_inaktif;
                    $arsip->tanggal_mulai_retensi = $request->tanggal_mulai_retensi;
                    $arsip->status_arsip = $this->hitungStatusArsip(
                        $request->masa_retensi_aktif,
                        $request->masa_retensi_inaktif,
                        $request->tanggal_mulai_retensi
                    );
                    $arsip->save();
                }

                // Update detail status
                $detail->update(['status' => 'DITERIMA']);
            }

            DB::commit();

            return redirect()->route('kearsipan.berita-acara.show', $berita_acara->id)
                ->with('success', 'Berita Acara berhasil diterima.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menerima: ' . $e->getMessage());
        }
    }

    /**
     * Tolak BAP (dari Unit Kearsipan)
     */
    public function tolak(Request $request, BeritaAcaraPindah $berita_acara)
    {
        // Hanya unit kearsipan yang bisa menolak
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Hanya Unit Kearsipan yang dapat menolak Berita Acara.');
        }

        if ($berita_acara->status !== BeritaAcaraPindah::STATUS_DIAJUKAN) {
            return back()->with('error', 'Berita Acara tidak dalam status diajukan.');
        }

        $request->validate([
            'alasan_ditolak' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $berita_acara->update([
                'status' => BeritaAcaraPindah::STATUS_DITOLAK,
                'tanggal_ditolak' => Carbon::now(),
                'ditolak_by' => Auth::id(),
                'alasan_ditolak' => $request->alasan_ditolak
            ]);

            // Update arsip kembali ke BELUM
            foreach ($berita_acara->details as $detail) {
                if ($detail->arsip) {
                    $detail->arsip->update(['status_pindah' => 'BELUM']);
                }
                $detail->update(['status' => 'DITOLAK']);
            }

            DB::commit();

            return redirect()->route('kearsipan.berita-acara.show', $berita_acara->id)
                ->with('success', 'Berita Acara ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk menghitung status arsip berdasarkan masa retensi
     */
    private function hitungStatusArsip($masaAktif, $masaInaktif, $tanggalMulai)
    {
        if (!$tanggalMulai) return 'AKTIF';

        $mulai = Carbon::parse($tanggalMulai);
        $now = Carbon::now();

        // Hitung tanggal aktif sampai
        $aktifSampai = $mulai->copy()->addYears((int) $masaAktif ?? 0);

        if ($now <= $aktifSampai) {
            return 'AKTIF';
        }

        // Hitung inaktif sampai
        if ($masaInaktif) {
            $inaktifSampai = $aktifSampai->copy()->addYears((int) $masaInaktif);
            if ($now <= $inaktifSampai) {
                return 'INAKTIF';
            }
            return 'HABIS_RETENSI';
        }

        return 'INAKTIF';
    }

    /**
     * Export lampiran BAP
     */
    public function exportLampiran(BeritaAcaraPindah $berita_acara)
    {
        $this->authorizeAccess($berita_acara);

        $namaFile = str_replace(['/', '\\'], '_', $berita_acara->nomor_bap);

        return Excel::download(
            new LampiranBeritaAcaraExport($berita_acara),
            'Lampiran_BA_' . $namaFile . '.xlsx'
        );
    }

    /**
     * Authorization helper
     */
    private function authorizeAccess($bap)
    {
        $user = Auth::user();
        
        // Jika user adalah admin/unit kearsipan, beri akses penuh
        if ( $user->isAdmin()) {
            return true;
        }
        
        // Jika user adalah subbagian, cek kepemilikan
        if ($bap->sub_bagian_id != $user->sub_bagian_id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }
        
        return true;
    }
}