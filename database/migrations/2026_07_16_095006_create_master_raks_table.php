<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_raks', function (Blueprint $table) {
            $table->id();
            $table->enum('lokasi_arsip', [
                'RUANG_SUBBAGIAN_UMUM_LOGISTIK',
                'RUANG_SUBBAGIAN_PARTISIPASI_MASYARAKAT_SDM',
                'RUANG_SUBBAGIAN_KEUANGAN',
                'RUANG_SUBBAGIAN_PERENCANAAN_DATA_INFORMASI',
                'RUANG_SUBBAGIAN_TEKNIS',
                'RUANG_SUBBAGIAN_HUKUM',
                'RECORD_CENTER_PERMANEN',
                'RECORD_CENTER_INAKTIF',
            ]);
            $table->string('nomor_rak', 50);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['lokasi_arsip', 'nomor_rak']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_raks');
    }
};