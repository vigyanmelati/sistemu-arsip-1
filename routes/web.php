<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArsipController;
use App\Http\Controllers\PemusnahanController;

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Arsip (untuk Admin dan Super Admin)
    Route::middleware(['admin'])->group(function () {
        Route::resource('arsip', ArsipController::class);
        Route::post('/arsip/import', [ArsipController::class, 'import'])
            ->name('arsip.import');
        Route::post('/arsip/export', [ArsipController::class, 'export'])
            ->name('arsip.export');
          

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



        // Route untuk laporan
        Route::get('/laporan', function () {
            return view('laporan.index');
        })->name('laporan.index');

    });
    
    // Master Data (hanya Super Admin)
    Route::middleware(['super_admin'])->group(function () {
        Route::prefix('master')->name('master.')->group(function () {
            Route::resource('kode-klasifikasi', KodeKlasifikasiController::class);
            Route::resource('sub-bagian', SubBagianController::class);
        });
        
        Route::resource('users', UserController::class);
    });
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// // Route untuk arsip
// Route::resource('arsip', ArsipController::class);


// Route untuk pengaturan
// Route::get('/pengaturan', function () {
//     return view('pengaturan.index');
// })->name('pengaturan.index');


