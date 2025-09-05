<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/vacancy/{id}', action: [\App\Http\Controllers\HHController::class, 'getVacancy'])->name('getVacancy');
Route::get('/vacancy/response/{id}', action: [\App\Http\Controllers\HHController::class, 'showVacancy'])->name('show.vacancy');
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
