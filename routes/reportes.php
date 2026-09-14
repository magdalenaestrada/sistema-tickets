<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportesController;

Route::middleware(['auth', 'can:gestionar reportes'])
    ->prefix('reportes')
    ->name('reportes.')
    ->group(function () {

        Route::get('/', [ReportesController::class, 'index'])
            ->name('index');

        Route::get('/ventas/resumen', [ReportesController::class, 'resumenVentas'])
            ->name('ventas.resumen');


        // =====================================================
        // VENTAS POR USUARIO
        // =====================================================

        Route::get(
            '/ventas-usuario/excel',
            [ReportesController::class, 'ventasPorUsuarioExcel']
        )->name('ventas.usuario.excel');

        Route::get(
            '/ventas-usuario/pdf',
            [ReportesController::class, 'ventasPorUsuarioPdf']
        )->name('ventas.usuario.pdf');


        // =====================================================
        // VENTAS GENERAL
        // =====================================================

        Route::get(
            '/ventas-general/excel',
            [ReportesController::class, 'ventasGeneralExcel']
        )->name('ventas.general.excel');

        Route::get(
            '/ventas-general/pdf',
            [ReportesController::class, 'ventasGeneralPdf']
        )->name('ventas.general.pdf');


        // =====================================================
        // VENTAS POR AGENCIA
        // =====================================================

        Route::get(
            '/ventas-agencia/excel',
            [ReportesController::class, 'ventasPorAgenciaExcel']
        )->name('ventas.agencia.excel');

        Route::get(
            '/ventas-agencia/pdf',
            [ReportesController::class, 'ventasPorAgenciaPdf']
        )->name('ventas.agencia.pdf');


        // =====================================================
        // VENTAS POR RUTA
        // =====================================================

        Route::get(
            '/ventas-ruta/excel',
            [ReportesController::class, 'ventasPorRutaExcel']
        )->name('ventas.ruta.excel');

        Route::get(
            '/ventas-ruta/pdf',
            [ReportesController::class, 'ventasPorRutaPdf']
        )->name('ventas.ruta.pdf');


        // =====================================================
        // PASAJEROS POR RUTA
        // =====================================================

        Route::get(
            '/pasajeros-ruta/excel',
            [ReportesController::class, 'pasajerosPorRutaExcel']
        )->name('pasajeros.ruta.excel');

        Route::get(
            '/pasajeros-ruta/pdf',
            [ReportesController::class, 'pasajerosPorRutaPdf']
        )->name('pasajeros.ruta.pdf');


        // =====================================================
        // HISTORIAL PASAJERO
        // Por ahora solamente PDF porque es el método que existe
        // =====================================================

        Route::get(
            '/historial-pasajero/pdf',
            [ReportesController::class, 'historialPasajeroPdf']
        )->name('historial.pasajero.pdf');
    });
