<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_box', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rak_id')
                ->constrained('master_raks')
                ->onDelete('cascade');
            $table->string('nomor_box', 50);
            $table->integer('kapasitas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['rak_id', 'nomor_box']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_box');
    }
};