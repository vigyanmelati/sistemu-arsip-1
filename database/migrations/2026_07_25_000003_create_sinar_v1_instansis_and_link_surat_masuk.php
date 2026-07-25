<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinar_v1_instansis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->unique();
            $table->string('nama_instansi');
            $table->string('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamp('legacy_created_at')->nullable();
            $table->timestamp('legacy_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->foreignId('sinar_v1_document_id')->nullable()->after('id')
                ->unique()->constrained('sinar_v1_documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuk', fn (Blueprint $table) => $table->dropConstrainedForeignId('sinar_v1_document_id'));
        Schema::dropIfExists('sinar_v1_instansis');
    }
};
