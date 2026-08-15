<?php

use App\Http\Controllers\ReportesController;

Route::middleware(['auth', 'can:gestionar reportes'])->prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [ReportesController::class, 'index'])->name('index');
    Route::get(
        '/ventas/resumen',
        [ReportesController::class, 'resumenVentas']
    )->name('ventas.resumen');

    Route::get(
        '/ventas-usuario/excel',
        [ReportesController::class, 'ventasPorUsuarioExcel']
    )->name('ventas.usuario.excel');

    Route::get(
        '/ventas-usuario/pdf',
        [ReportesController::class, 'ventasPorUsuarioPdf']
    )->name('ventas.usuario.pdf');
});
