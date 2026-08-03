<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->string('asal_data', 20)->default('SUBBAGIAN')->after('status_pindah');
            $table->string('keterangan_asal_data')->nullable()->after('asal_data');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn(['asal_data', 'keterangan_asal_data']);
        });
    }
};