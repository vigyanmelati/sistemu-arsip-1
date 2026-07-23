<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\KodeKlasifikasi;
use App\Models\SubBagian;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\ValidationException;
use PhpOffice\PhpSpreadsheet\Reader\Exception as SpreadsheetException;
use App\Models\MasterRak;
use App\Models\MasterBox;

class ArsipImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    SkipsEmptyRows
{
    public array $errors = [];
    use SkipsFailures;
    public int $importedRows = 0;

    // Jumlah baris yang di-skip karena terdeteksi duplikat (dalam file / database)
    public int $duplicateRows = 0;

    // Menyimpan key duplikat yang sudah dilihat saat fase VALIDASI (validateExcel)
    // format: [key => nomor_baris]
    protected array $seenKeysValidation = [];

    // Menyimpan key duplikat yang sudah dilihat saat fase IMPORT beneran (model())
    // dipisah dari $seenKeysValidation supaya tidak bentrok kalau instance dipakai ulang
    protected array $seenKeysImport = [];

    // Penghitung baris kasar untuk fase model() (mulai dari 2 karena baris 1 = header)
    protected int $currentRow = 1;

    public function rules(): array
    {
        return [
            'kode_klasifikasi' => [
                'required',
                function ($attribute, $value, $fail) {
                    $kode = \App\Models\KodeKlasifikasi::whereRaw('REPLACE(kode, " ", "") = ?', [str_replace(' ', '', strtoupper($value))])->first();

                    if (!$kode) {
                        $fail('Kode klasifikasi tidak ditemukan');
                    }
                }
            ],
            'uraian_arsip' => 'required',
            'tahun_arsip'  => 'required|numeric',

            // TANGGAL ARSIP (opsional, tapi kalau diisi harus bisa di-parse
            // dan tahunnya wajib sama dengan tahun_arsip. Pengecekan detail
            // dilakukan di validateRow() karena butuh akses ke field lain).
            'tanggal_arsip' => 'nullable',
        ];
    }

    public function isEmptyWhen(array $row): bool
    {
        return
            empty(trim($row['kode_klasifikasi'] ?? '')) &&
            empty(trim($row['jenis_arsip'] ?? '')) &&
            empty(trim($row['kurun_waktu'] ?? ''));
    }


    public function customValidationMessages()
    {
        return [
            'kode_klasifikasi.required' => 'Kode klasifikasi kosong',
            'uraian_arsip.required'     => 'Jenis arsip kosong',
            'tahun_arsip.required'      => 'Tahun arsip kosong',
        ];
    }

