<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ArsipImportSubBagian implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsOnFailure, 
    SkipsEmptyRows
{
    use SkipsFailures;

    public int $importedRows = 0;
    public array $errors = [];

    /**
     * Aturan validasi untuk setiap kolom
     */
    public function rules(): array
    {
        return [
            // WAJIB
            'kode_klasifikasi' => [
                'required',
                function ($attribute, $value, $fail) {
                    $kode = str_replace(' ', '', strtoupper(trim($value)));
                    $exists = KodeKlasifikasi::where('kode', $kode)->exists();
                    if (!$exists) {
                        $fail('Kode klasifikasi tidak ditemukan di database.');
                    }
                }
            ],
            'jenis_arsip' => 'required|string|max:500',
            'kurun_waktu' => 'required|numeric|min:2000|max:' . (date('Y') + 1),
            
            // JUMLAH (format: "angka<spasi>satuan")
            'jumlah' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^\d+\s+(BENDEL|LEMBAR)$/i', trim($value))) {
                        $fail('Format harus "angka<spasi>satuan" dengan satuan BENDEL atau LEMBAR, .');
                    }
                }
            ],

            // KETERANGAN (format: "MEDIA / KONDISI")
            'keterangan' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $parts = array_map('trim', explode('/', $value));
                        if (count($parts) !== 2) {
                            $fail('Format harus "MEDIA / KONDISI" (contoh: TEKSTUAL / BAIK)');
                        } else {
                            $media = strtoupper($parts[0]);
                            $kondisi = strtoupper($parts[1]);
                            if (!in_array($media, ['TEKSTUAL', 'DIGITAL'])) {
                                $fail('Media harus TEKSTUAL atau DIGITAL.');
                            }
                            if (!in_array($kondisi, ['BAIK', 'RUSAK', 'HILANG'])) {
                                $fail('Kondisi harus BAIK, RUSAK, atau HILANG.');
                            }
                        }
                    }
                }
            ],

            // OPTIONAL
            'tingkat_perkembangan' => 'nullable|in:ASLI,COPY,SALINAN',
            'link_foto'            => 'nullable|url|max:1000',
            'namanomor_box' => 'nullable',
            'nama_raklemari' => 'nullable',
            'aktif'                => 'nullable|string|max:100',
            'inaktif'              => 'nullable|string|max:100',
        ];
    }

    /**
     * Pesan error kustom
     */
    // public function customValidationMessages()
    // {
    //     return [
    //         'kode_klasifikasi.required' => 'Kode klasifikasi wajib diisi.',
    //         'jenis_arsip.required'      => 'Jenis arsip wajib diisi.',
    //         'kurun_waktu.required'      => 'Tahun arsip wajib diisi.',
    //         'kurun_waktu.numeric'       => 'Tahun arsip harus berupa angka.',
    //         'kurun_waktu.min'           => 'Tahun arsip minimal 2000.',
    //         'kurun_waktu.max'           => 'Tahun arsip maksimal ' . (date('Y') + 1) . '.',
    //         'jumlah.required'           => 'Jumlah berkas wajib diisi.',
    //         'tingkat_perkembangan.in'   => 'Tingkat perkembangan harus ASLI, COPY, atau SALINAN.',
    //         'link_foto.url'             => 'Link foto harus URL yang valid.',
    //     ];
    // }
