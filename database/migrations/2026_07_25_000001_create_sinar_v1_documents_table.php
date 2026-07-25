<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinar_v1_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id');
            $table->unsignedTinyInteger('legacy_category_id');
            $table->string('legacy_category_name');
            $table->unsignedBigInteger('legacy_bagian_id')->nullable();
            $table->string('legacy_bagian_name')->nullable();
            $table->unsignedBigInteger('legacy_user_id')->nullable();
            $table->foreignId('sub_bagian_id')->nullable()->constrained('sub_bagians')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('arsip_id')->nullable()->constrained('arsips')->nullOnDelete();
            $table->string('nomor_dokumen')->nullable();
            $table->string('nomor_agenda')->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->date('tanggal_penyelesaian')->nullable();
            $table->string('instansi_satker')->nullable();
            $table->string('kepada')->nullable();
            $table->text('perihal')->nullable();
            $table->text('catatan')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name_original')->nullable();
            $table->string('file_mime')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_checksum', 64)->nullable();
            $table->string('status_file')->default('TANPA_FILE');
            $table->string('status_hardcopy')->default('BELUM_DIVERIFIKASI');
            $table->string('status_integrasi')->default('BELUM_DIPROSES');
            $table->string('lokasi_hardcopy')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('legacy_created_at')->nullable();
            $table->timestamp('legacy_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['legacy_id', 'legacy_category_id'], 'sinar_v1_legacy_unique');
            $table->index(['legacy_category_id', 'tanggal_dokumen']);
            $table->index(['sub_bagian_id', 'status_hardcopy']);
            $table->index('nomor_dokumen');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinar_v1_documents');
    }
};
