<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pemusnahan', function (Blueprint $table) {
            $table->date('tanggal_usulan')
                  ->nullable()
                  ->after('tahun');
        });

        // 🔧 isi data lama supaya tidak NULL (optional tapi direkomendasikan)
        DB::table('pemusnahan')
            ->whereNull('tanggal_usulan')
            ->update([
                'tanggal_usulan' => now()->toDateString(),
            ]);
    }

    public function down(): void
    {
        Schema::table('pemusnahan', function (Blueprint $table) {
            $table->dropColumn('tanggal_usulan');
        });
    }
};
