<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
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
        // Controller memakai parameter `int $id`; ID bukan angka harus 404, bukan TypeError (500).
        Route::patterns(array_fill_keys(['id', 'santri', 'ustadz', 'wali_murid', 'kegiatan', 'session'], '[0-9]+'));
    }
}
