<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Aktif
            $table->string('aktif_keterangan')->nullable()->after('aktif_tahun');

            // Inaktif
            $table->string('inaktif_keterangan')->nullable()->after('inaktif_tahun');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn([
                'aktif_keterangan',
                'inaktif_keterangan',
            ]);
        });
    }
};
