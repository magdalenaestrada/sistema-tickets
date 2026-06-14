<?php

use App\Http\Controllers\ReportesController;

Route::middleware(['auth', 'can:gestionar reportes'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get(
        '/ventas/resumen',
        [ReportesController::class, 'resumenVentas']
    )->name('ventas.resumen');
});
