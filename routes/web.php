<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\JuicioController;
use App\Http\Controllers\EtapaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AbogadoController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\RevisionEtapaController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\UserRoleController;

// Debug de roles/permisos
Route::get('/debug-role', function () {
    $u = auth()->user();
    return [
        'user' => $u?->only(['id','name','email']),
        'roles' => $u?->getRoleNames(),
        'all_permissions' => $u?->getAllPermissions()->pluck('name'),
        'can_ver_juicios' => $u?->can('ver juicios'),
        'has_role_admin' => $u?->hasRole('admin'),
        'guard_default' => config('auth.defaults.guard'),
    ];
})->middleware(['auth']);

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // ===================== Dashboard =====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===================== Juicios =====================
    Route::get('/juicios', [JuicioController::class, 'index'])
        ->middleware('role_or_permission:admin|abogado|ver juicios,web')
        ->name('juicios.index');

    Route::get('/juicios/{juicio}', [JuicioController::class, 'show'])
        ->middleware('role_or_permission:admin|abogado|ver juicios,web')
        ->name('juicios.show');

    Route::get('/juicios/create', [JuicioController::class, 'create'])
        ->middleware('role_or_permission:admin|abogado|crear juicios,web')
        ->name('juicios.create');

    Route::post('/juicios', [JuicioController::class, 'store'])
        ->middleware('role_or_permission:admin|abogado|crear juicios,web')
        ->name('juicios.store');

    Route::get('/juicios/{juicio}/edit', [JuicioController::class, 'edit'])
        ->middleware('role_or_permission:admin|abogado|editar juicios,web')
        ->name('juicios.edit');

    Route::put('/juicios/{juicio}', [JuicioController::class, 'update'])
        ->middleware('role_or_permission:admin|abogado|editar juicios,web')
        ->name('juicios.update');

    Route::prefix('juicios/{juicio}')->group(function () {
        Route::get('etapas', [EtapaController::class, 'index'])
            ->middleware('role_or_permission:admin|abogado|ver juicios,web')
            ->name('juicios.etapas.index');

        Route::post('etapas', [EtapaController::class, 'store'])
            ->middleware('role_or_permission:admin|abogado|crear etapa de juicio,web')
            ->name('juicios.etapas.store');
    });

    // ===================== Clientes =====================
    Route::prefix('clientes')->group(function () {
        Route::get('/', [ClienteController::class, 'index'])
            ->middleware('role_or_permission:admin|abogado|ver juicios,web')
            ->name('clientes.index');

        Route::get('/create', [ClienteController::class, 'create'])
            ->middleware('role_or_permission:admin|abogado|crear juicios,web')
            ->name('clientes.create');

        Route::post('/', [ClienteController::class, 'store'])
            ->middleware('role_or_permission:admin|abogado|crear juicios,web')
            ->name('clientes.store');
    });

    // ===================== Abogados =====================
    Route::resource('abogados', AbogadoController::class)
        ->only(['index','create','store','edit','update'])
        ->middleware('role_or_permission:admin|abogado|ver juicios,web');

    Route::get('abogados/{abogado}/reasignar', [AbogadoController::class, 'reasignarForm'])
        ->middleware('role_or_permission:admin|abogado|editar juicios,web')
        ->name('abogados.reasignar.form');

    Route::post('abogados/{abogado}/reasignar', [AbogadoController::class, 'reasignarStore'])
        ->middleware('role_or_permission:admin|abogado|editar juicios,web')
        ->name('abogados.reasignar.store');

    // ===================== Revisiones =====================
    Route::resource('revisiones', RevisionController::class)
        ->parameters(['revisiones' => 'revision'])
        ->except(['show'])
        ->middleware('role_or_permission:admin|abogado|ver revisiones,web');

    Route::get('/revisiones/{revision}/etapas', [RevisionEtapaController::class, 'index'])
        ->middleware('role_or_permission:admin|abogado|ver revisiones,web')
        ->name('revisiones.etapas.index');

    Route::post('/revisiones/{revision}/etapas', [RevisionEtapaController::class, 'store'])
        ->middleware('role_or_permission:admin|abogado|crear revisiones,web')
        ->name('revisiones.etapas.store');

    Route::put('/revisiones/etapas/{etapa}', [RevisionEtapaController::class, 'update'])
        ->middleware('role_or_permission:admin|abogado|editar revisiones,web')
        ->name('revisiones.etapas.update');

    // ===================== Calendario =====================
    Route::get('/calendario', [CalendarioController::class, 'index'])
        ->middleware('role_or_permission:admin|abogado|ver juicios,web')
        ->name('calendario.index');

    Route::post('/calendario/upload', [CalendarioController::class, 'upload'])
        ->middleware('role_or_permission:admin|abogado|editar juicios,web')
        ->name('calendario.upload');

    // ===================== Seguridad (Roles & Permisos) =====================
    Route::middleware('role:admin')->prefix('seguridad')->group(function () {
        Route::get('roles',               [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}/edit',   [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}',        [RoleController::class, 'update'])->name('roles.update');

        Route::get('usuarios',            [UserRoleController::class, 'index'])->name('users.roles.index');
        Route::get('usuarios/{user}/rol', [UserRoleController::class, 'edit'])->name('users.roles.edit');
        Route::put('usuarios/{user}/rol', [UserRoleController::class, 'update'])->name('users.roles.update');
    });
});

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Otros requires
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
