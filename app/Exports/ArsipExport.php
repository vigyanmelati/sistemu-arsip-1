<?php

namespace App\Exports;

use App\Models\Arsip;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize
};

class ArsipExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected Request $request;
    protected array $columns;

    public function __construct(Request $request, array $columns)
    {
        $this->request = $request;
        $this->columns = $columns;
    }

    /**
     * Query data
     */
    public function query()
    {
        return Arsip::query()
            ->with(['kodeKlasifikasi', 'subBagian']);
    }

    /**
     * Heading dinamis
     */
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

        return collect($this->columns)
            ->filter(fn ($c) => isset($map[$c]))
            ->map(fn ($c) => $map[$c])
            ->values()
            ->toArray();
    }

    /**
     * Mapping data dinamis
     */
    public function map($arsip): array
    {
        $data = [];

        foreach ($this->columns as $col) {
            $data[] = match ($col) {
                'kode_klasifikasi' => $arsip->kodeKlasifikasi->kode ?? '-',
                'uraian_arsip'     => $arsip->uraian_arsip ?? '-',
                'tahun_arsip'      => $arsip->tahun_arsip ?? '-',
                'nomor_rak'        => $arsip->nomor_rak ?? '-',
                'nomor_box'        => $arsip->nomor_box ?? '-',
                'no_sampul'        => $arsip->no_sampul ?? '-',
                'aktif_sampai'     => optional($arsip->aktif_sampai)->format('d-m-Y'),
                'inaktif_sampai'   => optional($arsip->inaktif_sampai)->format('d-m-Y'),
                'status_arsip'     => $arsip->status_arsip ?? '-',
                'sub_bagian'       => $arsip->subBagian->nama ?? '-',
                'keterangan'       => $arsip->keterangan ?? '-',
                default            => '-',
            };
        }

        return $data;
    }
}
