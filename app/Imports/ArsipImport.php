<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ArsipImport implements ToModel, WithHeadingRow
{
    private function parseRetensi($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return ['tahun' => null, 'keterangan' => null];
        }

        $raw = trim((string) $value);
        
        // 1. Cek jika hanya angka (misal: "2" atau "2 tahun")
        if (is_numeric($raw)) {
            return ['tahun' => (int) $raw, 'keterangan' => null];
        }
        
        // 2. Cek pola "X tahun" (case insensitive, boleh ada spasi)
        // Contoh: "2 tahun", "2 Tahun", "2  tahun"
        if (preg_match('/^\s*(\d+)\s*tahun\s*$/i', $raw)) {
            preg_match('/\d+/', $raw, $matches);
            return ['tahun' => (int) $matches[0], 'keterangan' => null];
        }
        
        // 3. Cek jika mengandung teks lebih dari sekedar "tahun"
        // Contoh: "1 Tahun Setelah Barang Tidak Dikuasai"
        // Ambil angka pertama dan seluruh teks sebagai keterangan
        if (preg_match('/(\d+)/', $raw, $matches)) {
            return [
                'tahun' => (int) $matches[1],
                'keterangan' => $raw
            ];
        }
        
        // 4. Fallback
        return ['tahun' => null, 'keterangan' => $raw];
    }

    public function model(array $row)
    {
        // =======================
        // NORMALISASI INPUT
        // =======================
        $kodeInput = strtoupper(trim((string) ($row['kode_klasifikasi'] ?? '')));
        $kodeInput = str_replace(' ', '', $kodeInput);

        $subBagianInput = trim((string) ($row['sub_bagian'] ?? ''));

        if (!$kodeInput || !$subBagianInput) {
            return null;
        }

        $kode = KodeKlasifikasi::where('kode', $kodeInput)->first();
        $subBagian = SubBagian::where('nama_sub_bagian', $subBagianInput)->first();

        if (!$kode || !$subBagian) {
            return null;
        }

        // =======================
        // RETENSI
        // =======================
        $aktif   = $this->parseRetensi($row['aktif'] ?? null);
        $inaktif = $this->parseRetensi($row['inaktif'] ?? null);

        // =======================
        // TANGGAL
        // =======================
        $tanggalArsip = now();
        if (!empty($row['tanggal_arsip'])) {
            try {
                if (is_numeric($row['tanggal_arsip'])) {
                    $tanggalArsip = Date::excelToDateTimeObject($row['tanggal_arsip']);
                } else {
                    $tanggalArsip =
                        \DateTime::createFromFormat('d/m/Y', $row['tanggal_arsip'])
                        ?: \DateTime::createFromFormat('Y-m-d', $row['tanggal_arsip'])
                        ?: now();
                }
            } catch (\Exception $e) {
                $tanggalArsip = now();
            }
        }

        $nomorBox = $row['nomor_box'] ?? null;
        if (is_numeric($nomorBox) && $nomorBox < 1) {
            $totalMinutes = round($nomorBox * 24 * 60);
            $jam = floor($totalMinutes / 60);
            $menit = $totalMinutes % 60;
            $nomorBox = $jam . '.' . str_pad($menit, 2, '0', STR_PAD_LEFT);
        }

        return new Arsip([
            'kode_klasifikasi_id' => $kode->id,
            'uraian_arsip'        => $row['uraian_arsip'] ?? '',
            'sub_bagian_id'       => $subBagian->id,
            'tahun_arsip'         => (string) ($row['tahun_arsip'] ?? ''),
            'tanggal_arsip'       => $tanggalArsip,
            'jumlah_berkas'       => $row['jumlah_berkas'] ?? 1,
            'satuan_arsip'        => $row['satuan_arsip'] ?? 'BENDEL',

            'nomor_rak'           => $row['nomor_rak'] ?? '',
            'nomor_box'           => $nomorBox,
            'nomor_sampul'        => $row['nomor_sampul'] ?? '',
            'keterangan'          => $row['keterangan'] ?? 'BAIK',

            // RETENSI
            'masa_aktif_tahun'    => $aktif['tahun'],
            'aktif_keterangan'    => $aktif['keterangan'],

            'masa_inaktif_tahun'  => $inaktif['tahun'],
            'inaktif_keterangan'  => $inaktif['keterangan'],

            'status_arsip'        => 'AKTIF',
            'is_isi_keterangan'   => 0,
            'tanggal_masuk'       => now(),
            'created_by'          => Auth::id(),
        ]);
    }
}