<?php

namespace App\Providers;

use App\Models\Arsip;
use App\Observers\ArsipObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\Satker;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Arsip::observe(ArsipObserver::class);
        View::composer('*', function ($view) {
            $view->with('namaSatkerAktif', Satker::aktif()->nama_satker ?? 'KPU Provinsi Bali');
        });
    }
}
