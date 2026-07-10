<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemusnahan', function (Blueprint $table) {

            $table->string('file_persetujuan_anri')->nullable()->after('updated_at');
            $table->dateTime('tanggal_persetujuan_anri')->nullable()->after('file_persetujuan_anri');

            $table->string('file_persetujuan_kpu')->nullable()->after('tanggal_persetujuan_anri');
            $table->dateTime('tanggal_persetujuan_kpu')->nullable()->after('file_persetujuan_kpu');

            $table->string('file_berita_acara')->nullable()->after('tanggal_persetujuan_kpu');

            $table->string('file_sk_pemusnahan')->nullable()->after('file_berita_acara');

            $table->dateTime('tanggal_pemusnahan')->nullable()->after('file_sk_pemusnahan');
        });
    }

    public function down(): void
    {
        Schema::table('pemusnahan', function (Blueprint $table) {

            $table->dropColumn([
                'file_persetujuan_anri',
                'tanggal_persetujuan_anri',
                'file_persetujuan_kpu',
                'tanggal_persetujuan_kpu',
                'file_berita_acara',
                'file_sk_pemusnahan',
                'tanggal_pemusnahan',
            ]);
        });
    }
};