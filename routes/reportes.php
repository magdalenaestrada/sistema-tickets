<?php

use App\Http\Controllers\ReportesController;

Route::middleware(['auth'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReportesController::class, 'index'])->name('index');
    Route::post('/pdf', [ReportesController::class, 'generar'])->name('generar');
});
