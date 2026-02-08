<?php

namespace App\Exports;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    WithEvents,
    ShouldAutoSize
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\{
    Alignment,
    Border,
    Fill,
    Font
};
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ArsipExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithEvents,
    ShouldAutoSize
{
    protected Request $request;
    protected array $columns;

    public function __construct(Request $request, array $columns)
    {
        $this->request = $request;
        $this->columns = $columns;
    }

    /* ================= QUERY ================= */
    public function query()
    {
        $query = Arsip::query()
            ->with(['kodeKlasifikasi', 'subBagian'])
            ->orderBy('tahun_arsip', 'asc');

        // Filter dari request
        if ($this->request->has('tahun_arsip') && $this->request->tahun_arsip != '') {
            $query->where('tahun_arsip', $this->request->tahun_arsip);
        }

        if ($this->request->has('status_arsip') && $this->request->status_arsip != '') {
            $query->where('status_arsip', $this->request->status_arsip);
        }

        if ($this->request->has('sub_bagian_id') && $this->request->sub_bagian_id != '') {
            $query->where('sub_bagian_id', $this->request->sub_bagian_id);
        }

        return $query;
    }

    /* ================= HEADER KOLOM ================= */
    public function headings(): array
    {
        $map = [
            'kode_klasifikasi' => 'Kode Klasifikasi',
            'uraian_arsip'     => 'Judul Arsip',
            'tahun_arsip'      => 'Tahun',
            'nomor_rak'        => 'Rak',
            'nomor_box'        => 'Box',
            'no_sampul'        => 'No Sampul',
            'aktif_sampai'     => 'Aktif Sampai',
            'inaktif_sampai'   => 'Inaktif Sampai',
            'status_arsip'     => 'Status Arsip',
            'sub_bagian'       => 'Sub Bagian',
            'keterangan'       => 'Kondisi Fisik',
        ];

        $headings = [];
        foreach ($this->columns as $col) {
            $headings[] = $map[$col] ?? ucfirst(str_replace('_', ' ', $col));
        }

        return $headings;
    }

    /* ================= DATA ================= */
    public function map($arsip): array
    {
        $data = [];

        foreach ($this->columns as $col) {
            $value = match ($col) {
                'kode_klasifikasi' => $arsip->kodeKlasifikasi->kode ?? '-',
                'uraian_arsip'     => $arsip->uraian_arsip ?? '-',
                'tahun_arsip'      => $arsip->tahun_arsip ?? '-',
                'nomor_rak'        => $arsip->nomor_rak ?? '-',
                'nomor_box'        => $arsip->nomor_box ?? '-',
                'no_sampul'        => $arsip->no_sampul ?? '-',
                'aktif_sampai'     => $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d-m-Y') : '-',
                'inaktif_sampai'   => $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d-m-Y') : '-',
                'status_arsip'     => $this->formatStatus($arsip->status_arsip),
                'sub_bagian'       => $arsip->subBagian->nama_sub_bagian ?? '-',
                'keterangan'       => $arsip->keterangan ?? '-',
                default            => '-',
            };

            $data[] = $value;
        }

        return $data;
    }

    private function formatStatus($status): string
    {
        $statusMap = [
            'AKTIF' => 'AKTIF',
            'INAKTIF' => 'INAKTIF',
            'USUL_MUSNAH' => 'USUL MUSNAH',
            'PERMANEN' => 'PERMANEN',
            'MUSNAH' => 'MUSNAH'
        ];

        return $statusMap[$status] ?? $status;
    }

    /* ================= STYLING YANG LEBIH SEDERHANA ================= */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalColumns = count($this->columns);
                $lastColumnLetter = Coordinate::stringFromColumnIndex($totalColumns);
                
                // ===== TAMBAHKAN JUDUL DI ATAS DATA =====
                // Hanya tambahkan 1 baris untuk judul
                $sheet->insertNewRowBefore(1, 2); // 2 baris: 1 untuk judul, 1 untuk spacing
                
                // Set judul utama (di row 1)
                $sheet->mergeCells("A1:{$lastColumnLetter}1");
                $sheet->setCellValue('A1', 'DAFTAR ARSIP KPU PROVINSI BALI');
                
                // Format judul utama - lebih kecil dan simple
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12, // Diperkecil dari 16
                        'name' => 'Arial'
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Set tinggi baris judul
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Baris 2 untuk spacing (kosong)
                $sheet->getRowDimension(2)->setRowHeight(5);
                
                // ===== FORMAT HEADER TABEL (row 3) =====
                $headerRow = 3;
                
                // Background header - PUTIH saja (no blue)
                $sheet->getStyle("A{$headerRow}:{$lastColumnLetter}{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFFF'); // Putih
                
                // Font header - HITAM, BOLD saja (no white text)
                $sheet->getStyle("A{$headerRow}:{$lastColumnLetter}{$headerRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true, // TETAP BOLD
                            'color' => ['rgb' => '000000'], // Hitam
                            'size' => 10,
                            'name' => 'Arial'
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                
                // ===== BORDER UNTUK SELURUH TABEL =====
                // Dapatkan last row data (setelah penambahan 2 baris judul)
                $lastDataRow = $sheet->getHighestRow();
                $dataStartRow = $headerRow + 1;
                
                if ($dataStartRow <= $lastDataRow) {
                    // Border untuk HEADER
                    $headerRange = "A{$headerRow}:{$lastColumnLetter}{$headerRow}";
                    $sheet->getStyle($headerRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // Border untuk DATA
                    $dataRange = "A{$dataStartRow}:{$lastColumnLetter}{$lastDataRow}";
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // ===== FORMAT DATA =====
                    // Alignment vertikal TOP untuk data (agar wrap text rapi)
                    $sheet->getStyle("A{$dataStartRow}:{$lastColumnLetter}{$lastDataRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_TOP); // Ganti ke TOP
                    
                    // Wrap text untuk semua cell data
                    $sheet->getStyle("A{$dataStartRow}:{$lastColumnLetter}{$lastDataRow}")
                        ->getAlignment()
                        ->setWrapText(true);
                    
                    // Alignment horizontal khusus untuk kolom tertentu
                    foreach ($this->columns as $index => $col) {
                        $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                        
                        // Untuk kolom tahun, angka rata tengah
                        if ($col === 'tahun_arsip' || $col === 'nomor_rak' || $col === 'nomor_box') {
                            $sheet->getStyle("{$colLetter}{$dataStartRow}:{$colLetter}{$lastDataRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }
                        
                        // Untuk judul arsip, rata kiri
                        if ($col === 'uraian_arsip') {
                            $sheet->getStyle("{$colLetter}{$dataStartRow}:{$colLetter}{$lastDataRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                    }
                    
                    // ===== ALTERNATING ROW COLORS (opsional) =====
                    // Jika ingin zebra striping, uncomment ini:
                    /*
                    for ($row = $dataStartRow; $row <= $lastDataRow; $row++) {
                        if ($row % 2 == 0) {
                            $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('F5F5F5'); // Abu-abu sangat muda
                        }
                    }
                    */
                    
                    // ===== OTOMATIS SET TINGGI BARIS =====
                    for ($row = $headerRow; $row <= $lastDataRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height
                    }
                    
                    // Set tinggi minimum untuk row header
                    $sheet->getRowDimension($headerRow)->setRowHeight(25);
                    
                    // ===== SET LEBAR KOLOM =====
                    $this->setColumnWidths($sheet, $totalColumns);
                }
                
                // ===== PAGE SETUP =====
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                
                // Header akan diulang di setiap halaman
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
            }
        ];
    }
    
    private function setColumnWidths($sheet, $totalColumns): void
    {
        $columnsArray = $this->columns;
        
        // Lebar default yang lebih proporsional
        $widthMap = [
            'kode_klasifikasi' => 15,
            'uraian_arsip'     => 50, // Cukup untuk judul yang panjang
            'tahun_arsip'      => 8,
            'nomor_rak'        => 8,
            'nomor_box'        => 8,
            'no_sampul'        => 10,
            'aktif_sampai'     => 12,
            'inaktif_sampai'   => 12,
            'status_arsip'     => 12,
            'sub_bagian'       => 20,
            'keterangan'       => 12,
        ];
        
        foreach ($columnsArray as $index => $columnName) {
            $colLetter = Coordinate::stringFromColumnIndex($index + 1);
            
            if (isset($widthMap[$columnName])) {
                $sheet->getColumnDimension($colLetter)->setWidth($widthMap[$columnName]);
            } else {
                $sheet->getColumnDimension($colLetter)->setWidth(15); // Default width
            }
        }
        
        // Pastikan kolom judul arsip cukup lebar
        $uraianIndex = array_search('uraian_arsip', $columnsArray);
        if ($uraianIndex !== false) {
            $colLetter = Coordinate::stringFromColumnIndex($uraianIndex + 1);
            $sheet->getColumnDimension($colLetter)->setWidth(50);
        }
    }
}