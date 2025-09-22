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
    Route::get('etapas', [EtapaController::class, 'index'])
        ->name('juicios.etapas.index');   // <- agrega el prefijo en el nombre
    Route::post('etapas', [EtapaController::class, 'store'])
        ->name('juicios.etapas.store');
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
Route::middleware(['auth'])->group(function () {
    Route::resource('revisiones', RevisionController::class)
        ->parameters(['revisiones' => 'revision']) // 👈 fuerza {revision}
        ->except(['show']);
});
//Route::post('revisiones/{revision}/etapas',        [RevisionEtapaController::class, 'store'])->name('revisiones.etapas.store');
Route::middleware(['auth','verified'])->group(function () {
    // Etapas anidadas a la revisión
    Route::get('/revisiones/{revision}/etapas',        [RevisionEtapaController::class, 'index'])->name('revisiones.etapas.index');
    Route::post('/revisiones/{revision}/etapas',       [RevisionEtapaController::class, 'store'])->name('revisiones.etapas.store');
    Route::put('/revisiones/etapas/{etapa}',           [RevisionEtapaController::class, 'update'])->name('revisiones.etapas.update');
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
