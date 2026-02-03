<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Pemusnahan;
use App\Models\PemusnahanDetail;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DaftarArsipUsulMusnahExport;

class PemusnahanController extends Controller
{
    /**
     * ===============================
     * DAFTAR USULAN PEMUSNAHAN
     * ===============================
     */
    public function index()
    {
        $pemusnahans = Pemusnahan::withCount('details')
            ->latest()
            ->get();

        return view('pemusnahan.usulan.index', compact('pemusnahans'));
    }

    /**
     * ===============================
     * FORM USULAN BARU
     * ===============================
     */
    public function create()
    {
        $arsip = Arsip::where('status_arsip', 'USUL_MUSNAH')
            ->where('keterangan_jra', 'MUSNAH')
            ->orderBy('tahun_arsip')
            ->get();

        return view('pemusnahan.usulan.create', compact('arsip'));
    }

    /**
     * ===============================
     * SIMPAN DRAFT USULAN
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|numeric',
        ]);

        $pemusnahan = Pemusnahan::create([
            'tahun'      => $request->tahun,
            'tanggal_usulan' => now(),
            'status'     => 'draft',
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('pemusnahan.usulan.show', $pemusnahan)
            ->with('success', 'Usulan pemusnahan berhasil dibuat');
    }

    /**
     * ===============================
     * DETAIL USULAN PEMUSNAHAN
     * ===============================
     */
    public function show(Pemusnahan $pemusnahan)
    {
        $arsipList = Arsip::where('status_arsip', 'usul_musnah')
            ->whereDoesntHave('pemusnahanDetails', function ($q) use ($pemusnahan) {
                $q->where('pemusnahan_id', $pemusnahan->id);
            })
            ->orderBy('id')
            ->get();

        return view('pemusnahan.usulan.show', compact(
            'pemusnahan',
            'arsipList'
        ));
    }

    /**
     * ===============================
     * UPDATE KEPUTUSAN ARSIP
     * ===============================
     */
    public function updateKeputusan(Request $request, PemusnahanDetail $detail)
    {
        $request->validate([
            'keputusan' => 'required|in:musnah,tidak_musnah',
        ]);

        $detail->update([
            'keputusan' => $request->keputusan,
            'catatan'   => $request->catatan,
        ]);

        return back()->with('success', 'Keputusan arsip diperbarui');
    }

    /**
     * ===============================
     * FINALISASI PEMUSNAHAN
     * ===============================
     */

        public function finalisasi(Request $request, Pemusnahan $pemusnahan)
        {
            $request->validate([
                'tanggal_pemusnahan' => 'required|date',
            ]);

            // simpan keputusan
            foreach ($request->keputusan as $id => $keputusan) {
                PemusnahanDetail::where('id', $id)->update([
                    'keputusan' => $keputusan,
                    'catatan'   => $request->catatan[$id] ?? null,
                ]);
            }

            // validasi minimal 1 musnah
            if ($pemusnahan->details()->where('keputusan', 'musnah')->count() === 0) {
                return back()->with('error', 'Minimal satu arsip harus dimusnahkan');
            }

            $pemusnahan->update([
                'status' => 'ditetapkan',
                'tanggal_pemusnahan' => $request->tanggal_pemusnahan,
            ]);

            return redirect()
                ->route('pemusnahan.riwayat')
                ->with('success', 'Pemusnahan difinalisasi');
        }

    /**
     * ===============================
     * NOTA DINAS WORD
     * ===============================
     */
    public function notaDinasWord()
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText(
            "KOMISI PEMILIHAN UMUM PROVINSI BALI",
            ['bold' => true],
            ['alignment' => 'center']
        );

        $section->addTextBreak(2);
        $section->addText("NOTA DINAS", ['bold' => true], ['alignment' => 'center']);

        $fileName = 'Nota_Dinas_Pemusnahan.docx';
        $path = storage_path($fileName);

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * ===============================
     * EXPORT EXCEL
     * ===============================
     */
    public function daftarArsipExcel()
    {
        return Excel::download(
            new DaftarArsipUsulMusnahExport,
            'Daftar_Arsip_Usul_Musnah.xlsx'
        );
    }

    /**
     * ===============================
     * RIWAYAT PEMUSNAHAN
     * ===============================
     */
    public function riwayat()
    {
        $pemusnahans = Pemusnahan::where('status', 'ditetapkan')
            ->latest()
            ->get();

        return view('pemusnahan.riwayat.index', compact('pemusnahans'));
    }

    public function tambahArsip(Request $request, Pemusnahan $pemusnahan)
    {
        $request->validate([
            'arsip_id' => 'required|array',
            'arsip_id.*' => 'exists:arsips,id',
        ]);

        foreach ($request->arsip_id as $arsipId) {

            // Cegah duplikasi
            PemusnahanDetail::firstOrCreate(
                [
                    'pemusnahan_id' => $pemusnahan->id,
                    'arsip_id'      => $arsipId,
                ],
                [
                    'keputusan' => 'belum_dinilai',
                ]
            );
        }

        return back()->with('success', 'Arsip berhasil ditambahkan ke pemusnahan');
    }



    public function hapusArsip(Pemusnahan $pemusnahan, Arsip $arsip)
    {
        PemusnahanDetail::where('pemusnahan_id', $pemusnahan->id)
            ->where('arsip_id', $arsip->id)
            ->delete();

        return back()->with('success', 'Arsip dikeluarkan dari daftar');
    }

    public function sidang(Pemusnahan $pemusnahan)
    {
        $pemusnahan->load('details.arsip');

        return view('pemusnahan.sidang.index', compact('pemusnahan'));
    }


}
