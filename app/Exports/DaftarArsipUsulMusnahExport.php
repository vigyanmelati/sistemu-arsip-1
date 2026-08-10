<?php

namespace App\Exports;

use App\Models\Pemusnahan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\Satker;

class DaftarArsipUsulMusnahExport implements
    FromCollection,
    WithMapping,
    WithEvents,
    WithCustomStartCell
{
    protected $no = 1;
    protected $pemusnahan;
    protected $onlyMusnah;
    protected $arsipList; // simpan data buat hitung total di AfterSheet

    // Terima Pemusnahan dari controller
    public function __construct(Pemusnahan $pemusnahan, bool $onlyMusnah = false)
    {
        $this->pemusnahan = $pemusnahan;
        $this->onlyMusnah = $onlyMusnah;
    }

    public function collection()
    {
        $data = $this->pemusnahan->details()
            ->where('keputusan', 'musnah')
            ->with('arsip')
            ->get()
            ->pluck('arsip')
            ->filter() // buang null kalau arsip sudah terhapus
            ->sortBy('tahun_arsip')
            ->values();

        // simpan buat dipakai di registerEvents (hitung total per satuan)
        $this->arsipList = $data;

        return $data;
    }

    public function map($arsip): array
    {
        return [
            $this->no++,
            $arsip->uraian_arsip,
            $arsip->tahun_arsip,
            $arsip->jumlah_berkas . ' ' . $arsip->satuan_arsip,
            $arsip->tingkat_perkembangan,
            'Baik',
        ];
    }

    public function startCell(): string
    {
        return 'A9';
    }

    /**
     * ================= STYLING & LAYOUT =================
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                /* =================================================
                 | PAGE SETUP (A4)
                 ================================================= */
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setFitToWidth(1);

                $sheet->getPageMargins()
                    ->setTop(0.75)
                    ->setBottom(0.75)
                    ->setLeft(0.5)
                    ->setRight(0.5);

                /* =================================================
                 | LAMPIRAN (KANAN ATAS)
                 ================================================= */
                $sheet->setCellValue('E1', 'Lampiran Surat Dinas');
                $sheet->setCellValue('E2', 'Nomor : 1/TU.05.2-SD/51/1.2/2026');
                $sheet->setCellValue('E3', 'Tanggal : 2 September 2026');

                $sheet->getStyle('E1:E3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                /* =================================================
                 | JUDUL (DIBERI JARAK)
                 ================================================= */
                // Baris 4 dikosongkan → jarak visual
                $sheet->mergeCells('A5:F5');
                $satkerAktif = Satker::aktif();
                $namaSatker = $satkerAktif ? $satkerAktif->nama_satker : 'KPU PROVINSI BALI';
                $sheet->setCellValue(
                    'A5',
                    'DAFTAR ARSIP MUSNAH '. strtoupper($namaSatker)
                );

                $sheet->getStyle('A5')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                /* =================================================
                 | HEADER TABEL
                 ================================================= */
                $sheet->fromArray([
                    ['No', 'Jenis Arsip', 'Tahun', 'Jumlah', 'Tingkat Perkembangan', 'Keterangan']
                ], null, 'A8');

                $sheet->getStyle('A8:F8')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                /* =================================================
                 | BORDER DATA
                 ================================================= */
                $sheet->getStyle("A8:F{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                /* =================================================
                 | ALIGNMENT DATA (CENTER & TENGAH)
                 ================================================= */
                // No
                $sheet->getStyle("A9:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Tahun & Jumlah
                $sheet->getStyle("C9:D{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Tingkat Perkembangan & Keterangan
                $sheet->getStyle("E9:F{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                /* =================================================
                 | WIDTH KOLOM
                 ================================================= */
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(45);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(22);
                $sheet->getColumnDimension('F')->setWidth(12);

                /* =================================================
                 | WRAP TEXT JENIS ARSIP
                 ================================================= */
                $sheet->getStyle("B9:B{$lastRow}")
                    ->getAlignment()
                    ->setWrapText(true);

                /* =================================================
                 | TOTAL ARSIP (LEMBAR / BENDEL / BUKU / dll)
                 ================================================= */
                $totalPerSatuan = collect($this->arsipList)
                    ->groupBy(function ($arsip) {
                        // normalisasi biar "lembar"/"Lembar" ga dianggap beda
                        return strtolower(trim($arsip->satuan_arsip));
                    })
                    ->map(function ($group) {
                        return $group->sum('jumlah_berkas');
                    });

                // ubah jadi teks per satuan, misal "4358 Lembar"
                $bagian = $totalPerSatuan
                    ->map(function ($jumlah, $satuan) {
                        return $jumlah . ' ' . ucfirst($satuan);
                    })
                    ->values();

                // gabung pakai koma, dan "dan" sebelum item terakhir
                if ($bagian->count() > 1) {
                    $terakhir = $bagian->pop();
                    $ringkasan = $bagian->implode(', ') . ', dan ' . $terakhir;
                } else {
                    $ringkasan = $bagian->first();
                }

                $totalRow = $lastRow + 1; // langsung nempel di bawah tabel, tanpa jarak

                // Label (gabung A:C)
                $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
                $sheet->setCellValue("A{$totalRow}", 'Jumlah arsip yang dapat dimusnahkan');

                // Isi total (gabung D:F biar lega)
                $sheet->mergeCells("D{$totalRow}:F{$totalRow}");
                $sheet->setCellValue("D{$totalRow}", $ringkasan);

                // Border nyambung dengan tabel (A sampai F)
                $sheet->getStyle("A{$totalRow}:F{$totalRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Alignment & wrap text
                $sheet->getStyle("A{$totalRow}:C{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle("D{$totalRow}:F{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // Tinggi baris biar teks yang wrap kebaca jelas
                // $sheet->getRowDimension($totalRow)->setRowHeight(60);
            }
        ];
    }
}