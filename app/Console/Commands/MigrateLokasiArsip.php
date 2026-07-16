<?php

namespace App\Console\Commands;

use App\Models\Arsip;
use App\Models\MasterRak;
use App\Models\MasterBox;
use Illuminate\Console\Command;

class MigrateLokasiArsip extends Command
{
    protected $signature = 'lokasi:migrate';
    protected $description = 'Migrasi data nomor_rak dan nomor_box lama ke tabel master';

    public function handle()
    {
        $rows = Arsip::select('lokasi_arsip', 'nomor_rak', 'nomor_box')
            ->whereNotNull('lokasi_arsip')
            ->where('nomor_rak', '!=', '')
            ->distinct()
            ->get();

        $this->info("Ditemukan {$rows->count()} kombinasi unik lokasi/rak/box.");

        foreach ($rows as $row) {
            $rak = MasterRak::firstOrCreate([
                'lokasi_arsip' => $row->lokasi_arsip,
                'nomor_rak' => trim($row->nomor_rak),
            ]);

            $box = null;
            if (!empty(trim((string) $row->nomor_box))) {
                $box = MasterBox::firstOrCreate([
                    'rak_id' => $rak->id,
                    'nomor_box' => trim($row->nomor_box),
                ]);
            }

            Arsip::where('lokasi_arsip', $row->lokasi_arsip)
                ->where('nomor_rak', $row->nomor_rak)
                ->where('nomor_box', $row->nomor_box)
                ->update([
                    'rak_id' => $rak->id,
                    'box_id' => $box?->id,
                ]);
        }

        $this->info('Migrasi data lokasi arsip selesai.');
    }
}