<?php

namespace App\Imports;

use App\Models\Arsip;
use App\Models\SubBagian;
use App\Models\KodeKlasifikasi;
use App\Models\MasterRak;
use App\Models\MasterBox;
use App\Models\BeritaAcaraPindah;
use App\Models\BeritaAcaraDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

/**
 * Import arsip dari Excel.
 *
 * Pencocokan data lama vs baru dilakukan OTOMATIS berdasarkan kombinasi:
 * kode_klasifikasi + sub_bagian + tahun_arsip + uraian_arsip (persis sama).
 *   - Cocok  -> UPDATE (hanya field retensi: Aktif/Inaktif Tahun, Keterangan JRA,
 *               plus lokasi Rak/Box kalau diisi).
 *   - Tidak cocok -> CREATE arsip baru, asal_data = IMPORT.
 *
 * Setiap baris WAJIB memiliki Nomor Berita Acara -> dikaitkan ke BeritaAcaraPindah
 * (dibuat otomatis kalau belum ada) melalui BeritaAcaraDetail.
 *
 * ATURAN IMPORT (all-or-nothing):
 *   - Baris yang sama sekali kosong (tidak ada data apapun) dilewati, bukan dianggap error.
 *   - Semua baris yang berisi data divalidasi terlebih dahulu SEBELUM ada satupun
 *     yang disimpan ke database.
 *   - Jika ada satu saja baris yang gagal validasi, seluruh proses import DIBATALKAN
 *     (tidak ada data yang dibuat/diupdate sama sekali), dan seluruh pesan kekurangan
 *     data dari baris-baris yang bermasalah dikumpulkan di $failed.
 *   - Jika semua baris valid, baru diproses dan disimpan dalam satu transaksi.
 */
class ArsipImportMasuk implements ToCollection, WithHeadingRow, WithCalculatedFormulas, WithMultipleSheets
{
    public array $created = [];
    public array $updated = [];
    public array $failed = [];

