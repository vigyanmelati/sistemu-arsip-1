<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn(['nomor_rak', 'nomor_box']);
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->string('nomor_rak', 50)->nullable();
            $table->string('nomor_box', 50)->nullable();
        });
    }
};