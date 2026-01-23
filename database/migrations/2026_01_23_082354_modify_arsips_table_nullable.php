<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Ubah kolom NOT NULL menjadi nullable dengan default
            $table->integer('aktif_tahun')->default(0)->change();
            $table->integer('inaktif_tahun')->default(0)->change();
            $table->string('nomor_rak', 50)->default('')->change();
            $table->string('nomor_box', 50)->default('')->change();
            $table->date('aktif_sampai')->nullable()->change();
            $table->date('inaktif_sampai')->nullable()->change();
            $table->enum('keterangan_jra', ['MUSNAH', 'PERMANEN'])->default('MUSNAH')->change();
        });
    }

    public function down()
    {
        // Revert changes
        Schema::table('arsips', function (Blueprint $table) {
            $table->integer('aktif_tahun')->default(null)->change();
            $table->integer('inaktif_tahun')->default(null)->change();
            $table->string('nomor_rak', 50)->change();
            $table->string('nomor_box', 50)->change();
            $table->date('aktif_sampai')->change();
            $table->date('inaktif_sampai')->change();
            $table->enum('keterangan_jra', ['MUSNAH', 'PERMANEN'])->change();
        });
    }
};