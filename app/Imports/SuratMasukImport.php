<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\SuratMasuk;
use App\Models\SubBagian;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SuratMasukImport implements ToModel, WithStartRow
{
    public array $errors = [];
    public function startRow(): int
    {
        return 4;
    }
public function validateExcel(array $rows): void
{
    $rowNumber = 4;

    foreach ($rows as $row) {

        $this->validateRow($row, $rowNumber);

        $rowNumber++;
    }
}
private function validateRow(array $row, int $rowNumber): void
{
    // Nomor agenda
    if (empty(trim($row[0] ?? ''))) {
        $this->errors[] =
            "Baris {$rowNumber}: Nomor agenda wajib diisi.";
    }

    // Tanggal dokumen
    if (empty($row[1])) {
        $this->errors[] =
            "Baris {$rowNumber}: Tanggal dokumen wajib diisi.";
    }

    if (empty($row[2])) {
        $this->errors[] =
            "Baris {$rowNumber}: Tanggal penyelesaian wajib diisi.";
    }

    // Nomor dokumen
    if (empty(trim($row[3] ?? ''))) {
        $this->errors[] =
            "Baris {$rowNumber}: Nomor dokumen wajib diisi.";
    }

    // Kepada
    if (empty(trim($row[4] ?? ''))) {
        $this->errors[] =
            "Baris {$rowNumber}: Kepada wajib diisi.";
    }

    // Perihal
    if (empty(trim($row[5] ?? ''))) {
        $this->errors[] =
            "Baris {$rowNumber}: Perihal wajib diisi.";
    }

    

    // Sub Bagian
    if (!empty($row[8])) {

        $subBagian = SubBagian::where(
            'nama_sub_bagian',
            trim($row[8])
        )->first();

        if (!$subBagian) {
            $this->errors[] =
                "Baris {$rowNumber}: Nama sub bagian tidak ditemukan.";
        }
    }
}
    public function model(array $row)
{
    // Cari sub bagian berdasarkan nama
    $subBagian = SubBagian::where(
        'nama_sub_bagian',
        trim($row[8] ?? '')
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

        // KOLOM BARU
        'kepada' => $row[4] ?? null,

        'perihal' => $row[5] ?? null,

        'instansi_satker' => $row[6] ?? null,

        'catatan' => $row[7] ?? null,

        'sub_bagian_id' => $subBagian?->id,
    ]);
}
}