public function validateExcel(array $rows): void
{
    $rowNumber = 6;

    foreach ($rows as $row) {

        $this->validateRow(
            $row,
            $rowNumber
        );

        $rowNumber++;
    }
}
    /**
     * Lewati baris kosong
     */
    public function isEmptyWhen(array $row): bool
    {
        return empty(trim($row['kode_klasifikasi'] ?? '')) &&
               empty(trim($row['jenis_arsip'] ?? '')) &&
               empty(trim($row['kurun_waktu'] ?? ''));
    }

    /**
     * Heading row (baris ke-5)
     */
    public function headingRow(): int
    {
        return 5;
    }

    private function validateRow(array $row, int $rowNumber): void
{
    $requiredFields = [
        'kode_klasifikasi' => 'Kode Klasifikasi',
        'jenis_arsip'      => 'Jenis Arsip',
        'kurun_waktu'      => 'Kurun Waktu',
        'jumlah'           => 'Jumlah',
    ];

    foreach ($requiredFields as $field => $label) {

        if (
            !isset($row[$field]) ||
            trim((string)$row[$field]) === ''
        ) {
            $this->errors[] =
                "Baris {$rowNumber}: {$label} wajib diisi.";
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KODE KLASIFIKASI
    |--------------------------------------------------------------------------
    */

    if (!empty($row['kode_klasifikasi'])) {

        $kodeInput = strtoupper(
            str_replace(
                ' ',
                '',
                trim($row['kode_klasifikasi'])
            )
        );

        $kode = KodeKlasifikasi::whereRaw(
            'REPLACE(kode," ","") = ?',
            [$kodeInput]
        )->first();

        if (!$kode) {
            $this->errors[] =
                "Baris {$rowNumber}: Kode klasifikasi '{$row['kode_klasifikasi']}' tidak ditemukan.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TAHUN ARSIP
    |--------------------------------------------------------------------------
    */

    if (!empty($row['kurun_waktu'])) {

        if (!is_numeric($row['kurun_waktu'])) {

            $this->errors[] =
                "Baris {$rowNumber}: Kurun waktu harus berupa angka.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | JUMLAH
    |--------------------------------------------------------------------------
    */

    if (!empty($row['jumlah'])) {

        $jumlah = strtoupper(
            trim((string)$row['jumlah'])
        );

        preg_match('/[A-Z]+/', $jumlah, $match);

        $satuan = $match[0] ?? '';

        $allowed = [
            'BENDEL',
            'LEMBAR',
        ];

        if (!in_array($satuan, $allowed)) {

            $this->errors[] =
                "Baris {$rowNumber}: Satuan hanya boleh BENDEL atau  LEMBAR.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TINGKAT PERKEMBANGAN
    |--------------------------------------------------------------------------
    */

    if (!empty($row['tingkat_perkembangan'])) {

        $allowed = [
            'ASLI',
            'COPY',
            'SALINAN'
        ];

        $value = strtoupper(
            trim($row['tingkat_perkembangan'])
        );

        if (!in_array($value, $allowed)) {

            $this->errors[] =
                "Baris {$rowNumber}: Tingkat perkembangan hanya boleh ASLI, COPY atau SALINAN.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | LINK FOTO
    |--------------------------------------------------------------------------
    */

    if (!empty($row['link_foto'])) {

        if (!filter_var($row['link_foto'], FILTER_VALIDATE_URL)) {

            $this->errors[] =
                "Baris {$rowNumber}: Link foto tidak valid.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | KETERANGAN
    |--------------------------------------------------------------------------
    */

    if (!empty($row['keterangan'])) {

        $parts = array_map(
            'trim',
            explode('/', $row['keterangan'])
        );

        if (count($parts) !== 2) {

            $this->errors[] =
                "Baris {$rowNumber}: Keterangan harus berformat MEDIA / KONDISI.";

        } else {

            $media = strtoupper($parts[0]);
            $kondisi = strtoupper($parts[1]);

            if (
                !in_array(
                    $media,
                    ['TEKSTUAL', 'DIGITAL']
                )
            ) {

                $this->errors[] =
                    "Baris {$rowNumber}: Media arsip hanya boleh TEKSTUAL atau DIGITAL.";
            }

            if (
                !in_array(
                    $kondisi,
                    ['BAIK', 'RUSAK', 'HILANG']
                )
            ) {

                $this->errors[] =
                    "Baris {$rowNumber}: Kondisi arsip hanya boleh BAIK, RUSAK atau HILANG.";
            }
        }
    }
}
public function customValidationMessages()
{
    return [

        'kode_klasifikasi.required'
            => 'Kode klasifikasi wajib diisi.',

        'jenis_arsip.required'
            => 'Jenis arsip wajib diisi.',

        'kurun_waktu.required'
            => 'Kurun waktu wajib diisi.',

        'jumlah.required'
            => 'Jumlah wajib diisi.',

        'tingkat_perkembangan.in'
            => 'Tingkat perkembangan tidak valid.',

        'link_foto.url'
            => 'Link foto tidak valid.',

    ];
}
    /**
     * Proses setiap baris yang valid
     */
    public function model(array $row)
    {
// dd($row);
    if (
    empty(trim($row['kode_klasifikasi'] ?? '')) &&
    empty(trim($row['jenis_arsip'] ?? '')) &&
    empty(trim($row['kurun_waktu'] ?? ''))
) {
    return null;
}
        // Data sudah tervalidasi, kita bisa langsung proses
        Log::info('Import row:', $row);

        // Ambil user dan sub bagian
        $user = Auth::user();
        if (!$user) {
            Log::warning('User tidak login');
            return null;
        }

        $subBagian = $user->subBagian;
        if (!$subBagian) {
            Log::warning('Sub bagian tidak ditemukan untuk user: ' . $user->id);
            return null;
        }

        // Cari kode klasifikasi
        $kodeInput = str_replace(' ', '', strtoupper(trim($row['kode_klasifikasi'])));
        $kode = KodeKlasifikasi::where('kode', $kodeInput)->first();
        if (!$kode) {
            Log::warning('Kode tidak ditemukan: ' . $kodeInput);
            return null;
        }

        // Parsing data
        $uraianArsip = trim($row['jenis_arsip'] ?? '');
        $tahunArsip  = (int) ($row['kurun_waktu'] ?? date('Y'));
        $tingkatPerkembangan = strtoupper(trim($row['tingkat_perkembangan'] ?? 'ASLI'));
        if (!in_array($tingkatPerkembangan, ['ASLI', 'COPY', 'SALINAN'])) {
            $tingkatPerkembangan = 'ASLI';
        }

        // Link foto
        $linkFoto = trim($row['link_foto'] ?? '');
        if ($linkFoto && !filter_var($linkFoto, FILTER_VALIDATE_URL)) {
            $linkFoto = null; // validasi sudah memastikan URL, tapi amankan
        }

        // Jumlah dan satuan
        $jumlahRaw = strtoupper(trim($row['jumlah'] ?? '1 BENDEL'));
        preg_match('/\d+/', $jumlahRaw, $angka);
        $jumlahBerkas = isset($angka[0]) ? (int) $angka[0] : 1;
        preg_match('/[A-Z]+/', $jumlahRaw, $satuan);
        $satuanArsip = isset($satuan[0]) ? $satuan[0] : 'BENDEL';

        // Keterangan (media/kondisi)
        $mediaArsip = 'TEKSTUAL';
        $kondisiFisik = 'BAIK';
        $keteranganRaw = trim($row['keterangan'] ?? '');
        if ($keteranganRaw) {
            $parts = array_map('trim', explode('/', $keteranganRaw));
            if (count($parts) === 2) {
                $mediaArsip = strtoupper($parts[0]);
                $kondisiFisik = strtoupper($parts[1]);
            }
        }

        // Nomor box/rak
        $nomorBox = $row['namanomor_box'] ?? '';
        $nomorRak = $row['nama_raklemari'] ?? '';

        // Retensi (ambil dari kode klasifikasi jika tidak ada di Excel)
        $aktifTahun = $this->parseRetensiString($row['aktif'] ?? $kode->aktif_tahun ?? null);
        $inaktifTahun = $this->parseRetensiString($row['inaktif'] ?? $kode->inaktif_tahun ?? null);
        $keteranganJRA = $kode->keterangan_jra ?? 'BELUM DITENTUKAN';

        // Tanggal arsip (dari tahun)
        $tanggalArsip = Carbon::createFromDate($tahunArsip, 1, 1);

        // Hitung retensi
        $perhitungan = $this->hitungRetensi(
            $aktifTahun,
            $inaktifTahun,
            $keteranganJRA,
            $tanggalArsip->format('Y-m-d'),
            null
        );

        // Lokasi arsip dari mapping sub bagian
        $lokasiMapping = [
            'SUB BAGIAN UMUM DAN LOGISTIK' => 'RUANG_SUBBAGIAN_UMUM_LOGISTIK',
            'SUB BAGIAN PARTISIPASI MASYARAKAT DAN SDM' => 'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM',
            'SUB BAGIAN KEUANGAN' => 'RUANG_SUBBAGIAN_KEUANGAN',
            'SUB BAGIAN PERENCANAAN DATA DAN INFORMASI' => 'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI',
            'SUB BAGIAN TEKNIS' => 'RUANG_SUBBAGIAN_TEKNIS',
            'SUB BAGIAN HUKUM' => 'RUANG_SUBBAGIAN_HUKUM',
        ];
        $namaSubBagian = strtoupper(trim($subBagian->nama_sub_bagian ?? ''));
        $lokasiArsip = $lokasiMapping[$namaSubBagian] ?? null;

        // Siapkan data
        $data = [
            'kode_klasifikasi_id' => $kode->id,
            'uraian_arsip'        => $uraianArsip,
            'sub_bagian_id'       => $subBagian->id,
            'tahun_arsip'         => (string) $tahunArsip,
            'tanggal_arsip'       => $tanggalArsip->format('Y-m-d'),
            'jumlah_berkas'       => $jumlahBerkas,
            'satuan_arsip'        => $satuanArsip,
            'aktif_tahun'         => $aktifTahun,
            'inaktif_tahun'       => $inaktifTahun,
            'keterangan_jra'      => $keteranganJRA,
            'aktif_sampai'        => $perhitungan['aktif_sampai'],
            'inaktif_sampai'      => $perhitungan['inaktif_sampai'],
            'status_arsip'        => $perhitungan['status_arsip'],
            'status_pindah'       => 'BELUM',
            'nomor_box'           => (string) $nomorBox,
            'nomor_rak'           => (string) $nomorRak,
            'lokasi_arsip'        => $lokasiArsip,
            'tingkat_perkembangan'=> $tingkatPerkembangan,
            'link_foto'           => $linkFoto,
            'media_arsip'         => $mediaArsip,
            'keterangan'          => $kondisiFisik,
            'tanggal_masuk'       => now()->format('Y-m-d'),
            'created_by'          => Auth::id(),
        ];

        Log::info('Data siap simpan:', $data);
        $this->importedRows++;
        return new Arsip($data);
    }

    // ========================
    // METHOD PEMBANTU (tetap sama)
    // ========================

    private function parseRetensiString($value)
    {
        if ($value === null) return null;
        $raw = (string) $value;
        $raw = str_replace(["\xc2\xa0", "<br>", "\n", "\r"], ' ', $raw);
        $raw = preg_replace('/\s+/', ' ', trim($raw));
        if ($raw === '') return null;
        if (ctype_digit($raw)) return $raw . ' TAHUN';
        if (preg_match('/tahun/i', $raw)) return preg_replace('/tahun/i', 'TAHUN', $raw);
        if (preg_match('/^(\d+)\s*$/i', $raw, $m)) return $m[1] . ' TAHUN';
        return $raw;
    }

    private function extractNumberFromText($text)
    {
        if (!$text) return null;
        if (preg_match('/\d+/', $text, $m)) return (int) $m[0];
        return null;
    }

    private function hitungRetensi($aktifTahunText, $inaktifTahunText, $keteranganJRA, $tanggalArsip, $tanggalReferensi = null)
    {
        $result = ['aktif_sampai' => null, 'inaktif_sampai' => null, 'status_arsip' => 'AKTIF'];
        if (!$aktifTahunText || !$inaktifTahunText) return $result;

        $aktifTahun = $this->extractNumberFromText($aktifTahunText);
        $inaktifTahun = $this->extractNumberFromText($inaktifTahunText);
        if (!$aktifTahun || !$inaktifTahun) return $result;

        $tanggalDasar = Carbon::parse($tanggalArsip);
        if (stripos($aktifTahunText, 'SETELAH') !== false || stripos($inaktifTahunText, 'SETELAH') !== false) {
            if ($tanggalReferensi) $tanggalDasar = Carbon::parse($tanggalReferensi);
        }

        $aktifSampai = $tanggalDasar->copy()->addYears($aktifTahun);
        $inaktifSampai = $aktifSampai->copy()->addYears($inaktifTahun);
        $musnahSampai = $inaktifSampai->copy()->addYears(1);
        $sekarang = Carbon::now();

        if ($keteranganJRA === 'PERMANEN') {
            $result['status_arsip'] = 'PERMANEN';
        } elseif ($keteranganJRA === 'MUSNAH') {
            if ($sekarang <= $aktifSampai) $result['status_arsip'] = 'AKTIF';
            elseif ($sekarang <= $inaktifSampai) $result['status_arsip'] = 'INAKTIF';
            elseif ($sekarang <= $musnahSampai) $result['status_arsip'] = 'HABIS_RETENSI';
            else $result['status_arsip'] = 'HABIS_RETENSI';
        } else {
            $result['status_arsip'] = ($sekarang <= $aktifSampai) ? 'AKTIF' : 'INAKTIF';
        }

        $result['aktif_sampai'] = $aktifSampai->format('Y-m-d');
        $result['inaktif_sampai'] = $inaktifSampai->format('Y-m-d');
        return $result;
    }
}