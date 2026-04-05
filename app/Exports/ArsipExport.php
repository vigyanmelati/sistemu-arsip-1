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
    protected $totalBenedel = 0;
    protected $totalLembar = 0;

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
            ->whereIn('status_pindah', [
            'DIPINDAHKAN',
            'LANGSUNG'
        ])
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

        // Filter tambahan
        if ($this->request->has('kode_klasifikasi_id') && $this->request->kode_klasifikasi_id != '') {
            $query->where('kode_klasifikasi_id', $this->request->kode_klasifikasi_id);
        }

        if ($this->request->has('nomor_rak') && $this->request->nomor_rak != '') {
            $query->where('nomor_rak', $this->request->nomor_rak);
        }

        if ($this->request->has('nomor_box') && $this->request->nomor_box != '') {
            $query->where('nomor_box', $this->request->nomor_box);
        }

        if ($this->request->has('keterangan') && $this->request->keterangan != '') {
            $query->where('keterangan', $this->request->keterangan);
        }

        return $query;
    }

    /* ================= HEADER KOLOM ================= */
    public function headings(): array
    {
        $map = [
            'kode_klasifikasi' => 'Kode Klasifikasi',
            'uraian_arsip'     => 'Judul Arsip',
            'jumlah'           => 'Jumlah',
            'tahun_arsip'      => 'Tahun',
            'nomor_rak'        => 'Rak',
            'nomor_box'        => 'Box',
            'no_sampul'        => 'No Sampul',
            'aktif_sampai'     => 'Aktif Sampai',
            'inaktif_sampai'   => 'Inaktif Sampai',
            'status_arsip'     => 'Status Arsip',
            'sub_bagian'       => 'Sub Bagian',
            'keterangan'       => 'Keterangan', // Diubah dari Kondisi Fisik
            'tingkat_perkembangan' => 'Tingkat Perkembangan',
            'keterangan_jra'   => 'Keterangan JRA',
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
        
        // Hitung total untuk perhitungan di akhir
        if ($arsip->satuan_arsip === 'Benedel') {
            $this->totalBenedel += $arsip->jumlah_berkas;
        } elseif ($arsip->satuan_arsip === 'Lembar') {
            $this->totalLembar += $arsip->jumlah_berkas;
        }

        foreach ($this->columns as $col) {
            $value = match ($col) {
                'kode_klasifikasi' => $arsip->kodeKlasifikasi->kode ?? '-',
                'uraian_arsip'     => $arsip->uraian_arsip ?? '-',
                'jumlah'           => $this->formatJumlah($arsip),
                'tahun_arsip'      => $arsip->tahun_arsip ?? '-',
                'nomor_rak'        => $arsip->nomor_rak ?? '-',
                'nomor_box'        => $arsip->nomor_box ?? '-',
                'no_sampul'        => $arsip->no_sampul ?? '-',
                'aktif_sampai'     => $arsip->aktif_sampai ? \Carbon\Carbon::parse($arsip->aktif_sampai)->format('d-m-Y') : '-',
                'inaktif_sampai'   => $arsip->inaktif_sampai ? \Carbon\Carbon::parse($arsip->inaktif_sampai)->format('d-m-Y') : '-',
                'status_arsip'     => $this->formatStatus($arsip->status_arsip),
                'sub_bagian'       => $arsip->subBagian->nama_sub_bagian ?? '-',
                'keterangan'       => $arsip->keterangan ?? '-',
                'tingkat_perkembangan' => $arsip->tingkat_perkembangan ?? '-',
                'keterangan_jra'   => $arsip->keterangan_jra ?? '-',
                default            => '-',
            };

            $data[] = $value;
        }

        return $data;
    }

    private function formatJumlah($arsip): string
    {
        $jumlah = $arsip->jumlah_berkas ?? 0;
        $satuan = $arsip->satuan_arsip ?? '';
        
        if ($jumlah > 0 && $satuan) {
            return "{$jumlah} {$satuan}";
        }
        
        return '-';
    }

    private function formatStatus($status): string
    {
        $statusMap = [
            'AKTIF' => 'AKTIF',
            'INAKTIF' => 'INAKTIF',
            // 'HABIS_RETENSI' => 'HABIS RETENSI',
             'HABIS_RETENSI' => 'HABIS RETESNI',
            'PERMANEN' => 'PERMANEN',
            'MUSNAH' => 'MUSNAH'
        ];

        return $statusMap[$status] ?? $status;
    }

    /* ================= STYLING DENGAN PERHITUNGAN TOTAL ================= */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalColumns = count($this->columns);
                $lastColumnLetter = Coordinate::stringFromColumnIndex($totalColumns);
                
                // ===== TAMBAHKAN JUDUL =====
                $sheet->insertNewRowBefore(1, 2);
                $sheet->mergeCells("A1:{$lastColumnLetter}1");
                $sheet->setCellValue('A1', 'DAFTAR ARSIP KPU PROVINSI BALI');
                
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Arial'
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(2)->setRowHeight(5);
                
                // ===== FORMAT HEADER TABEL =====
                $headerRow = 3;
                
                // Background header putih
                $sheet->getStyle("A{$headerRow}:{$lastColumnLetter}{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFFFF');
                
                // Font header
                $sheet->getStyle("A{$headerRow}:{$lastColumnLetter}{$headerRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '000000'],
                            'size' => 10,
                            'name' => 'Arial'
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                
                // ===== BORDER UNTUK TABEL =====
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
                    $sheet->getStyle("A{$dataStartRow}:{$lastColumnLetter}{$lastDataRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_TOP);
                    
                    $sheet->getStyle("A{$dataStartRow}:{$lastColumnLetter}{$lastDataRow}")
                        ->getAlignment()
                        ->setWrapText(true);
                    
                    // ===== TAMBAHKAN TOTAL DI BAWAH =====
                    $totalRow = $lastDataRow + 2;
                    
                    // Merge cells untuk total
                    $sheet->mergeCells("A{$totalRow}:{$lastColumnLetter}{$totalRow}");
                    $sheet->setCellValue("A{$totalRow}", 
                        "TOTAL ARSIP YANG DIEKSPOR: {$this->totalBenedel} Benedel, {$this->totalLembar} Lembar");
                    
                    // Format total
                    $sheet->getStyle("A{$totalRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 11,
                            'color' => ['rgb' => '000000'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E8F5E9'], // Hijau muda
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000'],
                            ],
                        ],
                    ]);
                    
                    // ===== SETTING KOLOM =====
                    $this->setColumnWidths($sheet, $totalColumns);
                    
                    // ===== AUTO HEIGHT =====
                    for ($row = $headerRow; $row <= $totalRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(-1);
                    }
                    $sheet->getRowDimension($headerRow)->setRowHeight(25);
                    $sheet->getRowDimension($totalRow)->setRowHeight(30);
                }
                
                // ===== PAGE SETUP =====
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);
            }
        ];
    }
    
    private function setColumnWidths($sheet, $totalColumns): void
    {
        $columnsArray = $this->columns;
        
        $widthMap = [
            'kode_klasifikasi' => 15,
            'uraian_arsip'     => 50,
            'jumlah'           => 15,
            'tahun_arsip'      => 8,
            'nomor_rak'        => 8,
            'nomor_box'        => 8,
            'no_sampul'        => 10,
            'aktif_sampai'     => 12,
            'inaktif_sampai'   => 12,
            'status_arsip'     => 12,
            'sub_bagian'       => 20,
            'keterangan'       => 15,
            'tingkat_perkembangan' => 15,
            'keterangan_jra'   => 15,
        ];
        
        foreach ($columnsArray as $index => $columnName) {
            $colLetter = Coordinate::stringFromColumnIndex($index + 1);
            
            if (isset($widthMap[$columnName])) {
                $sheet->getColumnDimension($colLetter)->setWidth($widthMap[$columnName]);
            } else {
                $sheet->getColumnDimension($colLetter)->setWidth(15);
            }
        }
    }
}