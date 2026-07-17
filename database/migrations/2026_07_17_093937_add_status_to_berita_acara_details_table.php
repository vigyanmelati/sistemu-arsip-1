<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE berita_acara_detail
            MODIFY COLUMN status
            ENUM('DRAFT','DIAJUKAN','DITERIMA','DITOLAK')
            NOT NULL DEFAULT 'DRAFT'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE berita_acara_detail
            MODIFY COLUMN status
            ENUM('DIAJUKAN','DITERIMA')
            NOT NULL DEFAULT 'DIAJUKAN'
        ");
    }
};