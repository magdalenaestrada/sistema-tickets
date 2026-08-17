<?php

use App\Http\Controllers\FacturacionController;

Route::prefix('facturacion')->name('facturacion.')->group(function () {

    Route::get(
        '/',
        [FacturacionController::class, 'index']
    )->name('index');
    Route::get(
        '/{venta}',
        [FacturacionController::class, 'show']
    )->name('show');

    Route::post(
        '/{venta}/emitir',
        [FacturacionController::class, 'emitir']
    )->name('emitir');

    Route::get(
        '/{venta}/xml',
        [FacturacionController::class, 'descargarXml']
    )->name('xml');

    Route::get(
        '/{venta}/cdr',
        [FacturacionController::class, 'descargarCdr']
    )->name('cdr');

    Route::get(
        '/{venta}/pdf',
        [FacturacionController::class, 'descargarPdf']
    )->name('pdf');

    Route::post('/pos', [FacturacionController::class, 'store'])
        ->name('pos.store');

    Route::post('anular/{venta}', [FacturacionController::class, 'anularVenta'])
        ->name('anular');

    Route::post('/{venta}/anular-nota', [FacturacionController::class, 'crearNotaAnulacion'])
        ->name('anular.nota');

    Route::get('/buscar-comprobante', [FacturacionController::class, 'buscarComprobante'])
        ->name('buscar-comprobante');

    Route::post('/convertir-comprobante', [FacturacionController::class, 'convertirComprobante'])
        ->name('convertir-comprobante');
});


Route::prefix('solicitudes')->name('solicitudes.')->group(function () {

    Route::get(
        '/',
        [FacturacionController::class, 'solicitudes']
    )->name('index');

    Route::post(
        '/anulacion',
        [FacturacionController::class, 'solicitarAnulacion']
    )->name('anulacion');
    Route::get(
        '/{solicitud}',
        [FacturacionController::class, 'showSolicitud']
    )->name('show');

    Route::post(
        '/{solicitud}/aprobar',
        [FacturacionController::class, 'aprobarAnulacion']
    )->name('aprobar');

    Route::post(
        '/{solicitud}/rechazar',
        [FacturacionController::class, 'rechazarAnulacion']
    )->name('rechazar');
});
