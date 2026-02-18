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

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('arsip/export', [ArsipController::class, 'export'])
            ->name('arsip.export');
    
    // Arsip (untuk Admin dan Super Admin)
    Route::middleware(['admin'])->group(function () {
        Route::resource('arsip', ArsipController::class);
        Route::post('/arsip/import', [ArsipController::class, 'import'])
            ->name('arsip.import');
        

          

        /* =====================================
        |  P E M U S N A H A N  A R S I P
        ===================================== */
        Route::prefix('pemusnahan')
        ->name('pemusnahan.')
        ->group(function () {

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

        });
          // Arsip Masuk (pengajuan dari subbagian)
         // Arsip Masuk
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

});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::middleware(['auth', 'subbagian'])->prefix('subbagian')->name('subbagian.')->group(function () {
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

    
});



