<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class ArsipImport implements ToModel, WithHeadingRow
{
    /**
     * Parse nilai retensi dari Excel - handle multiline text
     */
    private function parseRetensiString($value)
    {
        if ($value === null) {
            return null;
        }

        // Normalisasi string Excel - handle multiline dengan tag <br>
        $raw = (string) $value;
        $raw = str_replace("\xc2\xa0", ' ', $raw); // NBSP Excel
        $raw = str_replace("<br>", " ", $raw); // Handle tag <br>
        $raw = str_replace("\n", " ", $raw); // Handle newline
        $raw = str_replace("\r", " ", $raw); // Handle carriage return
        $raw = preg_replace('/\s+/', ' ', trim($raw));

        if ($raw === '') {
            return null;
        }

        /**
         * =========================
         * 1️⃣ ANGKA SAJA -> tambah "TAHUN"
         * =========================
         */
        if (ctype_digit($raw)) {
            return $raw . ' TAHUN';
        }

        /**
         * =========================
         * 2️⃣ Sudah format lengkap
         * =========================
         */
        // Cek jika sudah mengandung "TAHUN" (case insensitive)
        if (preg_match('/tahun/i', $raw)) {
            // Uppercase kata "TAHUN" untuk konsistensi
            $raw = preg_replace('/tahun/i', 'TAHUN', $raw);
            return $raw;
        }

        /**
         * =========================
         * 3️⃣ Angka + spasi -> tambah "TAHUN"
         * =========================
         */
        // Cek jika string berisi angka diikuti spasi
        if (preg_match('/^(\d+)\s*$/i', $raw, $matches)) {
            return $matches[1] . ' TAHUN';
        }

        /**
         * =========================
         * 4️⃣ Format lainnya
         * =========================
         */
        return $raw;
    }

    /**
     * Ekstrak angka dari string retensi untuk perhitungan
     */
    private function extractNumberFromText($text)
    {
        if (!$text) {
            return null;
        }
        
        if (preg_match('/\d+/', $text, $matches)) {
            return (int) $matches[0];
        }
        return null;
    }

    /**
     * Hitung retensi seperti di controller
     */
    private function hitungRetensi($aktifTahunText, $inaktifTahunText, $keteranganJRA, $tanggalArsip, $tanggalReferensi = null)
    {
        $result = [
            'aktif_sampai' => null,
            'inaktif_sampai' => null,
            'status_arsip' => 'AKTIF'
        ];
        
        // Cek apakah mengandung kata SETELAH
        $aktifMengandungSetelah = stripos($aktifTahunText, 'SETELAH') !== false;
        $inaktifMengandungSetelah = stripos($inaktifTahunText, 'SETELAH') !== false;
        
        // Jika mengandung SETELAH tapi tanggal referensi kosong, kembalikan status AKTIF saja
        if (($aktifMengandungSetelah || $inaktifMengandungSetelah) && empty($tanggalReferensi)) {
            $result['status_arsip'] = 'AKTIF';
            return $result;
        }
        
        // Ekstrak angka dari teks
        $aktifTahun = $this->extractNumberFromText($aktifTahunText);
        $inaktifTahun = $this->extractNumberFromText($inaktifTahunText);
        
        if (!$aktifTahun || !$inaktifTahun) {
            $result['status_arsip'] = 'AKTIF';
            return $result;
        }
        
        // Tentukan tanggal dasar perhitungan
        if ($aktifMengandungSetelah || $inaktifMengandungSetelah) {
            // Jika ada SETELAH tapi tidak ada tanggal_referensi, gunakan tanggal_arsip
            if ($tanggalReferensi) {
                $tanggalDasar = Carbon::parse($tanggalReferensi);
            } else {
                $tanggalDasar = Carbon::parse($tanggalArsip);
            }
        } else {
            $tanggalDasar = Carbon::parse($tanggalArsip);
        }
        
        // Hitung tanggal aktif sampai
        $aktifSampai = $tanggalDasar->copy()->addYears($aktifTahun);
        
        // Hitung tanggal inaktif sampai (ditambahkan setelah aktif)
        $inaktifSampai = $aktifSampai->copy()->addYears($inaktifTahun);
        
        // Hitung tanggal musnah (untuk keterangan MUSNAH)
        $musnahSampai = $inaktifSampai->copy()->addYears(1);
        
        // Tentukan status arsip berdasarkan tanggal hari ini
        $sekarang = Carbon::now();
        
        if ($keteranganJRA === 'PERMANEN') {
            $result['status_arsip'] = 'PERMANEN';
        } elseif ($keteranganJRA === 'MUSNAH') {
            if ($sekarang <= $aktifSampai) {
                $result['status_arsip'] = 'AKTIF';
            } elseif ($sekarang <= $inaktifSampai) {
                $result['status_arsip'] = 'INAKTIF';
            } elseif ($sekarang <= $musnahSampai) {
                $result['status_arsip'] = 'MUSNAH';
            } else {
                $result['status_arsip'] = 'MUSNAH';
            }
        } else {
            if ($sekarang <= $aktifSampai) {
                $result['status_arsip'] = 'AKTIF';
            } elseif ($sekarang <= $inaktifSampai) {
                $result['status_arsip'] = 'INAKTIF';
            } else {
                $result['status_arsip'] = 'INAKTIF';
            }
        }
        
        // Set tanggal hasil perhitungan
        $result['aktif_sampai'] = $aktifSampai->format('Y-m-d');
        $result['inaktif_sampai'] = $inaktifSampai->format('Y-m-d');
        
        return $result;
    }

    public function model(array $row)
    {
        // =======================
        // DEBUG LOG
        // =======================
        \Log::info('Importing row:', $row);
        
        // =======================
        // NORMALISASI INPUT
        // =======================
        $kodeInput = strtoupper(trim((string) ($row['kode_klasifikasi'] ?? '')));
        $kodeInput = str_replace(' ', '', $kodeInput);

        $subBagianInput = trim((string) ($row['sub_bagian'] ?? ''));

        if (!$kodeInput || !$subBagianInput) {
            \Log::warning('Kode atau Sub Bagian kosong: ' . $kodeInput . ' - ' . $subBagianInput);
            return null;
        }

        $kode = KodeKlasifikasi::where('kode', $kodeInput)->first();
        $subBagian = SubBagian::where('nama_sub_bagian', $subBagianInput)->first();

        if (!$kode) {
            \Log::warning('Kode klasifikasi tidak ditemukan: ' . $kodeInput);
            return null;
        }
        
        if (!$subBagian) {
            \Log::warning('Sub bagian tidak ditemukan: ' . $subBagianInput);
            return null;
        }

        // =======================
        // PARSING RETENSI (STRING LENGKAP) - HANDLE MULTILINE
        // =======================
        $aktifTahun = $this->parseRetensiString($row['aktif'] ?? null);
        $inaktifTahun = $this->parseRetensiString($row['inaktif'] ?? null);

        \Log::info('Parsed retensi:', [
            'aktif_original' => $row['aktif'] ?? null,
            'aktif_parsed' => $aktifTahun,
            'inaktif_original' => $row['inaktif'] ?? null,
            'inaktif_parsed' => $inaktifTahun
        ]);

        // Default jika null
        if (!$aktifTahun) {
            $aktifTahun = '0 TAHUN';
            \Log::warning('Aktif tahun null, set to default: ' . $aktifTahun);
        }
        
        if (!$inaktifTahun) {
            $inaktifTahun = '0 TAHUN';
            \Log::warning('Inaktif tahun null, set to default: ' . $inaktifTahun);
        }

        // =======================
        // TANGGAL
        // =======================
        $tanggalArsip = now();
        if (!empty($row['tanggal_arsip'])) {
            try {
                if (is_numeric($row['tanggal_arsip'])) {
                    $tanggalArsip = Date::excelToDateTimeObject($row['tanggal_arsip']);
                } else {
                    // Coba berbagai format
                    $tanggalStr = trim($row['tanggal_arsip']);
                    
                    // Coba format dengan timestamp (2014-08-07 00:00:00)
                    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $tanggalStr)) {
                        $tanggalArsip = \DateTime::createFromFormat('Y-m-d H:i:s', $tanggalStr);
                    } 
                    // Coba format Y-m-d
                    elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalStr)) {
                        $tanggalArsip = \DateTime::createFromFormat('Y-m-d', $tanggalStr);
                    }
                    // Coba format d/m/Y
                    elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $tanggalStr)) {
                        $tanggalArsip = \DateTime::createFromFormat('d/m/Y', $tanggalStr);
                    }
                    
                    if (!$tanggalArsip) {
                        $tanggalArsip = now();
                        \Log::warning('Format tanggal tidak dikenali: ' . $tanggalStr . ', using now()');
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error parsing tanggal_arsip: ' . $e->getMessage());
                $tanggalArsip = now();
            }
        }

        \Log::info('Tanggal arsip: ' . $tanggalArsip->format('Y-m-d'));

        // =======================
        // KETERANGAN JRA
        // =======================
        // Default ke MUSNAH karena Excel tidak punya kolom ini
        $keteranganJRA = 'MUSNAH';
        
        // =======================
        // TANGGAL REFERENSI (jika ada)
        // =======================
        $tanggalReferensi = null;
        // Tidak ada kolom tanggal_referensi di Excel

        // =======================
        // HITUNG RETENSI OTOMATIS
        // =======================
        \Log::info('Menghitung retensi dengan parameter:', [
            'aktif_tahun' => $aktifTahun,
            'inaktif_tahun' => $inaktifTahun,
            'keterangan_jra' => $keteranganJRA,
            'tanggal_arsip' => $tanggalArsip->format('Y-m-d'),
            'tanggal_referensi' => $tanggalReferensi
        ]);

        $perhitungan = $this->hitungRetensi(
            $aktifTahun,
            $inaktifTahun,
            $keteranganJRA,
            $tanggalArsip->format('Y-m-d'), // Convert to string
            $tanggalReferensi
        );

        \Log::info('Hasil perhitungan:', $perhitungan);

        // =======================
        // NOMOR BOX (handle format Excel)
        // =======================
        $nomorBox = $row['nomor_box'] ?? null;
        if (!empty($nomorBox)) {
            // Handle format time (21:14:00) -> convert to string
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $nomorBox)) {
                // Keep as is
                \Log::info('Nomor box is time format: ' . $nomorBox);
            } elseif (is_numeric($nomorBox) && $nomorBox < 1) {
                $totalMinutes = round($nomorBox * 24 * 60);
                $jam = floor($totalMinutes / 60);
                $menit = $totalMinutes % 60;
                $nomorBox = $jam . '.' . str_pad($menit, 2, '0', STR_PAD_LEFT);
                \Log::info('Nomor box converted from Excel time: ' . $nomorBox);
            }
        }

        // =======================
        // TINGKAT PERKEMBANGAN (default ASLI karena tidak ada di Excel)
        // =======================
        $tingkatPerkembangan = 'ASLI';

        // =======================
        // DATA UNTUK DISIMPAN
        // =======================
        $data = [
            // WAJIB - Data Dasar
            'kode_klasifikasi_id' => $kode->id,
            'uraian_arsip'        => $row['uraian_arsip'] ?? '',
            'sub_bagian_id'       => $subBagian->id,
            'tahun_arsip'         => (string) ($row['tahun_arsip'] ?? date('Y')),
            'tanggal_arsip'       => $tanggalArsip->format('Y-m-d'),
            'jumlah_berkas'       => (int) ($row['jumlah_berkas'] ?? 1),
            'satuan_arsip'        => strtoupper(trim(($row['satuan_arsip'] ?? 'BENDEL'))),

            // Masa Retensi (STRING LENGKAP)
            'aktif_tahun'         => $aktifTahun,
            'inaktif_tahun'       => $inaktifTahun,
            'tanggal_referensi'   => $tanggalReferensi,
            'keterangan_jra'      => $keteranganJRA,

            // Hasil Perhitungan OTOMATIS
            'aktif_sampai'        => $perhitungan['aktif_sampai'],
            'inaktif_sampai'      => $perhitungan['inaktif_sampai'],
            'status_arsip'        => $perhitungan['status_arsip'],

            // OPTIONAL
            'nomor_rak'           => (string) ($row['nomor_rak'] ?? ''),
            'nomor_box'           => (string) $nomorBox,
            'nomor_sampul'        => $row['nomor_sampul'] ?? '',
            'tingkat_perkembangan' => $tingkatPerkembangan,
            'keterangan'          => strtoupper(trim(($row['keterangan'] ?? 'BAIK'))),
            'tanggal_masuk'       => now()->format('Y-m-d'),
            'created_by'          => Auth::id(),
        ];

        \Log::info('Data untuk disimpan:', $data);

        return new Arsip($data);
    }

    /**
     * Aturan heading row
     */
    public function headingRow(): int
    {
        return 1; // Baris pertama adalah header
    }
}