<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_instansis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi')->unique();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tujuan_disposisis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tujuan')->unique();
            $table->boolean('aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->foreignId('instansi_id')->nullable()->after('sub_bagian_id')
                ->constrained('surat_instansis')->nullOnDelete();
            $table->unsignedBigInteger('sub_bagian_id')->nullable()->change();
        });

        Schema::create('surat_masuk_tujuan_disposisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->cascadeOnDelete();
            $table->foreignId('tujuan_disposisi_id')->constrained('tujuan_disposisis')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['surat_masuk_id', 'tujuan_disposisi_id'], 'sm_tujuan_unique');
        });

        DB::table('surat_masuk')->select('instansi_satker')->whereNotNull('instansi_satker')
            ->where('instansi_satker', '<>', '')->distinct()->orderBy('instansi_satker')
            ->each(function ($row) {
                $nama = trim($row->instansi_satker);
                $id = DB::table('surat_instansis')->where('nama_instansi', $nama)->value('id');
                if (! $id) {
                    $id = DB::table('surat_instansis')->insertGetId([
                        'nama_instansi' => $nama,
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('surat_masuk')->where('instansi_satker', $row->instansi_satker)
                    ->update(['instansi_id' => $id]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_tujuan_disposisi');
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instansi_id');
        });
        Schema::dropIfExists('tujuan_disposisis');
        Schema::dropIfExists('surat_instansis');
    }
};
