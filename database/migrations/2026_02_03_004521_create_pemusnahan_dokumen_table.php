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
       Schema::create('pemusnahan_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemusnahan_id')
                ->constrained('pemusnahan')
                ->cascadeOnDelete();

            $table->string('jenis_dokumen');
            $table->string('nama_file');
            $table->string('path_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemusnahan_dokumen');
    }
};
