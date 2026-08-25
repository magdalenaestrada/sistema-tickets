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

    Route::get(
        '/ventas-general/excel',
        [ReportesController::class, 'ventasGeneralExcel']
    )->name('ventas.general.excel');

    Route::get(
        '/ventas-general/pdf',
        [ReportesController::class, 'ventasGeneralPdf']
    )->name('ventas-general.pdf');

    Route::get(
        '/ventas-agencia/excel',
        [ReportesController::class, 'ventasPorAgenciaExcel']
    )->name('ventas.agencia.excel');

    Route::get(
        '/ventas-agencia/pdf',
        [ReportesController::class, 'ventasPorAgenciaPdf']
    )->name('ventas.agencia.pdf');

    Route::get(
        '/ventas-ruta/excel',
        [ReportesController::class, 'ventasPorRutaExcel']
    )->name('ventas.ruta.excel');

    Route::get(
        '/ventas-ruta/pdf',
        [ReportesController::class, 'ventasPorRutaPdf']
    )->name('ventas.ruta.pdf');

    Route::get(
        '/pasajeros-ruta/excel',
        [ReportesController::class, 'pasajerosPorRutaExcel']
    )->name('pasajeros.ruta.excel');

    Route::get(
        '/pasajeros-ruta/pdf',
        [ReportesController::class, 'pasajerosPorRutaPdf']
    )->name('pasajeros.ruta.pdf');


    Route::get(
        '/historial-pasajero/excel',
        [ReportesController::class, 'historialPasajeroExcel']
    )->name('historial.pasajero.excel');

    Route::get(
        '/historial-pasajero/pdf',
        [ReportesController::class, 'historialPasajeroPdf']
    )->name('historial.pasajero.pdf');


    Route::get(
        '/sobreequipaje/excel',
        [ReportesController::class, 'sobreequipajeExcel']
    )->name('sobreequipaje.excel');

    Route::get(
        '/sobreequipaje/pdf',
        [ReportesController::class, 'sobreequipajePdf']
    )->name('sobreequipaje.pdf');
});
