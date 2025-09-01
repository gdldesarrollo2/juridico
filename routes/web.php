<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\JuicioController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/juicios', [JuicioController::class, 'index'])->name('juicios.index');
Route::get('/juicios/create', [JuicioController::class, 'create'])->name('juicios.create');
Route::post('/juicios', [JuicioController::class, 'store'])->name('juicios.store');
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
