<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemusnahan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_usulan')->nullable();
            $table->year('tahun');
            $table->date('tanggal_usulan')->nullable();

            $table->enum('status', [
                'draft',       // masih kumpulin arsip
                'dikunci',     // sudah final
                'disidangkan', // sedang penilaian
                'selesai'      // sudah ada BA pemusnahan
            ])->default('draft');

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemusnahan');
    }
};
