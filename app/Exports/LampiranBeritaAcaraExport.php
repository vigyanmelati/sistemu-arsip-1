<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\BeritaAcaraPindah;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class LampiranBeritaAcaraExport implements FromView, WithStyles, WithEvents
{
    protected $beritaAcara;

    public function __construct(BeritaAcaraPindah $beritaAcara)
    {
        $this->beritaAcara = $beritaAcara;
    }

    public function view(): View
    {
        return view('exports.lampiran-berita-acara', [
            'beritaAcara'   => $this->beritaAcara,
            'namaSubBagian' => optional($this->beritaAcara->subBagian)->nama_sub_bagian ?? '-',
        ]);
    }
    public function styles(Worksheet $sheet)
    {
        return [
            // Judul (row 2)
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
            ],

            // Header tabel (row 8)
            8 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Baris nomor urut kolom (row 9)
            9 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // Judul
                $sheet->mergeCells('B2:H2');
                $sheet->getStyle('B2:H2')
                    ->getAlignment()
                    ->setHorizontal('center');

                // NAMA UNIT PENGOLAH
                $sheet->mergeCells('A3:D3');

                // Lampiran / Nomor / Tanggal -> hanya merge kolom G:H
                $sheet->mergeCells('G4:H4');
                $sheet->mergeCells('G5:H5');
                $sheet->mergeCells('G6:H6');

                // Header tabel: bold + rata tengah
                $sheet->getStyle('A8:H8')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A8:H8')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Wrap text
                $sheet->getStyle('A1:H200')
                    ->getAlignment()
                    ->setWrapText(true);

                // Border mulai dari header sampai baris terakhir data
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A8:H{$lastRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                // Alignment tengah vertikal untuk seluruh tabel
                $sheet->getStyle("A8:H{$lastRow}")
                    ->getAlignment()
                    ->setVertical('center');

                // Tinggi baris otomatis
                for ($i = 1; $i <= $lastRow; $i++) {
                    $sheet->getDelegate()
                        ->getRowDimension($i)
                        ->setRowHeight(-1);
                }

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(50);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(25);
            },
        ];
    }
}