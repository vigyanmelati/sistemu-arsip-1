<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Ubah tipe data int -> string
            $table->string('aktif_tahun')->default('')->change();
            $table->string('inaktif_tahun')->default('')->change();

            // Hapus kolom keterangan
            $table->dropColumn([
                'aktif_keterangan',
                'inaktif_keterangan'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Kembalikan ke int
            $table->integer('aktif_tahun')->default(0)->change();
            $table->integer('inaktif_tahun')->default(0)->change();

            // Tambahkan kembali kolom yang dihapus
            $table->string('aktif_keterangan')->nullable();
            $table->string('inaktif_keterangan')->nullable();
        });
    }
};
