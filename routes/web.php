<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\PemusnahanController;
use App\Http\Controllers\Superadmin\SubBagianController;
use App\Http\Controllers\Superadmin\KodeKlasifikasiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubBagianDashboardController;
use App\Http\Controllers\SubBagianArsipController;
use App\Http\Controllers\SubBagianRiwayatPemindahanController;
use App\Http\Controllers\AdminArsipMasukController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\LokasiSubBagianController;
use App\Http\Controllers\BeritaAcaraPindahController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\LintasUnitController;
use App\Http\Controllers\SubBagianSuratMasukController;
use App\Http\Controllers\TUDashboardController;
use App\Http\Controllers\MasterRakController;
use App\Http\Controllers\MasterBoxController;


require __DIR__.'/auth.php';


Route::middleware(['auth', 'nocache'])->group(function () {
    // routes/web.php
Route::post('/arsip/{id}/update-field', [ArsipController::class, 'updateInline'])
    ->name('arsip.update-field');
Route::get('/arsip-masuk/{arsip}/download-file', [AdminArsipMasukController::class, 'downloadFile'])
    ->name('arsip-masuk.download-file')
    ->where('arsip', '[0-9]+'); // Hanya menerima angka
    // Route untuk edit harus diatas route resource atau menggunakan nama yang berbeda
Route::get('/arsip-masuk/{id}/edit', [AdminArsipMasukController::class, 'edit'])->name('arsip-masuk.edit');
Route::put('/arsip-masuk/{id}', [AdminArsipMasukController::class, 'update'])->name('arsip-masuk.update');
Route::post('/arsip-masuk/{id}/update-field', [AdminArsipMasukController::class, 'updateField'])->name('arsip-masuk.update-field');

// Route resource (jika ada)
// Route::resource('arsip-masuk', AdminArsipMasukController::class);
    Route::patch('/arsip/{id}/inline-update', [ArsipController::class, 'updateInline'])
    ->name('arsip.inline-update');
    Route::resource('berita-acara', BeritaAcaraPindahController::class);
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route::get('arsip/export', [ArsipController::class, 'export'])
    //         ->name('arsip.export');

    Route::post('/arsip/export', [ArsipController::class, 'export'])->name('arsip.export');

    Route::get('/arsip/check-duplicates', [ArsipController::class, 'checkDuplicates'])->name('arsip.check-duplicates');
    Route::post('/arsip/update-status-bulk', [ArsipController::class, 'updateStatusBulk'])->name('arsip.update-status-bulk');
    // Arsip (untuk Admin dan Super Admin)
    Route::middleware(['admin'])->group(function () {
       
        Route::post('/arsip/import', [ArsipController::class, 'import'])
            ->name('arsip.import');

           
        
          

        /* =====================================
        |  P E M U S N A H A N  A R S I P
        ===================================== */

        Route::prefix('pemusnahan')
        ->name('pemusnahan.')
        ->group(function () {
            Route::get('/{pemusnahan}/kpu', [PemusnahanController::class, 'kpu'])
        ->name('kpu');

        Route::post('/{pemusnahan}/kpu', [PemusnahanController::class, 'simpanKpu'])
            ->name('kpu.simpan');

            // ===============================
            // USULAN PEMUSNAHAN
            // ===============================
            Route::get('/usulan', [PemusnahanController::class, 'index'])
                ->name('usulan.index');

            Route::get('/usulan/create', [PemusnahanController::class, 'create'])
                ->name('usulan.create');

            Route::post('/usulan', [PemusnahanController::class, 'store'])
                ->name('usulan.store');

            Route::get('/usulan/{pemusnahan}', [PemusnahanController::class, 'show'])
                ->name('usulan.show');

            // ===============================
            // SIDANG
            // ===============================
            Route::get('/usulan/{pemusnahan}/sidang',
                [PemusnahanController::class, 'sidang'])
                ->name('sidang');

            Route::post('/sidang/inline-update',
                [PemusnahanController::class, 'inlineUpdate'])
                ->name('sidang.inline.update');

            Route::post('/usulan/{pemusnahan}/finalisasi',
                [PemusnahanController::class, 'finalisasi'])
                ->name('finalisasi');

            // ===============================
            // PROSES ANRI
            // ===============================
            Route::get('/usulan/{pemusnahan}/anri',
                [PemusnahanController::class, 'anri'])
                ->name('anri');

            Route::post('/usulan/{pemusnahan}/anri',
                [PemusnahanController::class, 'simpanAnri'])
                ->name('anri.simpan');

            // ===============================
            // TAMBAH / HAPUS ARSIP
            // ===============================
            Route::post('/usulan/{pemusnahan}/arsip',
                [PemusnahanController::class, 'tambahArsip'])
                ->name('arsip.tambah');

            Route::delete('/usulan/{pemusnahan}/arsip/{arsip}',
                [PemusnahanController::class, 'hapusArsip'])
                ->name('arsip.hapus');

            // ===============================
            // EXPORT & DOKUMEN
            // ===============================
            Route::get('/export/arsip-usul',
                [PemusnahanController::class, 'daftarArsipExcel'])
                ->name('export.usul');

            Route::get('/usulan/nota-dinas',
                [PemusnahanController::class, 'notaDinasWord'])
                ->name('usulan.nota_dinas');

            // ===============================
            // RIWAYAT
            // ===============================
            Route::get('/riwayat',
                [PemusnahanController::class, 'riwayat'])
                ->name('riwayat');
                Route::post(
                '/pemusnahan/{pemusnahan}/setujui-anri',
                [PemusnahanController::class, 'setujuiAnri']
            )->name('anri.setujui');

            Route::get('/{pemusnahan}/eksekusi', [PemusnahanController::class, 'eksekusi'])
            ->name('eksekusi');

            Route::post('/{pemusnahan}/eksekusi', [PemusnahanController::class, 'simpanEksekusi'])
                ->name('eksekusi.simpan');

            Route::get(
                '/riwayat/{pemusnahan}',
                [PemusnahanController::class, 'riwayatShow']
            )->name('riwayat.show');

            Route::get('/usulan/{pemusnahan}/edit', [PemusnahanController::class, 'edit'])
    ->name('usulan.edit');

Route::put('/usulan/{pemusnahan}', [PemusnahanController::class, 'update'])
    ->name('usulan.update');

Route::delete('/usulan/{pemusnahan}', [PemusnahanController::class, 'destroy'])
    ->name('usulan.destroy');

        });
          // Arsip Masuk (pengajuan dari subbagian)
         // Arsip Masuk
            Route::post('/arsip-masuk/{arsip}/verifikasi', [AdminArsipMasukController::class, 'verifikasi'])
        ->name('admin.arsip-masuk.verifikasi');
    Route::get('/arsip-masuk', [AdminArsipMasukController::class, 'index'])->name('arsip-masuk.index');
    Route::get('/arsip-masuk/{arsip}', [AdminArsipMasukController::class, 'show'])->name('arsip-masuk.show');
    Route::post('/arsip-masuk/{arsip}/terima', [AdminArsipMasukController::class, 'terima'])->name('admin.arsip-masuk.terima');
    Route::post('/arsip-masuk/{arsip}/tolak', [AdminArsipMasukController::class, 'tolak'])->name('admin.arsip-masuk.tolak');
    Route::post('/arsip-masuk/{arsip}/pindahkan', [AdminArsipMasukController::class, 'pindahkan'])->name('admin.arsip-masuk.pindahkan');
    Route::post('/arsip-masuk/proses-multiple', [AdminArsipMasukController::class, 'prosesMultiple'])->name('admin.arsip-masuk.proses-multiple');
    Route::get('/arsip-masuk/{arsip}/download-berita-acara', [AdminArsipMasukController::class, 'downloadBeritaAcara'])->name('admin.arsip-masuk.download-berita-acara');
    Route::get('/arsip-masuk-dashboard', [AdminArsipMasukController::class, 'dashboard'])->name('arsip-masuk.dashboard');
       
    });

    Route::get('arsip-masuk/{arsip}/download-berita-acara', [AdminArsipMasukController::class, 'downloadBeritaAcara'])
    ->name('arsip-masuk.download-berita-acara');
    
    // Master Data (hanya Super Admin)
    Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {

        Route::resource('sub-bagians', SubBagianController::class)
            ->except(['create', 'show', 'edit']);

        Route::resource('kode-klasifikasis', KodeKlasifikasiController::class)
            ->except(['create', 'show', 'edit']);
        Route::resource('users', UserController::class);
    });
    Route::get('/', function () {
    return redirect()->route('dashboard');
});

    // Manajemen Lokasi (tanpa tabel baru)
// routes/web.php
Route::prefix('manajemen-lokasi')->name('manajemen-lokasi.')->group(function () {
    Route::get('/', [LokasiController::class, 'index'])->name('index');                  // card ruangan
    Route::get('/ruangan/{ruangan}', [LokasiController::class, 'listRak'])->name('rak'); // card rak
    Route::get('/ruangan/{ruangan}/rak/{rak}', [LokasiController::class, 'listBox'])->name('box'); // card box
    Route::get('/ruangan/{ruangan}/rak/{rak}/box/{box}', [LokasiController::class, 'listArsip'])->name('arsip'); // list arsip
    
});

Route::get('/surat-masuk/template', function () {
    return response()->download(
        public_path('template/template_import_surat_masuk.xlsx')
    );
})->name('surat-masuk.template');

Route::get('/surat-masuk/export', [SuratMasukController::class, 'export'])
    ->name('surat-masuk.export');

Route::post('/surat-masuk/import', [SuratMasukController::class, 'import'])
    ->name('surat-masuk.import');

Route::resource('surat-masuk', SuratMasukController::class);

Route::get(
    'surat-masuk/{id}/disposisi',
    [SuratMasukController::class, 'disposisi']
)->name('surat-masuk.disposisi');





});



