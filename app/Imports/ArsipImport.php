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

class ArsipImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsOnFailure, 
    SkipsEmptyRows
{
    use SkipsFailures;

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

    public function model(array $row)
    {
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

            // if (!$kodeInput) return null;
            if (!$kodeInput) {
                return null;
            }

            $kode = KodeKlasifikasi::whereRaw('REPLACE(kode, " ", "") = ?', [$kodeInput])->first();

            // if (!$kode) {
            //     Log::warning('Kode tidak ditemukan: ' . $kodeInput);
            //     return null;
            // }
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

            // if (!$subBagian) {
            //     throw new \Exception('Sub bagian tidak ditemukan untuk user');
            // }
            // =======================
            // MAPPING EXCEL
            // =======================
            $uraianArsip = trim(preg_replace('/\s+/', ' ', $row['uraian_arsip'] ?? ''));
            if (empty($uraianArsip)) {
                Log::warning('Uraian arsip kosong');
                return null;
                //    throw new \Exception('Uraian Arsip Kosong');
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

            // $tanggalArsip = !empty($row['tanggal_arsip'])
            //     ? (is_numeric($row['tanggal_arsip'])
            //         ? Date::excelToDateTimeObject($row['tanggal_arsip'])
            //         : Carbon::parse($row['tanggal_arsip']))
            //     : Carbon::createFromDate((int)$tahunArsip, 1, 1);

            if (!empty($row['tanggal_arsip'])) {
                if (is_numeric($row['tanggal_arsip'])) {
                    $tanggalArsip = Carbon::instance(
                        Date::excelToDateTimeObject($row['tanggal_arsip'])
                    );
                } else {
                    $tanggalArsip = Carbon::parse($row['tanggal_arsip']);
                }
            } else {
                $tanggalArsip = Carbon::createFromDate((int)$tahunArsip, 1, 1);
            }

            $nomorRak = $row['nomor_rak'] ?? null;
            $nomorBox = $row['nomor_box'] ?? null;

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

            // Ambil angka dari retensi (jika ada)
            // $aktifText = $kode->aktif_tahun ?? null;
            // $inaktifText = $kode->inaktif_tahun ?? null;
            
            // $aktifTahun = $aktifText ? (int) preg_replace('/[^0-9]/', '', $aktifText) : null;
            // $inaktifTahun = $inaktifText ? (int) preg_replace('/[^0-9]/', '', $inaktifText) : null;
            $aktifText = $this->parseRetensi($row['aktif_tahun'] ?? null);
            $inaktifText = $this->parseRetensi($row['inaktif_tahun'] ?? null);

            $aktifTahun = $this->ambilAngka($aktifText);
            $inaktifTahun = $this->ambilAngka($inaktifText);

            // Hitung retensi
            // $aktifSampai = $aktifTahun ? (clone $tanggalArsip)->addYears($aktifTahun) : null;
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
            // if ($keteranganJRA === 'PERMANEN') {
            //     $status = 'PERMANEN';
            // } elseif ($aktifSampai && $now <= $aktifSampai) {
            //     $status = 'AKTIF';
            // } elseif ($inaktifSampai && $now <= $inaktifSampai) {
            //     $status = 'INAKTIF';
            // } elseif ($inaktifSampai) {
            //     $status = 'HABIS_RETENSI';
            // } else {
            //     $status = 'AKTIF';
            // }

            if ($keteranganJRA === 'PERMANEN') {
                $status = 'PERMANEN';
            } elseif ($isAfterCondition) {
                $status = 'AKTIF'; // 🔥 paksa aktif
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
                    'nomor_rak' => $nomorRak,
                    'nomor_box' => $nomorBox,
                    'media_arsip' => $mediaArsip,
                    'keterangan' => $kondisiFisik,
                    'tanggal_masuk' => now()->format('Y-m-d'),
                    'created_by' => Auth::id(),
                ]);
                
                DB::commit();
                Log::info('SUCCESS: Data saved with ID: ' . $arsip->id);
                
                return $arsip;
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('DB Error: ' . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Import Error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return null;
            //    throw new \Exception('Import Error: ' . $e->getMessage());
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
}