    /**
     * Batasi import HANYA ke sheet pertama (mis. "Data Arsip").
     * Kalau file Excel punya sheet lain (kosong/tersembunyi/sheet lain
     * yang tidak relevan), sheet tersebut diabaikan sepenuhnya sehingga
     * tidak ikut memicu pesan error "Baris -: Tidak ada data..." yang
     * sebenarnya berasal dari sheet lain, bukan dari sheet data utama.
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function collection(Collection $rows)
    {
        // ===================== TAHAP 1: VALIDASI SEMUA BARIS =====================
        // Tidak ada satupun query INSERT/UPDATE yang dijalankan di tahap ini.
        $validRows = [];

        foreach ($rows as $index => $row) {
            $baris = $index + 2; // +2 karena heading row = baris 1

            if ($this->isRowEmpty($row)) {
                // Baris kosong (misal baris kosong di akhir sheet) dilewati saja,
                // tidak dianggap sebagai error.
                continue;
            }

            $errors = $this->validateRow($row);

            if (!empty($errors)) {
                $this->failed[] = [
                    'baris' => $baris,
                    'pesan' => implode(' ', $errors),
                ];
                continue;
            }

            $validRows[$baris] = $row;
        }

        // Kalau ada baris yang gagal validasi -> batalkan seluruh import.
        // Tidak ada satupun baris (walaupun valid) yang disimpan.
        if (!empty($this->failed)) {
            $this->created = [];
            $this->updated = [];
            return;
        }

        if (empty($validRows)) {
            // Tidak ada data sama sekali di file (semua baris kosong).
            $this->failed[] = [
                'baris' => '-',
                'pesan' => 'Tidak ada data yang bisa diimpor. Pastikan file diisi sesuai template.',
            ];
            return;
        }

        // ===================== TAHAP 2: PROSES SEMUA BARIS (SATU TRANSAKSI) =====================
        DB::beginTransaction();
        try {
            foreach ($validRows as $baris => $row) {
                $this->processRow($row, $baris);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            // Catat detail teknis untuk developer, tapi jangan tampilkan SQL mentah ke user.
            Log::error('Import Arsip gagal, seluruh proses dibatalkan.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->created = [];
            $this->updated = [];
            $this->failed[] = [
                'baris' => '-',
                'pesan' => 'Import dibatalkan karena terjadi kesalahan sistem saat menyimpan data. '
                    . 'Silakan hubungi administrator (detail teknis sudah dicatat di log).',
            ];
        }
    }

    /**
     * Cek apakah sebuah baris dianggap kosong.
     *
     * Sengaja HANYA mengecek kolom-kolom wajib (bukan seluruh kolom di baris),
     * karena baris "kosong" di Excel sering masih menyisakan format/formula
     * kosong di kolom lain (mis. kolom Tanggal Arsip / No Rak / No Box) akibat
     * template yang di-copy sampai baris ke bawah. Kalau semua kolom wajib
     * kosong, baris dianggap kosong dan dilewati, apapun isi kolom lainnya.
     */
    protected function isRowEmpty(Collection $row): bool
    {
        $kolomWajib = [
            'nomor_berita_acara',
            'kode_klasifikasi',
            'sub_bagian',
            'judul_arsip',
            'tahun_arsip',
        ];

        foreach ($kolomWajib as $kolom) {
            if ($this->cleanString($row[$kolom] ?? null) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Bersihkan string dari whitespace biasa maupun non-breaking space (\xC2\xA0)
     * yang sering nyangkut kalau data di-copy dari Word/PDF/aplikasi lain, karena
     * trim() bawaan PHP tidak menghapus non-breaking space.
     */
    protected function cleanString($value): string
    {
        if ($value === null) return '';
        $value = str_replace("\xC2\xA0", ' ', (string) $value);
        return trim($value);
    }

    /**
     * Validasi satu baris TANPA menyentuh database untuk write.
     * Mengembalikan array pesan error (bisa lebih dari satu, supaya user
     * langsung tahu semua yang kurang dari baris tersebut, bukan cuma satu-satu).
     */
    protected function validateRow(Collection $row): array
    {
        $errors = [];

        $nomorBap            = $this->cleanString($row['nomor_berita_acara'] ?? null);
        $kodeKlasifikasiText = $this->cleanString($row['kode_klasifikasi'] ?? null);
        $subBagianText       = $this->cleanString($row['sub_bagian'] ?? null);
        $uraian              = $this->cleanString($row['judul_arsip'] ?? null);
        $tahunArsip          = $this->cleanString($row['tahun_arsip'] ?? null);

        if ($nomorBap === '')            $errors[] = 'Nomor Berita Acara wajib diisi.';
        if ($kodeKlasifikasiText === '') $errors[] = 'Kode Klasifikasi wajib diisi.';
        if ($subBagianText === '')       $errors[] = 'Sub Bagian wajib diisi.';
        if ($uraian === '')              $errors[] = 'Judul Arsip wajib diisi.';
        if ($tahunArsip === '')          $errors[] = 'Tahun Arsip wajib diisi.';

        // Hanya cek ke master kalau teksnya memang diisi (hindari pesan ganda/rancu)
        if ($kodeKlasifikasiText !== '') {
            $ada = KodeKlasifikasi::where('kode', $kodeKlasifikasiText)->exists();
            if (!$ada) {
                $errors[] = "Kode Klasifikasi '{$kodeKlasifikasiText}' tidak ditemukan di master.";
            }
        }

        if ($subBagianText !== '') {
            $ada = SubBagian::whereRaw('LOWER(nama_sub_bagian) = ?', [strtolower($subBagianText)])->exists();
            if (!$ada) {
                $errors[] = "Sub Bagian '{$subBagianText}' tidak ditemukan di master.";
            }
        }

        // Tanggal Arsip sifatnya opsional, tapi kalau diisi harus valid.
        $tanggalArsipRaw = $this->cleanString($row['tanggal_arsip'] ?? null);
        if ($tanggalArsipRaw !== '') {
            try {
                $this->parseTanggal($tanggalArsipRaw);
            } catch (\Throwable $e) {
                $errors[] = "Tanggal Arsip '{$tanggalArsipRaw}' tidak valid. Gunakan format DD/MM/YYYY.";
            }
        }

        return $errors;
    }

    /**
     * Proses satu baris yang SUDAH pasti valid (sudah lolos validateRow()).
     * Tidak lagi membuka transaksi sendiri -> mengikuti transaksi dari collection().
     * Kalau ada exception di sini, biarkan naik ke collection() supaya seluruh
     * import ikut di-rollback.
     */
    protected function processRow(Collection $row, int $baris): void
    {
        $nomorBap            = $this->cleanString($row['nomor_berita_acara'] ?? null);
        $kodeKlasifikasiText = $this->cleanString($row['kode_klasifikasi'] ?? null);
        $subBagianText       = $this->cleanString($row['sub_bagian'] ?? null);
        $uraian              = $this->cleanString($row['judul_arsip'] ?? null);
        $tahunArsip          = $this->cleanString($row['tahun_arsip'] ?? null);

        $kodeKlasifikasi = KodeKlasifikasi::where('kode', $kodeKlasifikasiText)->first();
        $subBagian = SubBagian::whereRaw('LOWER(nama_sub_bagian) = ?', [strtolower($subBagianText)])->first();
        $lokasiArsip = match ($subBagian->id) {
            1 => 'RUANG_SUBBAGIAN_UMUM_LOGISTIK',
            2 => 'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM',
            3 => 'RUANG_SUBBAGIAN_KEUANGAN',
            5 => 'RUANG_SUBBAGIAN_TEKNIS',
            6 => 'RUANG_SUBBAGIAN_HUKUM',
            7 => 'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI',
            default => null,
        };
        // Pastikan BAP tersedia (dibuat otomatis kalau belum ada).
        // PENTING: sub_bagian_id dan created_by WAJIB diisi karena kolom-kolom ini
        // NOT NULL tanpa default di tabel berita_acara_pindah.
        $bap = BeritaAcaraPindah::firstOrCreate(
            ['nomor_bap' => $nomorBap],
            [
                'tanggal_bap'   => now(),
                'status'        => 'DIAJUKAN',
                'sub_bagian_id' => $subBagian->id,
                'created_by'    => auth()->id(),
            ]
        );

        // Cocokkan otomatis: kode klasifikasi + sub bagian + tahun + uraian (persis)
        $arsip = Arsip::where('kode_klasifikasi_id', $kodeKlasifikasi->id)
            ->where('sub_bagian_id', $subBagian->id)
            ->where('tahun_arsip', $tahunArsip)
            ->where('status_pindah', 'DIAJUKAN')
            ->whereRaw('LOWER(TRIM(uraian_arsip)) = ?', [strtolower(trim($uraian))])
            ->first();

        $aktifTahun   = $this->nullableValue($row['aktif_tahun'] ?? null);
        $inaktifTahun = $this->nullableValue($row['inaktif_tahun'] ?? null);
        $ketJra       = $this->nullableValue($row['keterangan_jra'] ?? null);

        // Resolve Rak & Box (opsional). Dibuat otomatis di master kalau belum ada.
        [$rakId, $boxId] = $this->resolveRakBox(
            $this->nullableValue($row['no_rak'] ?? null),
            $this->nullableValue($row['no_box'] ?? null)
        );
                if ($arsip) {
            // ===================== UPDATE (hanya field retensi & lokasi jika diisi) =====================
            if ($aktifTahun !== null)   $arsip->aktif_tahun = $aktifTahun;
            if ($inaktifTahun !== null) $arsip->inaktif_tahun = $inaktifTahun;
            if ($ketJra !== null)       $arsip->keterangan_jra = $ketJra;
            if ($rakId !== null)        $arsip->rak_id = $rakId;
            if ($boxId !== null)        $arsip->box_id = $boxId;

            $arsip->keterangan_asal_data = trim(
                ($arsip->keterangan_asal_data ? $arsip->keterangan_asal_data . ' | ' : '')
                . 'Update via Import (' . $nomorBap . ') pada ' . now()->format('d/m/Y H:i')
            );

           if (empty($arsip->lokasi_arsip)) {
                $arsip->lokasi_arsip = $lokasiArsip;
            }

            $arsip->skipHistory = true;
            $arsip->recalculateRetensi();
            $arsip->save();

            // Hubungkan ke BAP jika belum terhubung
            BeritaAcaraDetail::firstOrCreate([
                'arsip_id' => $arsip->id,
                'bap_id'   => $bap->id,
            ], [
                'status' => BeritaAcaraDetail::STATUS_DITERIMA ?? 'DITERIMA',
            ]);

            $this->updated[] = [
                'baris' => $baris,
                'id'    => $arsip->id,
                'judul' => $arsip->uraian_arsip,
            ];
        } else {
            // ===================== CREATE ARSIP BARU =====================
            $tanggalArsip = $this->nullableValue($row['tanggal_arsip'] ?? null);
            $jumlahBerkasRaw = $this->nullableValue($row['jumlah_berkas'] ?? null) ?? '1';

            $arsip = new Arsip();
            $arsip->kode_klasifikasi_id = $kodeKlasifikasi->id;
            $arsip->sub_bagian_id       = $subBagian->id;
            $arsip->uraian_arsip        = $uraian;
            $arsip->tahun_arsip         = $tahunArsip;
            $arsip->tanggal_arsip       = $this->parseTanggal($tanggalArsip);
            $arsip->tanggal_masuk       = now();
            $arsip->created_by          = auth()->id();

            // "Jumlah Berkas" bisa diisi bebas, mis. "1 Bendel" -> pecah jadi angka + satuan
            [$jumlah, $satuan] = $this->parseJumlahBerkas($jumlahBerkasRaw);
            $arsip->jumlah_berkas = $jumlah;
            $arsip->satuan_arsip  = $satuan;

            $arsip->aktif_tahun    = $aktifTahun;
            $arsip->inaktif_tahun  = $inaktifTahun;
            $arsip->keterangan_jra = $ketJra;
            $arsip->rak_id         = $rakId;
            $arsip->box_id         = $boxId;
            $arsip->lokasi_arsip = $lokasiArsip;
            $arsip->status_pindah  = 'DIAJUKAN';

            // Tandai asal data: hasil IMPORT, bukan input manual sub bagian
            $arsip->asal_data = 'IMPORT';
            $arsip->keterangan_asal_data = 'Dibuat via Import (' . $nomorBap . ') pada ' . now()->format('d/m/Y H:i');
                if (empty($arsip->lokasi_arsip)) {
    $arsip->lokasi_arsip = $lokasiArsip;
}
            $arsip->skipHistory = true;
            $arsip->recalculateRetensi();
            $arsip->save();

            BeritaAcaraDetail::create([
                'arsip_id' => $arsip->id,
                'bap_id'   => $bap->id,
                'status'   => BeritaAcaraDetail::STATUS_DITERIMA ?? 'DITERIMA',
            ]);

            $this->created[] = [
                'baris' => $baris,
                'id'    => $arsip->id,
                'judul' => $arsip->uraian_arsip,
            ];
        }
    }

    /**
     * Cari/otomatis buat Rak & Box di master berdasarkan nama/nomor yang diisi di excel.
     * Return [rak_id|null, box_id|null]
     */
    protected function resolveRakBox(?string $namaRak, ?string $namaBox): array
    {
        $rakId = null;
        $boxId = null;

        if ($namaRak !== null) {
            $rak = MasterRak::whereRaw('LOWER(nomor_rak) = ?', [strtolower($namaRak)])->first();
            if (!$rak) {
                $rak = MasterRak::create(['nomor_rak' => $namaRak]);
            }
            $rakId = $rak->id;
        }

        if ($namaBox !== null) {
            $query = MasterBox::whereRaw('LOWER(nomor_box) = ?', [strtolower($namaBox)]);
            if ($rakId !== null) {
                $query->where('rak_id', $rakId);
            }
            $box = $query->first();
            if (!$box) {
                $box = MasterBox::create([
                    'nomor_box' => $namaBox,
                    'rak_id'    => $rakId,
                ]);
            }
            $boxId = $box->id;
        }

        return [$rakId, $boxId];
    }

    /**
     * Pecah "1 Bendel" -> [1, "Bendel"]. Kalau cuma angka -> satuan default "Berkas".
     */
    protected function parseJumlahBerkas(string $raw): array
    {
        $raw = trim($raw);
        if (preg_match('/^(\d+)\s*(.*)$/', $raw, $m)) {
            $jumlah = (int) $m[1];
            $satuan = trim($m[2]) !== '' ? trim($m[2]) : 'Berkas';
            return [$jumlah, $satuan];
        }
        return [1, $raw !== '' ? $raw : 'Berkas'];
    }

    /**
     * Parse nilai tanggal dari Excel, yang bisa datang dalam beberapa bentuk:
     *  - Serial number Excel (mis. "44197") kalau kolom diformat sebagai
     *    angka/General, bukan Date -> perlu dikonversi khusus, tidak bisa
     *    langsung dibaca Carbon::parse().
     *  - Teks tanggal umum, mis. "17/08/2024" (DD/MM/YYYY).
     *  - Format lain yang masih bisa dikenali Carbon sebagai fallback.
     *
     * @throws \Exception kalau format tidak bisa dikenali sama sekali.
     */
    protected function parseTanggal($value): ?Carbon
    {
        $value = $this->cleanString($value);
        if ($value === '') return null;

        // Kasus serial number Excel, mis. 44197 -> 17/08/2024
        if (is_numeric($value)) {
            $dateTime = ExcelDate::excelToDateTimeObject((float) $value);
            return Carbon::instance($dateTime)->startOfDay();
        }

        // Coba format umum DD/MM/YYYY atau DD-MM-YYYY dulu, supaya tidak
        // salah tafsir dengan format bulan-duluan ala Amerika.
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            $parsed = Carbon::createFromFormat($format, $value);
            if ($parsed !== false) {
                return $parsed->startOfDay();
            }
        }

        // Fallback terakhir: biarkan Carbon menebak sendiri.
        return Carbon::parse($value);
    }

    protected function nullableValue($value)
    {
        $value = $this->cleanString($value);
        return $value === '' ? null : $value;
    }

    public function headingRow(): int
{
    return 4;
}
}
