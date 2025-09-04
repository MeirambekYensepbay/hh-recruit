<?php

use App\Http\Controllers\HHController;
use Illuminate\Support\Facades\Route;

Route::get('/vacancies', [HHController::class, 'index']);
Route::get('/vacancy/response-urls/{id}', action: [HHController::class, 'getResponseUrls'])->name('vacancy.responses.urls');
Route::post('/vacancy/analyse', action: [HHController::class, 'getAnalyse'])->name('vacancy.analyse');
Route::post('/responses/save', action: [HHController::class, 'setResponses'])->name('responses.set');
