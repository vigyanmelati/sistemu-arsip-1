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

  /* -------- P E M U S N A H A N  A R S I P -------- */
       Route::prefix('pemusnahan')->name('pemusnahan.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PROSES PEMUSNAHAN (INTI)
    |--------------------------------------------------------------------------
    */
    Route::get('/proses', [PemusnahanController::class, 'usulan'])
        ->name('proses');

    /*
    |--------------------------------------------------------------------------
    | ROUTE LAMA (TETAP DIPAKAI)
    |--------------------------------------------------------------------------
    */
    Route::get('/usulan', [PemusnahanController::class, 'usulan'])
        ->name('usulan');

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE DOKUMEN
    |--------------------------------------------------------------------------
    */
    Route::get('/usulan/nota-dinas', function () {
        $path = public_path('template/Nota Dinas Penghapusan Arsip.docx');

        if (!file_exists($path)) {
            abort(404, 'Template Nota Dinas tidak ditemukan');
        }

        return response()->download(
            $path,
            'Nota Dinas Penghapusan Arsip.docx'
        );
    })->name('usulan.nota_dinas_word');

    /*
    |--------------------------------------------------------------------------
    | DAFTAR ARSIP (EXCEL)
    |--------------------------------------------------------------------------
    */
    Route::get('/usulan/daftar-arsip-excel',
        [PemusnahanController::class, 'daftarArsipExcel']
    )->name('usulan.excel');

    /*
    |--------------------------------------------------------------------------
    | RIWAYAT PEMUSNAHAN
    |--------------------------------------------------------------------------
    */
    Route::get('/riwayat', [PemusnahanController::class, 'riwayat'])
        ->name('riwayat');

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


