<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\JuicioController;
use App\Http\Controllers\EtapaController;
use App\Http\Controllers\DashboardController;

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
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
