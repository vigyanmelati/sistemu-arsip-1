<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\SuratMasuk;
use App\Models\SubBagian;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SuratMasukImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 4;
    }

    public function model(array $row)
    {
        // Cari sub bagian berdasarkan nama
        $subBagian = SubBagian::where(
            'nama_sub_bagian',
            trim($row[7] ?? '')
        )->first();

        return new SuratMasuk([

            'nomor_agenda' => $row[0] ?? null,

            'tanggal_dokumen' => !empty($row[1])
                ? Carbon::parse($row[1])->format('Y-m-d')
                : null,

            'tanggal_penyelesaian' => !empty($row[2])
                ? Carbon::parse($row[2])->format('Y-m-d')
                : null,

            'nomor_dokumen' => $row[3] ?? null,

            'perihal' => $row[4] ?? null,

            'instansi_satker' => $row[5] ?? null,

            'catatan' => $row[6] ?? null,

            // Foreign key dari tabel sub_bagians
            'sub_bagian_id' => $subBagian?->id,
        ]);
    }
}