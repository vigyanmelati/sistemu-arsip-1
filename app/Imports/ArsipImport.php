<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class ArsipImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Cari relasi
        $kode = KodeKlasifikasi::where('kode', $row['kode_klasifikasi'])->first();
        $subBagian = SubBagian::where('nama_sub_bagian', $row['sub_bagian'])->first();

        if (!$kode || !$subBagian) {
            return null; // skip baris jika relasi tidak ketemu
        }

        return new Arsip([
            'kode_klasifikasi_id' => $kode->id,
            'uraian_arsip'        => $row['uraian_arsip'],
            'sub_bagian_id'       => $subBagian->id,
            'tahun_arsip'         => (string) $row['tahun_arsip'],
            'tanggal_arsip'       => Carbon::parse($row['tanggal_arsip']),
            'jumlah_berkas'       => $row['jumlah_berkas'] ?? 1,
            'satuan_arsip'        => $row['satuan_arsip'] ?? 'LEMBAR',

            'nomor_rak'           => $row['nomor_rak'] ?? '',
            'nomor_box'           => $row['nomor_box'] ?? '',
            'nomor_sampul'        => $row['nomor_sampul'] ?? '',
            'keterangan'          => $row['keterangan'] ?? 'BAIK',

            'status_arsip'        => $row['status_arsip'] ?? 'AKTIF',
            'is_isi_keterangan'   => 0,

            'tanggal_masuk'       => now(),
            'created_by'          => Auth::id(),
        ]);
    }
}
