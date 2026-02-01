<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DaftarArsipUsulMusnahExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class PemusnahanController extends Controller
{
    /**
     * Halaman Usulan Pemusnahan
     */
    public function usulan()
    {
        $arsip = Arsip::where('status_arsip', 'USUL_MUSNAH')
            ->where('keterangan_jra', 'MUSNAH')
            ->orderBy('tahun_arsip')
            ->get();

        return view('pemusnahan.usulan.index', compact('arsip'));
    }

    /**
     * Generate PDF Nota Dinas Usul Pemusnahan
     */
    public function notaDinasWord()
    {
        $phpWord = new PhpWord();

        $section = $phpWord->addSection([
            'marginTop'    => 1134,
            'marginBottom' => 1134,
            'marginLeft'   => 1134,
            'marginRight'  => 1134,
        ]);

        /* ================= HEADER ================= */
        $section->addText(
            "KOMISI PEMILIHAN UMUM\nPROVINSI BALI",
            ['bold' => true],
            ['alignment' => 'center']
        );

        $section->addText(
            "Jalan Cok Agung Tresna No. 8 Denpasar 80235\n" .
            "Telpon (0361) 222498  Email prov_bali@kpu.go.id",
            [],
            ['alignment' => 'center']
        );

        $section->addTextBreak(1);
        $section->addLine(['weight' => 1]);

        /* ================= JUDUL ================= */
        $section->addTextBreak(1);
        $section->addText(
            "NOTA DINAS",
            ['bold' => true],
            ['alignment' => 'center']
        );

        $section->addTextBreak(1);

        /* ================= ISI IDENTITAS ================= */
        $table = $section->addTable();

        $table->addRow();
        $table->addCell(3000)->addText("Kepada");
        $table->addCell(6000)->addText(": Yth. Ketua Tim Penilai, Pengelola dan Penerapan Kearsipan");

        $table->addRow();
        $table->addCell()->addText("Dari");
        $table->addCell()->addText(": Sekretaris KPU Provinsi Bali");

        $table->addRow();
        $table->addCell()->addText("Tembusan");
        $table->addCell()->addText(": -");

        $table->addRow();
        $table->addCell()->addText("Nomor");
        $table->addCell()->addText(": 670/TU.05.2-ND/51/" . date('Y'));

        $table->addRow();
        $table->addCell()->addText("Tanggal");
        $table->addCell()->addText(": " . now()->translatedFormat('d F Y'));

        $table->addRow();
        $table->addCell()->addText("Sifat");
        $table->addCell()->addText(": Segera");

        $table->addRow();
        $table->addCell()->addText("Lampiran");
        $table->addCell()->addText(": -");

        $table->addRow();
        $table->addCell()->addText("Perihal");
        $table->addCell()->addText(": Penyusutan Arsip Inaktif Tahun " . date('Y'));

        /* ================= PARAGRAF ================= */
        $section->addTextBreak(1);

        $section->addText(
            "Berdasarkan Peraturan Komisi Pemilihan Umum Nomor 17 Tahun 2023 " .
            "tentang Jadwal Retensi Arsip Komisi Pemilihan Umum, Komisi Pemilihan " .
            "Umum Provinsi dan Komisi Pemilihan Umum Kabupaten/Kota disebutkan " .
            "bahwa arsip yang telah melewati masa retensi dan memiliki keterangan " .
            "musnah dapat dilakukan pemusnahan sesuai dengan prosedur yang berlaku.",
            [],
            ['alignment' => 'both']
        );

        $section->addTextBreak(1);

        $section->addText(
            "Berkenaan dengan hal tersebut, Tim Penilai, Pengelola dan Penerapan " .
            "Kearsipan agar melakukan proses penyusutan terhadap seluruh arsip milik " .
            "KPU Provinsi Bali yang telah berstatus inaktif dan berketerangan musnah " .
            "serta mengajukan proses pemusnahan ke ANRI.",
            [],
            ['alignment' => 'both']
        );

        $section->addTextBreak(1);

        $section->addText(
            "Demikian disampaikan, untuk dapat dilaksanakan dengan penuh tanggung jawab.",
            [],
            ['alignment' => 'both']
        );

        /* ================= TTD ================= */
        $section->addTextBreak(2);
        $section->addText("Sekretaris,");
        $section->addTextBreak(3);
        $section->addText("I Made Oka Purnama", ['bold' => true]);

        /* ================= DOWNLOAD ================= */
        $fileName = 'Nota_Dinas_Usul_Pemusnahan.docx';
        $tempFile = storage_path($fileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }
    /**
     * Export Excel Daftar Arsip Usul Musnah
     */
    public function daftarArsipExcel()
    {
        return Excel::download(
            new DaftarArsipUsulMusnahExport,
            'Daftar_Arsip_Usul_Musnah.xlsx'
        );
    }
}
