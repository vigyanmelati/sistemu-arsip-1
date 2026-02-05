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
        Schema::table('pemusnahan', function (Blueprint $table) {
            $table->json('dokumen_pemusnahan')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('pemusnahan', function (Blueprint $table) {
            $table->dropColumn('dokumen_pemusnahan');
        });
    }
};
