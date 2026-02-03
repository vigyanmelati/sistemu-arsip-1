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
        Schema::create('log_pemusnahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemusnahan_id')->constrained();
            $table->string('aksi'); // kunci, sidang, upload BA, dll
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_pemusnahan');
    }
};
