<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bersihkan sisa kolom/FK dari percobaan migration sebelumnya yang gagal
        if (Schema::hasColumn('arsips', 'rak_id')) {
            Schema::table('arsips', function (Blueprint $table) {
                $table->dropForeign(['rak_id']);
                $table->dropColumn('rak_id');
            });
        }

        if (Schema::hasColumn('arsips', 'box_id')) {
            Schema::table('arsips', function (Blueprint $table) {
                $table->dropColumn('box_id');
            });
        }

        // Tambahkan ulang kolom rak_id dan box_id dengan FK yang benar
        Schema::table('arsips', function (Blueprint $table) {
            $table->foreignId('rak_id')->nullable()->after('nomor_rak')
                ->constrained('master_raks')->onDelete('set null');
            $table->foreignId('box_id')->nullable()->after('nomor_box')
                ->constrained('master_box')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropForeign(['rak_id']);
            $table->dropForeign(['box_id']);
            $table->dropColumn(['rak_id', 'box_id']);
        });
    }
};