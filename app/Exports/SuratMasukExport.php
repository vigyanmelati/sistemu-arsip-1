<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SuratMasukExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        return SuratMasuk::select(
            'nomor_agenda',
            'tanggal_dokumen',
            'tanggal_penyelesaian',
            'nomor_dokumen',
            'perihal',
            'instansi_satker',
            'catatan'
        )
        ->get()
        ->map(function ($item) {

            return [
                $item->nomor_agenda,

                $item->tanggal_dokumen
                    ? \Carbon\Carbon::parse($item->tanggal_dokumen)
                        ->translatedFormat('d F Y')
                    : '-',

                $item->tanggal_penyelesaian
                    ? \Carbon\Carbon::parse($item->tanggal_penyelesaian)
                        ->translatedFormat('d F Y')
                    : '-',

                $item->nomor_dokumen,
                $item->perihal,
                $item->instansi_satker,
                $item->catatan,
            ];
        });
    }

    public function headings(): array
    {
        return [
            ['DAFTAR SURAT MASUK'],
            [],
            [
                'No Agenda',
                'Tanggal Dokumen',
                'Tanggal Penyelesaian',
                'No Surat',
                'Perihal',
                'Asal Dokumen',
                'Keterangan',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Judul
        $sheet->mergeCells('A1:G1');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ]);

        // Header tabel
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);

        // Isi tabel
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle("A3:G{$lastRow}")
            ->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ],
                'alignment' => [
                    'vertical' => 'center',
                ]
            ]);

        // Tinggi row judul
        $sheet->getRowDimension(1)->setRowHeight(28);

        return [];
    }
}