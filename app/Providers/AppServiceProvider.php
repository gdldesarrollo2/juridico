<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
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
         // Parche temporal: neutraliza cualquier "regla" llamada así
    foreach (['gabinete','domiciliaria','electronica','secuencial'] as $alias) {
        Validator::extend($alias, function () {
            return true; // siempre pasa
        });
    }
    }
}
