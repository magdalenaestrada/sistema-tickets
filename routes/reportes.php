<?php

use App\Http\Controllers\ReportesController;

Route::middleware(['auth', 'can:gestionar reportes'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReportesController::class, 'index'])->name('index');
    Route::get(
        '/ventas/resumen',
        [ReportesController::class, 'resumenVentas']
    )->name('ventas.resumen');
});
