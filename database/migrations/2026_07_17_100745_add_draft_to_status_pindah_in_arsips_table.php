<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah kolom menjadi string dulu agar bisa diubah
        DB::statement("ALTER TABLE arsips MODIFY COLUMN status_pindah VARCHAR(50) DEFAULT 'BELUM'");
        
        // Set nilai default untuk data yang sudah ada
        DB::statement("UPDATE arsips SET status_pindah = 'BELUM' WHERE status_pindah IS NULL OR status_pindah = ''");
        
        // Ubah kembali menjadi enum dengan nilai baru
        DB::statement("ALTER TABLE arsips MODIFY COLUMN status_pindah ENUM(
            'BELUM',
            'DRAFT',
            'DIAJUKAN',
            'DIPERBAIKI',
            'DITOLAK',
            'DIPINDAHKAN',
            'DITERIMA',
            'LANGSUNG',
            'NON_ARSIP'
        ) DEFAULT 'BELUM'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE arsips MODIFY COLUMN status_pindah VARCHAR(50) DEFAULT 'BELUM'");
        
        DB::statement("ALTER TABLE arsips MODIFY COLUMN status_pindah ENUM(
            'BELUM',
            'DRAFT',
            'DIAJUKAN',
            'DIPERBAIKI',
            'DITOLAK',
            'DIPINDAHKAN',
            'DITERIMA',
            'LANGSUNG',
            'NON_ARSIP'
        ) DEFAULT 'BELUM'");
    }
};