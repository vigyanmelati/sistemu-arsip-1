<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_instansis', function (Blueprint $table) {
            $table->string('alamat')->nullable()->after('nama_instansi');
            $table->string('telepon')->nullable()->after('alamat');
            $table->string('fax')->nullable()->after('telepon');
            $table->string('email')->nullable()->after('fax');
            $table->string('website')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('surat_instansis', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'telepon', 'fax', 'email', 'website']);
        });
    }
};
