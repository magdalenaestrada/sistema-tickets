<?php

use App\Http\Controllers\ReportesController;

Route::middleware(['auth'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReportesController::class, 'index'])->name('index');
    Route::get('/data/{tipo}', [ReportesController::class, 'datos'])->name('data');
    Route::post('/pdf', [ReportesController::class, 'generar'])->name('generar');
});
