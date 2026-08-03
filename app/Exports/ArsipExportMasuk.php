<?php

namespace App\Exports;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ArsipExportMasuk implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithEvents{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Arsip::with(['kodeKlasifikasi', 'subBagian', 'rak', 'box', 'beritaAcaraDetailS.beritaAcara'])
        ->where('status_pindah', 'DIAJUKAN')    
        ->orderBy('created_at', 'desc');

        // Ikuti filter yang sama dengan halaman index (opsional, sesuaikan kebutuhan)
        if ($this->request->filled('sub_bagian_id')) {
            $query->where('sub_bagian_id', $this->request->sub_bagian_id);
        }
        if ($this->request->filled('tahun_arsip')) {
            $query->where('tahun_arsip', $this->request->tahun_arsip);
        }
        if ($this->request->filled('status_arsip')) {
            $query->where('status_arsip', $this->request->status_arsip);
        }
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('uraian_arsip', 'like', "%{$search}%")
                  ->orWhereHas('kodeKlasifikasi', fn($s) => $s->where('kode', 'like', "%{$search}%"))
                  ->orWhereHas('subBagian', fn($s) => $s->where('nama_sub_bagian', 'like', "%{$search}%"));
            });
        }

        // Filter berdasarkan Nomor Berita Acara (BAP) tertentu.
        // Kalau tidak diisi (kosong / "Semua Nomor BAP"), semua data akan ikut ter-export.
        if ($this->request->filled('nomor_bap')) {
            $nomorBap = $this->request->nomor_bap;
            $query->whereHas('beritaAcaraDetailS.beritaAcara', function ($q) use ($nomorBap) {
                $q->where('nomor_bap', $nomorBap);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nomor Berita Acara',
            'Kode Klasifikasi',
            'Sub Bagian',
            'Judul Arsip',
            'Tahun Arsip',
            'Jumlah Berkas',
            'No Rak',
            'No Box',
            'Aktif Tahun',
            'Inaktif Tahun',
            'Keterangan JRA',
            'Aktif Sampai',
            'Inaktif Sampai',
            'Status Arsip',
            'Status Pemindahan',
            'Asal Data',
            'Tanggal Diajukan',
        ];
    }
    public function map($arsip): array
    {
        $nomorBap = optional(
            optional($arsip->beritaAcaraDetailS->first())->beritaAcara
        )->nomor_bap;

        return [
            $nomorBap ?? '-',
            $arsip->kodeKlasifikasi->kode ?? '-',
            $arsip->subBagian->nama_sub_bagian ?? '-',
            $arsip->uraian_arsip,
            $arsip->tahun_arsip,
            $arsip->jumlah_berkas . ' ' . $arsip->satuan_arsip,
            $arsip->rak->nomor_rak ?? '-',
            $arsip->box->nomor_box ?? '-',
            $arsip->aktif_tahun ?? '-',
            $arsip->inaktif_tahun ?? '-',
            $arsip->keterangan_jra ?? '-',
            $arsip->aktif_sampai
                ? Carbon::parse($arsip->aktif_sampai)->format('d/m/Y')
                : '-',
            $arsip->inaktif_sampai
                ? Carbon::parse($arsip->inaktif_sampai)->format('d/m/Y')
                : '-',
            $arsip->status_arsip ?? '-',
            $arsip->status_pindah ?? '-',
            $arsip->asal_data === 'IMPORT'
                ? 'Import Excel'
                : 'Sub Bagian',
            $arsip->created_at
                ? $arsip->created_at->format('d/m/Y H:i')
                : '-',
        ];
    }

    public function registerEvents(): array
{
    
    return [
       AfterSheet::class => function (AfterSheet $event) {

            // Sisipkan 3 baris
            $event->sheet->insertNewRowBefore(1, 3);

            // Judul
            $event->sheet->mergeCells('A2:Q2');
            $event->sheet->setCellValue('A2', 'DAFTAR ARSIP MASUK');

            $event->sheet->getStyle('A2')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Header
            $event->sheet->getStyle('A4:Q4')->applyFromArray([
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'D9EAD3', // hijau muda (opsional)
                    ],
                ],
            ]);

            // Jumlah baris terakhir
            $lastRow = $event->sheet->getHighestRow();

            // Border seluruh tabel
            $event->sheet->getStyle("A4:Q{$lastRow}")
                ->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '000000',
                            ],
                        ],
                    ],
                ]);

            // Tengah untuk beberapa kolom
            $event->sheet->getStyle("A4:Q{$lastRow}")
                ->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        }
    ];
}

   public function styles(Worksheet $sheet)
{
    return [];
}
}