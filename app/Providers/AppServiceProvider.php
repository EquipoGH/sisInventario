<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Bien;
use App\Observers\BienObserver;
use Illuminate\Pagination\Paginator;

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
        // Registrar el Observer de Bien
        Bien::observe(BienObserver::class);

        // Bootstrap 5 para paginación (no se rompe nada en vistas)
        Paginator::useBootstrapFive();
    }
}
