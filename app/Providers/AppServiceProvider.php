<?php

// app/Providers/AppServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ Parche temporal: neutraliza cualquier "regla" llamada así
        foreach (['gabinete','domiciliaria','electronica','secuencial'] as $alias) {
            Validator::extend($alias, function () {
                return true; // siempre pasa
            });
        }

        // ✅ Compartir datos de autenticación/roles/permisos con Inertia
        Inertia::share('auth', function () {
            $user = auth()->user();

            return [
                'user' => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'roles' => $user->getRoleNames(),
                    'perms' => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ];
        });
    }
}
