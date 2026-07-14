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
        Schema::table('arsips', function (Blueprint $table) {
            $table->enum('klasifikasi_keamanan', [
                'Biasa/Terbuka',
                'Terbatas',
                'Rahasia'
            ])
            ->default('Biasa/Terbuka')
            ->after('status_arsip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn('klasifikasi_keamanan');
        });
    }
};