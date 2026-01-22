<?php
// database/migrations/[timestamp]_add_keterangan_and_no_sampul_to_arsips_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('arsips', function (Blueprint $table) {
            // Tambah kolom keterangan (kondisi fisik)
            $table->enum('keterangan', ['BAIK', 'RUSAK', 'HILANG'])
                  ->default('BAIK')
                  ->after('status_arsip')
                  ->comment('Kondisi fisik arsip');
            
            // Tambah kolom no_sampul
            $table->string('no_sampul', 100)
                  ->nullable()
                  ->after('nomor_box')
                  ->comment('Nomor sampul/cover arsip');
        });
    }

    public function down()
    {
        Schema::table('arsips', function (Blueprint $table) {
            $table->dropColumn(['keterangan', 'no_sampul']);
        });
    }
};