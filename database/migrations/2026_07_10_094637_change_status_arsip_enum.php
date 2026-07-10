<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE arsips
            MODIFY status_arsip ENUM(
                'AKTIF',
                'INAKTIF',
                'USUL_MUSNAH',
                'PERMANEN',
                'MUSNAH',
                'NON_ARSIP',
                'HABIS_RETENSI',
                'DIAJUKAN_MUSNAH',
                'DISETUJUI_MUSNAH',
                'DIMUSNAHKAN'
            ) NOT NULL DEFAULT 'AKTIF'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE arsips
            MODIFY status_arsip ENUM(
                'AKTIF',
                'INAKTIF',
                'USUL_MUSNAH',
                'PERMANEN',
                'MUSNAH',
                'NON_ARSIP',
                'HABIS_RETENSI'
            ) NOT NULL DEFAULT 'AKTIF'
        ");
    }
};