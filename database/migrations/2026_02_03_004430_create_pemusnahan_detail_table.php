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
        Schema::create('pemusnahan_detail', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pemusnahan_id');
            $table->unsignedInteger('arsip_id');

            $table->enum('keputusan', [
                'belum_dinilai',
                'disetujui',
                'tidak_disetujui',
                'ditunda'
            ])->default('belum_dinilai');

            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('pemusnahan_id')
                ->references('id')
                ->on('pemusnahan')
                ->onDelete('cascade');

            $table->foreign('arsip_id')
                ->references('id')
                ->on('arsip')
                ->onDelete('cascade');

            $table->unique(['pemusnahan_id', 'arsip_id']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemusnahan_detail');
    }
};
