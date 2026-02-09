<?php

namespace App\Providers;

use App\Models\Arsip;
use App\Observers\ArsipObserver;
use Illuminate\Support\ServiceProvider;

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
         Arsip::observe(ArsipObserver::class);
    }
}
