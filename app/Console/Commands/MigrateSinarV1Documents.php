<?php

namespace App\Console\Commands;

use App\Models\SinarV1Document;
use App\Models\SubBagian;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MigrateSinarV1Documents extends Command
{
    protected $signature = 'sinar-v1:migrate {--commit : Simpan data; tanpa opsi ini hanya dry-run} {--limit= : Batasi jumlah data} {--skip-files : Jangan salin lampiran}';

    protected $description = 'Migrasikan kategori 1-11 dan Surat Keluar dari database SINAR V1';

    public function handle(): int
    {
        try {
            $source = DB::connection(config('sinar_v1.connection'));
            $query = $source->table('t_smasuk as s')
                ->leftJoin('t_instansi as i', 's.id_instansi', '=', 'i.id')
                ->leftJoin('t_bagian as b', 's.id_bagian', '=', 'b.id')
                ->whereIn('s.id_kategori', array_keys(SinarV1Document::CATEGORIES))
                ->select('s.*', 'i.nama_instansi', 'b.nama_bagian')
                ->orderBy('s.id');

            if ($this->option('limit')) {
                $query->limit((int) $this->option('limit'));
            }

            $total = (clone $query)->count();
        } catch (Throwable $exception) {
            $this->error('Koneksi atau struktur database SINAR V1 tidak dapat dibaca.');
            $this->line('Periksa SINAR_V1_DB_* pada .env, lalu jalankan php artisan config:clear.');
            $this->line('Detail: '.$exception->getMessage());

            return self::FAILURE;
        }
        $this->info(($this->option('commit') ? 'MIGRASI' : 'DRY-RUN').": {$total} dokumen ditemukan.");
        if (! $this->option('commit')) {
            return self::SUCCESS;
        }

        $stats = ['saved' => 0, 'files' => 0, 'missing' => 0];
        $query->chunkById(200, function ($rows) use (&$stats) {
            foreach ($rows as $row) {
                $existing = SinarV1Document::where('legacy_id', $row->id)
                    ->where('legacy_category_id', $row->id_kategori)
                    ->first();
                $file = $existing?->status_file === 'TERSEDIA'
                    && $existing->file_path
                    && Storage::disk('local')->exists($existing->file_path)
                    ? $existing->only(['status_file', 'file_path', 'file_name_original', 'file_mime', 'file_size', 'file_checksum'])
                    : $this->copyLegacyFile($row);
                $subBagian = $row->nama_bagian
                    ? SubBagian::whereRaw('LOWER(TRIM(nama_sub_bagian)) = ?', [Str::lower(trim($row->nama_bagian))])->first()
                    : null;

                SinarV1Document::updateOrCreate(
                    ['legacy_id' => $row->id, 'legacy_category_id' => $row->id_kategori],
                    [
                        'legacy_category_name' => SinarV1Document::CATEGORIES[$row->id_kategori],
                        'legacy_bagian_id' => $row->id_bagian ?? null,
                        'legacy_bagian_name' => $row->nama_bagian ?? null,
                        'legacy_user_id' => $row->id_user ?? null,
                        // Pertahankan pemetaan yang sudah dikoreksi petugas saat import ulang.
                        'sub_bagian_id' => $existing?->sub_bagian_id ?? $subBagian?->id,
                        'nomor_dokumen' => $row->no_surat ?? null,
                        'nomor_agenda' => $row->no_agenda ?? null,
                        'tanggal_dokumen' => $this->validDate($row->tgl_surat ?? null),
                        'tanggal_penyelesaian' => $this->validDate($row->tgl_buku ?? null),
                        'instansi_satker' => $row->nama_instansi ?? null,
                        'kepada' => $row->kepada ?? null,
                        'perihal' => $row->hal ?? null,
                        'catatan' => $row->catatan ?? null,
                        'legacy_created_at' => $row->created_at ?? null,
                        'legacy_updated_at' => $row->updated_at ?? null,
                    ] + $file
                );
                $stats['saved']++;
                $file['status_file'] === 'TERSEDIA' ? $stats['files']++ :
                    ($file['status_file'] === 'HILANG' ? $stats['missing']++ : null);
            }
        }, 's.id', 'id');

        $this->table(['Tersimpan', 'File tersalin', 'File hilang'], [array_values($stats)]);

        return self::SUCCESS;
    }

    private function copyLegacyFile(object $row): array
    {
        $name = trim((string) ($row->nama_file ?? ''));
        if ($name === '' || strtoupper($name) === 'N/A') {
            return ['status_file' => 'TANPA_FILE'];
        }
        if ($this->option('skip-files') || ! config('sinar_v1.copy_files')) {
            return ['status_file' => 'BELUM_DISALIN', 'file_name_original' => $name];
        }

        $root = config('sinar_v1.files_root');
        if (! $root) {
            return ['status_file' => 'HILANG', 'file_name_original' => $name];
        }
        $rootReal = realpath($root);
        $source = $rootReal ? realpath($rootReal.DIRECTORY_SEPARATOR.trim((string) ($row->path ?? ''), '/\\').DIRECTORY_SEPARATOR.$name) : false;
        if (! $source || ! str_starts_with(strtolower($source), strtolower($rootReal.DIRECTORY_SEPARATOR)) || ! is_file($source)) {
            return ['status_file' => 'HILANG', 'file_name_original' => $name];
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $path = 'sinar_v1/'.($row->id_kategori).'/'.Str::uuid().($extension ? ".{$extension}" : '');
        Storage::disk('local')->put($path, File::get($source));

        return [
            'status_file' => 'TERSEDIA', 'file_path' => $path, 'file_name_original' => $name,
            'file_mime' => File::mimeType($source), 'file_size' => File::size($source),
            'file_checksum' => hash_file('sha256', $source),
        ];
    }

    private function validDate(?string $value): ?string
    {
        if (! $value || $value === '0000-00-00') {
            return null;
        }
        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