    public function headingRow(): int
    {
        return 1;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: BANGUN KEY UNIK UNTUK DETEKSI DUPLIKAT
    |--------------------------------------------------------------------------
    | Kombinasi field yang dianggap "sama" / duplikat:
    | kode_klasifikasi + sub_bagian + uraian_arsip (dinormalisasi) + tahun_arsip
    | + tanggal_arsip (kalau ada).
    | Sesuaikan kombinasi ini kalau definisi "duplikat" di bisnis kamu beda.
    |--------------------------------------------------------------------------
    */
    private function buildDuplicateKey(
        int $kodeId,
        int $subBagianId,
        string $uraianArsip,
        $tahunArsip,
        ?string $tanggalArsip
    ): string {
        $uraianNormalized = strtolower(trim(preg_replace('/\s+/', ' ', $uraianArsip)));

        return implode('|', [
            $kodeId,
            $subBagianId,
            $uraianNormalized,
            (string) $tahunArsip,
            $tanggalArsip ?? '',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: RESOLVE KODE KLASIFIKASI & SUB BAGIAN DARI SATU BARIS
    |--------------------------------------------------------------------------
    */
    private function resolveKode(string $kodeRaw): ?KodeKlasifikasi
    {
        $kodeInput = strtoupper(str_replace(' ', '', trim($kodeRaw)));

        if (!$kodeInput) {
            return null;
        }

        return KodeKlasifikasi::whereRaw('REPLACE(kode, " ", "") = ?', [$kodeInput])->first();
    }

    private function resolveSubBagian(?string $namaSubBagian): ?SubBagian
    {
        $user = Auth::user();
        $subBagian = $user?->subBagian;

        if (!$subBagian && $namaSubBagian) {
            $subBagian = SubBagian::where('nama_sub_bagian', trim($namaSubBagian))->first();
        }

        return $subBagian;
    }

    private function resolveTanggalKey($tanggalArsipRaw): ?string
    {
        $tanggal = $this->parseTanggalArsipFleksibel($tanggalArsipRaw);

        return $tanggal ? $tanggal->format('Y-m-d') : null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER: PARSING TANGGAL ARSIP YANG "TAHAN BANTING"
    |--------------------------------------------------------------------------
    | Kolom tanggal di file Excel bisa datang dalam beberapa bentuk berbeda
    | tergantung cara user mengisi/menyimpan filenya:
    |
    |   1) Excel serial number (mis. 45678) -> ini yang paling sering bikin
    |      error kalau langsung di-Carbon::parse() begitu saja, karena bukan
    |      string tanggal, tapi angka murni hasil format cell "Date" di Excel.
    |   2) Serial number yang "kebungkus" jadi string murni angka, mis. "45678"
    |      (kadang terjadi tergantung driver Excel yang dipakai).
    |   3) String dengan berbagai format umum: 17/08/1945, 17-08-1945,
    |      1945-08-17, 17 Agustus 1945 (nama bulan Indonesia), dst.
    |   4) String dengan whitespace "aneh" seperti non-breaking space (biasa
    |      muncul kalau tanggal di-copy-paste dari sumber lain ke Excel).
    |
    | Strategi:
    |   - Kalau numeric (angka asli / angka dibungkus string) -> convert
    |     pakai PhpSpreadsheet Date::excelToDateTimeObject().
    |   - Kalau string -> normalisasi whitespace & nama bulan Indonesia dulu,
    |     lalu coba beberapa format eksplisit satu-satu (supaya hasilnya
    |     konsisten & bisa diprediksi, tidak salah tebak).
    |   - Baru fallback ke Carbon::parse() otomatis kalau semua format
    |     eksplisit gagal.
    |   - Kalau tetap tidak bisa di-parse, return null (bukan exception),
    |     supaya caller bisa menangani sebagai error baris biasa, bukan
    |     bikin seluruh proses import crash.
    |
    | Dipakai di validateRow() DAN model() supaya hasil parsing selalu
    | konsisten antara fase validasi dan fase import beneran.
    |--------------------------------------------------------------------------
    */
  private function parseTanggalArsipFleksibel($value): ?Carbon
{
    if ($value === null || $value === '') {
        Log::info('Tanggal raw: NULL atau kosong');
        return null;
    }

    Log::info('Tanggal raw: ' . print_r($value, true));

    // 0. Excel Date Object
    if ($value instanceof \DateTimeInterface) {
        $carbon = Carbon::instance($value)->startOfDay();
        Log::info('Tanggal parsed (DateTimeInterface): ' . $carbon->toDateString());
        return $carbon;
    }

    // 1) Excel serial date (angka asli)
    if (is_numeric($value)) {
        try {
            $carbon = Carbon::instance(Date::excelToDateTimeObject((float) $value))->startOfDay();
            Log::info('Tanggal parsed (serial number): ' . $carbon->toDateString());
            return $carbon;
        } catch (\Throwable $e) {
            Log::warning('Gagal konversi serial number: ' . $e->getMessage());
            // lanjut ke proses string
        }
    }

    // 2) Normalisasi whitespace aneh
    $raw = trim((string) $value);
    $raw = str_replace(["\xc2\xa0"], ' ', $raw);
    $raw = preg_replace('/\s+/', ' ', $raw);

    if ($raw === '') {
        Log::info('Tanggal parsed: kosong setelah normalisasi');
        return null;
    }

    // 3) Serial number yang kebungkus string angka
    if (ctype_digit($raw)) {
        try {
            $carbon = Carbon::instance(Date::excelToDateTimeObject((float) $raw))->startOfDay();
            Log::info('Tanggal parsed (serial string): ' . $carbon->toDateString());
            return $carbon;
        } catch (\Throwable $e) {
            // lanjut
        }
    }

    // 4) Normalisasi nama bulan Indonesia -> Inggris
    $bulanIndo = [
        'JANUARI'   => 'January',
        'FEBRUARI'  => 'February',
        'MARET'     => 'March',
        'APRIL'     => 'April',
        'MEI'       => 'May',
        'JUNI'      => 'June',
        'JULI'      => 'July',
        'AGUSTUS'   => 'August',
        'SEPTEMBER' => 'September',
        'OKTOBER'   => 'October',
        'NOVEMBER'  => 'November',
        'DESEMBER'  => 'December',
    ];

    $upper = strtoupper($raw);
    foreach ($bulanIndo as $indo => $eng) {
        if (str_contains($upper, $indo)) {
            $raw = str_ireplace($indo, $eng, $raw);
            break;
        }
    }

    // 5) Coba format eksplisit satu-satu
    $formats = [
        'd/m/Y', 'd-m-Y', 'd.m.Y',
        'Y-m-d', 'Y/m/d',
        'd/m/y', 'd-m-y',
        'd F Y', 'j F Y', 'd M Y', 'j M Y',
        'm/d/Y', 'm-d-Y',
        'Y-m-d H:i:s', 'd/m/Y H:i:s', 'd-m-Y H:i:s',
    ];

    foreach ($formats as $format) {
        try {
            $date = Carbon::createFromFormat('!' . $format, $raw);
            if ($date !== false) {
                $carbon = $date->startOfDay();
                Log::info("Tanggal parsed (format '{$format}'): " . $carbon->toDateString());
                return $carbon;
            }
        } catch (\Throwable $e) {
            continue;
        }
    }

    // 6) Fallback terakhir: biarkan Carbon menebak
    try {
        $carbon = Carbon::parse($raw)->startOfDay();
        Log::info('Tanggal parsed (fallback Carbon::parse): ' . $carbon->toDateString());
        return $carbon;
    } catch (\Throwable $e) {
        Log::warning('Gagal parse tanggal: ' . $e->getMessage());
        return null;
    }
}

    /*
    |--------------------------------------------------------------------------
    | HELPER: CEK APAKAH KEY SUDAH ADA DI DATABASE
    |--------------------------------------------------------------------------
    */
    private function existsInDatabase(
        int $kodeId,
        int $subBagianId,
        string $uraianArsip,
        $tahunArsip,
        ?string $tanggalKey
    ): bool {
        return Arsip::where('kode_klasifikasi_id', $kodeId)
            ->where('sub_bagian_id', $subBagianId)
            ->where('tahun_arsip', (string) $tahunArsip)
            ->whereRaw('LOWER(TRIM(uraian_arsip)) = ?', [strtolower(trim(preg_replace('/\s+/', ' ', $uraianArsip)))])
            ->when($tanggalKey, function ($query) use ($tanggalKey) {
                $query->whereDate('tanggal_arsip', $tanggalKey);
            })
            ->exists();
    }

    private function validateRow(array $row, int $rowNumber): void
    {

      $lokasiArsip = $this->normalizeLokasiArsip(
    $row['lokasi_arsip'] ?? ''
);

    $rakModel = null;

    // ============================
    // VALIDASI RAK
    // ============================

    if (!empty($row['nomor_rak'])) {

        $nomorRak = trim(
            $row['nomor_rak']
        );

        $rakModel = MasterRak::whereRaw(
                'LOWER(TRIM(nomor_rak)) = ?',
                [strtolower($nomorRak)]
            )
            ->where(
                'lokasi_arsip',
                $lokasiArsip
            )
            ->first();

        if (!$rakModel) {

            $this->errors[] =
                "Baris {$rowNumber}: Rak '{$nomorRak}' tidak ditemukan pada lokasi {$lokasiArsip}. Silakan periksa kembali Menu Manajemen Lokasi.";
        }
    }

    // ============================
    // VALIDASI BOX
    // ============================

    if (!empty($row['nomor_box'])) {

        $nomorBox = trim(
            $row['nomor_box']
        );

        // Kalau rak tidak ditemukan, tidak usah cek box
        if (!$rakModel) {
            return;
        }

        $boxModel = MasterBox::where(
                'rak_id',
                $rakModel->id
            )
            ->whereRaw(
                'LOWER(TRIM(nomor_box)) = ?',
                [strtolower($nomorBox)]
            )
            ->first();

        if (!$boxModel) {

            $this->errors[] =
                "Baris {$rowNumber}: Box '{$nomorBox}' tidak ditemukan pada Rak '{$rakModel->nomor_rak}'. Silakan periksa kembali Menu Manajemen Lokasi.";
        }
    }
    
        $requiredFields = [
            'kode_klasifikasi' => 'Kode Klasifikasi',
            'uraian_arsip'     => 'Uraian Arsip',
            'sub_bagian'      => 'Sub Bagian',
            'tahun_arsip'      => 'Tahun Arsip',
            'tanggal_arsip'    => 'Tanggal Arsip',
            'jumlah_berkas'    => 'Jumlah Berkas',
            'nomor_rak'    => 'Nomor Rak',
            'nomor_box'    => 'Nomor Box',
            'keterangan'    => 'Keterangan',
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

        $kode = null;

        if (!empty($row['kode_klasifikasi'])) {

            $kode = $this->resolveKode($row['kode_klasifikasi']);

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

        if (!empty($row['tahun_arsip'])) {

            if (!is_numeric($row['tahun_arsip'])) {
                $this->errors[] =
                    "Baris {$rowNumber}: Tahun arsip harus berupa angka.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TANGGAL ARSIP
        |--------------------------------------------------------------------------
        | Kolom ini wajib diisi (lihat $requiredFields di atas), tapi bisa
        | datang dalam banyak format dari Excel (serial number, d/m/Y,
        | Y-m-d, nama bulan Indonesia, dll) -> ditangani oleh
        | parseTanggalArsipFleksibel() supaya tidak error.
        |
        | Kalau berhasil di-parse, tahun pada tanggal_arsip WAJIB SAMA
        | dengan tahun_arsip.
        |--------------------------------------------------------------------------
        */

        $tanggalKey = null;

        if (!empty($row['tanggal_arsip'])) {

            $tanggal = $this->parseTanggalArsipFleksibel($row['tanggal_arsip']);

            if (!$tanggal) {

                $this->errors[] =
                    "Baris {$rowNumber}: Format Tanggal Arsip tidak dikenali/tidak bisa dibaca. Gunakan format seperti 12/01/2023, 2023-01-12, 12 Januari 2023, atau format cell Date bawaan Excel.";

            } else {

                $tanggalKey = $tanggal->format('Y-m-d');

                if (
                    !empty($row['tahun_arsip']) &&
                    is_numeric($row['tahun_arsip']) &&
                    $tanggal->year != (int) $row['tahun_arsip']
                ) {

                    $this->errors[] =
                        "Baris {$rowNumber}: Tahun arsip ({$row['tahun_arsip']}) harus sama dengan tahun pada Tanggal Arsip ({$tanggal->year}).";
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SATUAN ARSIP
        |--------------------------------------------------------------------------
        */

        if (!empty($row['jumlah_berkas'])) {

            $jumlah = strtoupper(
                trim((string)$row['jumlah_berkas'])
            );

            preg_match('/[A-Z]+/', $jumlah, $match);

            $satuan = $match[0] ?? '';

            $allowed = [
                'BENDEL',
                'LEMBAR',
            ];

            if (!in_array($satuan, $allowed)) {

                $this->errors[] =
                    "Baris {$rowNumber}: Satuan arsip hanya boleh BENDEL atau LEMBAR .";
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
| KLASIFIKASI KEAMANAN
|--------------------------------------------------------------------------
*/

if (!empty($row['klasifikasi_keamanan'])) {

    $value = strtoupper(trim($row['klasifikasi_keamanan']));

    $allowed = [
        'TERBATAS',
        'RAHASIA',
        'BIASA/TERBUKA',
    ];

    if (!in_array($value, $allowed)) {

        $this->errors[] =
            "Baris {$rowNumber}: Klasifikasi keamanan hanya boleh TERBATAS, RAHASIA atau BIASA/TERBUKA.";
    }
}

/*
|--------------------------------------------------------------------------
| KETERANGAN (MEDIA/KONDISI)
|--------------------------------------------------------------------------
*/

if (!empty($row['keterangan'])) {

    $parts = explode('/', strtoupper(trim($row['keterangan'])));

    if (count($parts) < 2) {

        $this->errors[] =
            "Baris {$rowNumber}: Format keterangan harus MEDIA/KONDISI.";
    } else {

        $media = trim($parts[0]);
        $kondisi = trim($parts[1]);

        $allowedMedia = [
            'TEKSTUAL',
            'DIGITAL',
        ];

        $allowedKondisi = [
            'BAIK',
            'RUSAK',
            'HILANG',
        ];

        if (!in_array($media, $allowedMedia)) {

            $this->errors[] =
                "Baris {$rowNumber}: Media arsip hanya boleh TEKSTUAL atau DIGITAL.";
        }

        if (!in_array($kondisi, $allowedKondisi)) {

            $this->errors[] =
                "Baris {$rowNumber}: Kondisi arsip hanya boleh BAIK, RUSAK atau HILANG.";
        }
    }
}

/*
|--------------------------------------------------------------------------
| LOKASI ARSIP
|--------------------------------------------------------------------------
*/

if (!empty($row['lokasi_arsip'])) {

    $lokasiArsip = $this->normalizeLokasiArsip(
        $row['lokasi_arsip']
    );

    $allowed = [
        'RECORD_CENTER_INAKTIF',
        'RECORD_CENTER_PERMANEN',
    ];

    if (!in_array($lokasiArsip, $allowed)) {

        $this->errors[] =
            "Baris {$rowNumber}: Lokasi arsip hanya boleh RECORD CENTER INAKTIF atau RECORD CENTER PERMANEN.";
    }
}
        /*
        |--------------------------------------------------------------------------
        | KETERANGAN JRA
        |--------------------------------------------------------------------------
        */

        if (!empty($row['keterangan_jra'])) {

            $allowed = [
                'MUSNAH',
                'PERMANEN',
                'BELUM DITENTUKAN'
            ];

            $value = strtoupper(
                trim($row['keterangan_jra'])
            );

            if (!in_array($value, $allowed)) {

                $this->errors[] =
                    "Baris {$rowNumber}: Keterangan JRA tidak valid.";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT (ANTAR BARIS DALAM FILE & DI DATABASE)
        |--------------------------------------------------------------------------
        | Hanya dicek kalau field kunci (kode, uraian, tahun) sudah lolos & sub
        | bagian bisa ditemukan, supaya tidak menumpuk error yang sama dengan
        | validasi wajib diisi di atas.
        |--------------------------------------------------------------------------
        */

        if (
            $kode &&
            !empty($row['uraian_arsip']) &&
            !empty($row['tahun_arsip'])
        ) {

            $subBagian = $this->resolveSubBagian($row['sub_bagian'] ?? null);

            if (!$subBagian) {
                $this->errors[] =
                    "Baris {$rowNumber}: Sub bagian '{$row['sub_bagian']}' tidak ditemukan, duplikat tidak bisa dicek.";
            } else {

                $uraianArsip = trim(preg_replace('/\s+/', ' ', $row['uraian_arsip']));

                $key = $this->buildDuplicateKey(
                    $kode->id,
                    $subBagian->id,
                    $uraianArsip,
                    $row['tahun_arsip'],
                    $tanggalKey
                );

                // 1) Cek duplikat ANTAR BARIS dalam file Excel
                if (isset($this->seenKeysValidation[$key])) {

                    $baseline = $this->seenKeysValidation[$key];

                    $this->errors[] =
                        "Baris {$rowNumber}: Data duplikat dengan baris {$baseline} pada file yang sama (kode klasifikasi, uraian arsip, tahun & sub bagian sama).";

                } else {

                    $this->seenKeysValidation[$key] = $rowNumber;

                    // 2) Cek duplikat di DATABASE
                    if ($this->existsInDatabase($kode->id, $subBagian->id, $uraianArsip, $row['tahun_arsip'], $tanggalKey)) {

                        $this->errors[] =
                            "Baris {$rowNumber}: Data arsip sudah ada di database (kode klasifikasi, uraian arsip, tahun & sub bagian sama).";
                    }
                }
            }
        }
    }

    public function validateExcel(array $rows): void
    {
        $rowNumber = 2;

        // Reset supaya validateExcel bisa dipanggil ulang tanpa membawa state lama
        $this->seenKeysValidation = [];

        foreach ($rows as $row) {

            $this->validateRow(
                $row,
                $rowNumber
            );

            $rowNumber++;
        }
    }

    /**
     * Helper untuk dipakai di controller:
     * cek dulu apakah ada error (termasuk duplikat) sebelum menjalankan Excel::import().
     *
     * Contoh pemakaian di controller:
     *
     *   $rows = Excel::toArray(new ArsipImport, $file)[0];
     *   $import = new ArsipImport();
     *   $import->validateExcel($rows);
     *
     *   if ($import->hasErrors()) {
     *       return back()->withErrors($import->errors);
     *       // import TIDAK dilanjutkan ke database
     *   }
     *
     *   Excel::import($import, $file);
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function model(array $row)
    {
        
        $this->currentRow++;

        if (
            empty(trim($row['kode_klasifikasi'] ?? '')) &&
            empty(trim($row['uraian_arsip'] ?? '')) &&
            empty(trim($row['tahun_arsip'] ?? ''))
        ) {
            return null;
        }
        try {
            Log::info('ROW:', $row);

            // =======================
            // KODE KLASIFIKASI
            // =======================
            $kodeInput = strtoupper(trim($row['kode_klasifikasi'] ?? ''));
            $kodeInput = str_replace(' ', '', $kodeInput);

            if (!$kodeInput) {
                return null;
            }

            $kode = KodeKlasifikasi::whereRaw('REPLACE(kode, " ", "") = ?', [$kodeInput])->first();

            if (!$kode) {
               return null;
            }

            // =======================
            // USER & SUB BAGIAN
            // =======================
            $user = Auth::user();
            $namaSubBagian = trim($row['sub_bagian'] ?? '');
            $subBagian = $user->subBagian;

            if (!$subBagian && $namaSubBagian) {
                $subBagian = SubBagian::where('nama_sub_bagian', $namaSubBagian)->first();
            }

            if (!$subBagian) {
                Log::warning('Sub bagian tidak ditemukan: ' . $namaSubBagian);
                return null;
            }

            // =======================
            // MAPPING EXCEL
            // =======================
            $uraianArsip = trim(preg_replace('/\s+/', ' ', $row['uraian_arsip'] ?? ''));
            if (empty($uraianArsip)) {
                Log::warning('Uraian arsip kosong');
                return null;
            }

            $tingkatPerkembangan = strtoupper(trim($row['tingkat_perkembangan'] ?? 'ASLI'));

            $allowedTingkatPerkembangan = [
                'ASLI',
                'COPY',
                'SALINAN',
            ];

            if (!in_array($tingkatPerkembangan, $allowedTingkatPerkembangan)) {
                $tingkatPerkembangan = 'ASLI';
            }

            $tahunArsip = $row['tahun_arsip'] ?? date('Y');

            // =======================
            // TANGGAL ARSIP
            // =======================
            // Mendukung berbagai format tanggal Excel (serial number, d/m/Y,
            // Y-m-d, nama bulan Indonesia, dll) lewat parseTanggalArsipFleksibel().
            // Ini adalah safety net terakhir: kalau formatnya tidak dikenali,
            // atau tahunnya tidak cocok dengan tahun_arsip, baris di-skip
            // (tidak throw exception yang bisa menghentikan seluruh import).
            if (!empty($row['tanggal_arsip'])) {

                $tanggalArsip = $this->parseTanggalArsipFleksibel($row['tanggal_arsip']);

                if (!$tanggalArsip) {
                    $pesan = "Baris {$this->currentRow}: Format Tanggal Arsip '{$row['tanggal_arsip']}' tidak dikenali, data tidak diimport.";
                    $this->errors[] = $pesan;
                    Log::warning($pesan);
                    return null;
                }

                if ($tanggalArsip->year != (int) $tahunArsip) {
                    $pesan = "Baris {$this->currentRow}: Tahun arsip ({$tahunArsip}) tidak sama dengan tahun pada Tanggal Arsip ({$tanggalArsip->year}), data tidak diimport.";
                    $this->errors[] = $pesan;
                    Log::warning($pesan);
                    return null;
                }

            } else {
                $tanggalArsip = Carbon::createFromDate((int) $tahunArsip, 1, 1);
            }

            // =======================
// LOKASI ARSIP
// =======================

$lokasiArsip = $this->normalizeLokasiArsip(
    $row['lokasi_arsip'] ?? ''
);

// =======================
// NOMOR RAK & BOX
// =======================
// =======================
// NOMOR RAK
// =======================

$rak = MasterRak::whereRaw(
        'LOWER(TRIM(nomor_rak)) = ?',
        [strtolower(trim($row['nomor_rak']))]
    )
    ->where(
        'lokasi_arsip',
        $lokasiArsip
    )
    ->first();

if (!$rak) {

    $this->errors[] =
        "Baris {$this->currentRow}: Rak '{$row['nomor_rak']}' tidak ditemukan pada lokasi {$lokasiArsip}.";

    return null;
}


// =======================
// NOMOR BOX
// =======================

$box = MasterBox::where(
        'rak_id',
        $rak->id
    )
    ->whereRaw(
        'LOWER(TRIM(nomor_box)) = ?',
        [strtolower(trim($row['nomor_box']))]
    )
    ->first();

if (!$box) {

    $this->errors[] =
        "Baris {$this->currentRow}: Box '{$row['nomor_box']}' tidak ditemukan pada Rak '{$rak->nomor_rak}'.";

    return null;
}

$rakId = $rak->id;
$boxId = $box->id;

            // $nomorRak = $row['nomor_rak'] ?? null;
            // $nomorBox = $row['nomor_box'] ?? null;

            // =======================
            // CEK DUPLIKAT (SAFETY NET SAAT IMPORT BENERAN)
            // =======================
            // Catatan: pengecekan utama sebaiknya sudah dilakukan lewat
            // validateExcel() SEBELUM Excel::import() dipanggil, supaya kalau
            // ada duplikat import bisa dibatalkan total sebelum menyentuh
            // database. Blok ini hanya jaring pengaman terakhir: kalau tetap
            // ketemu duplikat di sini, baris ini di-skip (tidak di-insert),
            // tapi baris-baris lain yang valid tetap lanjut diproses karena
            // commit dilakukan per baris.
            $tanggalKeyImport = $tanggalArsip->format('Y-m-d');

            $duplicateKey = $this->buildDuplicateKey(
                $kode->id,
                $subBagian->id,
                $uraianArsip,
                $tahunArsip,
                $tanggalKeyImport
            );

            if (isset($this->seenKeysImport[$duplicateKey])) {
                $pesan = "Baris {$this->currentRow}: Data duplikat dengan baris {$this->seenKeysImport[$duplicateKey]} dalam file yang sama, data tidak diimport.";
                $this->errors[] = $pesan;
                $this->duplicateRows++;
                Log::warning($pesan);
                return null;
            }

            $this->seenKeysImport[$duplicateKey] = $this->currentRow;

            if ($this->existsInDatabase($kode->id, $subBagian->id, $uraianArsip, $tahunArsip, $tanggalKeyImport)) {
                $pesan = "Baris {$this->currentRow}: Data arsip sudah ada di database, data tidak diimport.";
                $this->errors[] = $pesan;
                $this->duplicateRows++;
                Log::warning($pesan);
                return null;
            }

            // =======================
            // JUMLAH BERKAS
            // =======================
            $jumlahRaw = strtoupper(trim($row['jumlah_berkas'] ?? '1 BERKAS'));
            preg_match('/\d+/', $jumlahRaw, $angka);
            $jumlahBerkas = isset($angka[0]) ? (int) $angka[0] : 1;
            preg_match('/[A-Z]+/', $jumlahRaw, $satuan);
            $satuanArsip = isset($satuan[0]) ? $satuan[0] : 'BERKAS';

            // =======================
            // KETERANGAN (MEDIA / KONDISI)
            // =======================
            $keteranganRaw = strtoupper(trim($row['keterangan'] ?? 'TEKSTUAL/BAIK'));
            $mediaArsip = 'TEKSTUAL';
            $kondisiFisik = 'BAIK';

            if ($keteranganRaw) {
                $parts = array_map('trim', explode('/', $keteranganRaw));
                if (count($parts) >= 2) {
                    $mediaArsip = $parts[0];
                    $kondisiFisik = $parts[1];
                }
            }

            // =======================
            // RETENSI
            // =======================
            $allowedJRA = ['MUSNAH', 'PERMANEN', 'BELUM DITENTUKAN'];
            $keteranganJRA = strtoupper(trim($row['keterangan_jra'] ?? ''));
            if (!in_array($keteranganJRA, $allowedJRA)) {
                $keteranganJRA = 'MUSNAH';
            }

            $aktifText = $this->parseRetensi($row['aktif_tahun'] ?? null);
            $inaktifText = $this->parseRetensi($row['inaktif_tahun'] ?? null);

            $aktifTahun = $this->ambilAngka($aktifText);
            $inaktifTahun = $this->ambilAngka($inaktifText);

            $isAfterCondition = str_contains($aktifText, 'SETELAH');

            if ($isAfterCondition) {
                $aktifSampai = null;
                $inaktifSampai = null;
            } else {
                $aktifSampai = $aktifTahun ? (clone $tanggalArsip)->addYears($aktifTahun) : null;
                $inaktifSampai = ($aktifSampai && $inaktifTahun)
                    ? (clone $aktifSampai)->addYears($inaktifTahun)
                    : null;
            }
            $inaktifSampai = ($aktifSampai && $inaktifTahun) ? (clone $aktifSampai)->addYears($inaktifTahun) : null;

            // Status
            $now = now();

            if ($keteranganJRA === 'PERMANEN') {
                $status = 'PERMANEN';
            } elseif ($isAfterCondition) {
                $status = 'AKTIF';
            } elseif ($aktifSampai && $now <= $aktifSampai) {
                $status = 'AKTIF';
            } elseif ($inaktifSampai && $now <= $inaktifSampai) {
                $status = 'INAKTIF';
            } elseif ($inaktifSampai) {
                $status = 'HABIS_RETENSI';
            } else {
                $status = 'AKTIF';
            }
            $linkFoto = trim($row['link_foto'] ?? '');

            $klasifikasiRaw = strtoupper(
                str_replace(
                    ' ',
                    '',
                    trim($row['klasifikasi_keamanan'] ?? '')
                )
            );


            $mapKlasifikasi = [

                'BIASA/TERBUKA' => 'Biasa/Terbuka',
                'TERBATAS'      => 'Terbatas',
                'RAHASIA'       => 'Rahasia',

            ];


            $klasifikasiKeamanan =
                $mapKlasifikasi[$klasifikasiRaw]
                ?? 'Biasa/Terbuka';
            // =======================
            // SIMPAN DATA - PAKAI DB TRANSACTION
            // =======================
            DB::beginTransaction();

            try {
                $arsip = Arsip::create([
                    'kode_klasifikasi_id' => $kode->id,
                    'uraian_arsip' => $uraianArsip,
                    'sub_bagian_id' => $subBagian->id,
                    'tahun_arsip' => (string) $tahunArsip,
                    'tanggal_arsip' => $tanggalArsip->format('Y-m-d'),
                    'jumlah_berkas' => $jumlahBerkas,
                    'satuan_arsip' => $satuanArsip,
                    'aktif_tahun' => $aktifText,
                    'inaktif_tahun' => $inaktifText,
                    'keterangan_jra' => $keteranganJRA,
                    'aktif_sampai' => $aktifSampai ? $aktifSampai->format('Y-m-d') : null,
                    'inaktif_sampai' => $inaktifSampai ? $inaktifSampai->format('Y-m-d') : null,
                    'status_arsip' => $status,
                    'tingkat_perkembangan' => $tingkatPerkembangan,
                    'status_pindah' => 'LANGSUNG',
                    'link_foto' => $linkFoto ?: null,
                    'rak_id' => $rakId,

                    'box_id' => $boxId,

                    'lokasi_arsip' => $lokasiArsip,

                    'klasifikasi_keamanan' => $klasifikasiKeamanan,
                    'media_arsip' => $mediaArsip,
                    'keterangan' => $kondisiFisik,
                    'tanggal_masuk' => now()->format('Y-m-d'),
                    'created_by' => Auth::id(),
                ]);
                $this->importedRows++;
                DB::commit();
                Log::info('SUCCESS: Data saved with ID: ' . $arsip->id);

                return $arsip;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('DB Error: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Exception $e) {

    $this->errors[] =
        "Baris {$this->currentRow}: " . $e->getMessage();

    Log::error(
        'Import Error: ' . $e->getMessage()
    );

    Log::error(
        $e->getTraceAsString()
    );

    return null;
}
    }

    private function parseRetensi($value)
    {
        if (!$value) return null;
        $text = strtoupper(trim((string) $value));
        $text = str_replace(["\n", "\r", "<br>"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }

    private function ambilAngka($text)
    {
        if (!$text) return null;
        if (preg_match('/\d+/', $text, $m)) {
            return (int) $m[0];
        }
        return null;
    }

   private function normalizeLokasiArsip($value): ?string
{
    if (empty($value)) {
        return null;
    }

    $value = strtoupper(trim($value));

    $mapping = [
        'RECORD CENTER INAKTIF' => 'RECORD_CENTER_INAKTIF',
        'RECORD_CENTER_INAKTIF' => 'RECORD_CENTER_INAKTIF',

        'RECORD CENTER PERMANEN' => 'RECORD_CENTER_PERMANEN',
        'RECORD_CENTER_PERMANEN' => 'RECORD_CENTER_PERMANEN',
    ];

    return $mapping[$value] ?? $value;
}
}
