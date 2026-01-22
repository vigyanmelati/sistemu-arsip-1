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
        // Route untuk pemusnahan
        Route::get('/pemusnahan', [PemusnahanController::class, 'index'])->name('pemusnahan.index');
        Route::get('/pemusnahan/create', [PemusnahanController::class, 'create'])->name('pemusnahan.create');
        Route::post('/pemusnahan', [PemusnahanController::class, 'store'])->name('pemusnahan.store');
        Route::get('/pemusnahan/{id}/approve', [PemusnahanController::class, 'approve'])->name('pemusnahan.approve');
        Route::post('/pemusnahan/{id}/approve', [PemusnahanController::class, 'processApprove'])->name('pemusnahan.process-approve');
        Route::post('/pemusnahan/{id}/reject', [PemusnahanController::class, 'reject'])->name('pemusnahan.reject');

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


