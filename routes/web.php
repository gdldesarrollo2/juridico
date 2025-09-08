<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\JuicioController;
use App\Http\Controllers\EtapaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AbogadoController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::get('/juicios', [JuicioController::class, 'index'])->name('juicios.index');
Route::get('/juicios/create', [JuicioController::class, 'create'])->name('juicios.create');
Route::post('/juicios', [JuicioController::class, 'store'])->name('juicios.store');
Route::prefix('juicios/{juicio}')->group(function () {
    Route::get('etapas', [EtapaController::class, 'index'])->name('etapas.index');
    Route::post('etapas', [EtapaController::class, 'store'])->name('etapas.store');
    // (opcional) editar/eliminar más tarde:
    // Route::put('etapas/{etapa}', [EtapaController::class, 'update'])->name('etapas.update');
    // Route::delete('etapas/{etapa}', [EtapaController::class, 'destroy'])->name('etapas.destroy');
});
Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index'])->name('clientes.index');      // opcional
    Route::get('/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/', [ClienteController::class, 'store'])->name('clientes.store');
});
Route::resource('abogados', AbogadoController::class)->except(['show']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