Route::middleware(['auth', 'nocache'])->group(function () {
    Route::get('/surat-masuk/cek-duplikasi', [
    SuratMasukController::class,
    'cekDuplikasi'
])->name('surat-masuk.cek-duplikasi');
    Route::post('/arsip-masuk/{id}/update-field', [AdminArsipMasukController::class, 'updateField'])->name('arsip-masuk.update-field');
     Route::resource('arsip', ArsipController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
Route::prefix('lintas-unit')->name('lintas-unit.')->group(function () {

    Route::get('/', [LintasUnitController::class, 'index'])
        ->name('index');

    Route::get('/{unit}', [LintasUnitController::class, 'daftar'])
        ->name('daftar');

});
    Route::get('/arsip/{arsip}/download-file', [SubBagianArsipController::class, 'downloadFile'])->name('arsip.downloadFile');
   Route::post('/berita-acara/{berita_acara}/kirim', [BeritaAcaraPindahController::class, 'kirim'])->name('berita-acara.kirim');
    Route::delete(
    '/berita-acara/{berita_acara}/arsip/{arsip}',
    [BeritaAcaraPindahController::class, 'removeArsip']
)->name('berita-acara.removeArsip');
    // === ROUTE EXPORT LAMPIRAN ===
    Route::get('/berita-acara/{berita_acara}/export-lampiran', [BeritaAcaraPindahController::class, 'exportLampiran'])->name('berita-acara.exportLampiran');
Route::post('/manajemen-lokasi/rak', [LokasiController::class, 'storeRak'])->name('manajemen-lokasi.store-rak');
Route::post('/manajemen-lokasi/box', [LokasiController::class, 'storeBox'])->name('manajemen-lokasi.store-box');
Route::get('/manajemen-lokasi/rak/{id}/edit', [LokasiController::class, 'editRak'])->name('manajemen-lokasi.edit-rak');
Route::put('/manajemen-lokasi/rak/{id}', [LokasiController::class, 'updateRak'])->name('manajemen-lokasi.update-rak');
Route::delete('/manajemen-lokasi/rak/{id}', [LokasiController::class, 'destroyRak'])->name('manajemen-lokasi.destroy-rak');
Route::put('/manajemen-lokasi/box/{id}', [LokasiController::class, 'updateBox'])->name('manajemen-lokasi.update-box');
Route::delete('/manajemen-lokasi/box/{id}', [LokasiController::class, 'destroyBox'])->name('manajemen-lokasi.destroy-box');
});

Route::middleware(['auth', 'subbagian'])->prefix('subbagian')->name('subbagian.')->group(function () {

    Route::post('/manajemen-lokasi/rak', [LokasiSubBagianController::class, 'storeRak'])->name('manajemen-lokasi.store-rak');
    Route::post('/manajemen-lokasi/box', [LokasiSubBagianController::class, 'storeBox'])->name('manajemen-lokasi.store-box');
    Route::put('/manajemen-lokasi/rak/{id}', [LokasiSubBagianController::class, 'updateRak'])->name('manajemen-lokasi.update-rak');
    Route::delete('/manajemen-lokasi/rak/{id}', [LokasiSubBagianController::class, 'destroyRak'])->name('manajemen-lokasi.destroy-rak');

    Route::put('/manajemen-lokasi/box/{id}', [LokasiSubBagianController::class, 'updateBox'])->name('manajemen-lokasi.update-box');
    Route::delete('/manajemen-lokasi/box/{id}', [LokasiSubBagianController::class, 'destroyBox'])->name('manajemen-lokasi.destroy-box');

    Route::get('/dashboard', [SubBagianDashboardController::class, 'index'])->name('dashboard');
     // CRUD Arsip
    Route::get('/arsip', [SubBagianArsipController::class, 'index'])->name('arsip.index');
    Route::get('/arsip/create', [SubBagianArsipController::class, 'create'])->name('arsip.create');
    Route::post('/arsip', [SubBagianArsipController::class, 'store'])->name('arsip.store');
    Route::get('/arsip/{arsip}/edit', [SubBagianArsipController::class, 'edit'])->name('arsip.edit');
    Route::put('/arsip/{arsip}', [SubBagianArsipController::class, 'update'])->name('arsip.update');
    Route::delete('/arsip/{arsip}', [SubBagianArsipController::class, 'destroy'])->name('arsip.destroy');
    Route::get('/arsip/{arsip}', [SubBagianArsipController::class, 'show'])->name('arsip.show');

    // Import & Export
    Route::post('/arsip/import', [SubBagianArsipController::class, 'import'])->name('arsip.import');
    Route::post('/arsip/export', [SubBagianArsipController::class, 'export'])->name('arsip.export');
    Route::get('/arsip/{arsip}/download-file', [SubBagianArsipController::class, 'downloadFile'])->name('arsip.downloadFile');

    // Ajukan Pindah (upload BAP)
    Route::post('/arsip/{arsip}/ajukan-pindah', [SubBagianArsipController::class, 'ajukanPindah'])->name('arsip.ajukanPindah');
    Route::post('/arsip/ajukan-pindah-multiple', [SubBagianArsipController::class, 'ajukanPindahMultiple'])->name('arsip.ajukanPindahMultiple');

     Route::prefix('riwayat-pemindahan')->name('riwayat-pemindahan.')->group(function () {
        Route::get('/', [SubBagianRiwayatPemindahanController::class, 'index'])->name('index');
        Route::get('/{arsip}', [SubBagianRiwayatPemindahanController::class, 'show'])->name('show');
        Route::post('/{arsip}/perbaiki', [SubBagianRiwayatPemindahanController::class, 'perbaikiArsip'])->name('perbaiki');
        // Route::post('/{arsip}/ajukan-kembali', [SubBagianRiwayatPemindahanController::class, 'ajukanKembali'])->name('ajukan-kembali');
        Route::get('/riwayat-pemindahan/{arsip}/edit-perbaikan', 
        [SubBagianRiwayatPemindahanController::class, 'editPerbaikan'])
        ->name('edit-perbaikan');
        
        Route::put('/riwayat-pemindahan/{arsip}/update-perbaikan', 
            [SubBagianRiwayatPemindahanController::class, 'updatePerbaikan'])
            ->name('update-perbaikan');
            
        Route::post('/riwayat-pemindahan/{arsip}/ajukan-kembali', 
            [SubBagianRiwayatPemindahanController::class, 'ajukanKembali'])
            ->name('ajukan-kembali');
        });

        Route::prefix('manajemen-lokasi')->name('manajemen-lokasi.')->group(function () {
        Route::get('/', [LokasiSubBagianController::class, 'index'])->name('index');                  // card ruangan
        Route::get('/ruangan/{ruangan}', [LokasiSubBagianController::class, 'listRak'])->name('rak'); // card rak
        Route::get('/ruangan/{ruangan}/rak/{rak}', [LokasiSubBagianController::class, 'listBox'])->name('box'); // card box
        Route::get('/ruangan/{ruangan}/rak/{rak}/box/{box}', [LokasiSubBagianController::class, 'listArsip'])->name('arsip'); // list arsip
    });
Route::post('/subbagian/arsip/{arsip}/duplicate', [SubBagianArsipController::class, 'duplicate'])
    ->name('subbagian.arsip.duplicate');
Route::get(
    '/berita-acara/{berita_acara}/export-lampiran',
    [BeritaAcaraPindahController::class, 'exportLampiran']
)->name('berita-acara.exportLampiran');

    
});




Route::middleware(['auth'])->group(function () {
    Route::get('/tu/dashboard', [TuDashboardController::class, 'index'])
        ->name('tu.dashboard');

        Route::get('/arsip-masuk/{id}/edit', [AdminArsipMasukController::class, 'edit'])->name('arsip-masuk.edit');
Route::put('/arsip-masuk/{id}', [AdminArsipMasukController::class, 'update'])->name('arsip-masuk.update');
});

Route::middleware(['auth'])->prefix('subbagian')->name('subbagian.')->group(function () {
    Route::get('/surat-masuk/template', function () {
        return response()->download(
            public_path('template/template_import_surat_masuk.xlsx')
        );
    })->name('surat-masuk.template');

    Route::get('/surat-masuk/export', [SuratMasukController::class, 'export'])
        ->name('surat-masuk.export');

    Route::post('/surat-masuk/import', [SuratMasukController::class, 'import'])
        ->name('surat-masuk.import');

        Route::get(
            '/surat-masuk/{id}/disposisi',
            [SubBagianSuratMasukController::class, 'disposisi']
        )->name('surat-masuk.disposisi');

        Route::resource(
            'surat-masuk',
            SubBagianSuratMasukController::class
        );
});
Route::prefix('subbagian/riwayat-pemindahan')
    ->name('subbagian.riwayat-pemindahan.')
    ->middleware(['auth'])
    ->group(function () {

        // Halaman utama riwayat pemindahan
        Route::get('/', [SubBagianRiwayatPemindahanController::class, 'index'])
            ->name('index');

        // Detail arsip
        Route::get('/{arsip}', [SubBagianRiwayatPemindahanController::class, 'show'])
            ->name('show');

        // Form perbaikan arsip
        Route::get('/{arsip}/perbaikan', [SubBagianRiwayatPemindahanController::class, 'perbaikanForm'])
            ->name('perbaikan');

        // Simpan hasil perbaikan
        Route::put('/{arsip}/perbaikan', [SubBagianRiwayatPemindahanController::class, 'simpanPerbaikan'])
            ->name('simpan-perbaikan');

        // Ajukan kembali setelah diperbaiki
        Route::post('/{arsip}/ajukan-kembali', [SubBagianRiwayatPemindahanController::class, 'ajukanKembali'])
            ->name('ajukan-kembali');

        // Kembalikan menjadi arsip internal
        Route::post('/{arsip}/kembalikan-internal', [SubBagianRiwayatPemindahanController::class, 'kembalikanInternal'])
            ->name('kembalikan-internal');

    });


