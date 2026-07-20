<?php

use App\Http\Controllers\FacturacionController;

Route::prefix('facturacion')->name('facturacion.')->group(function () {

    Route::get(
        '/',
        [FacturacionController::class, 'index']
    )->name('index');

    Route::get(
        '/solicitudes-anulacion',
        [FacturacionController::class, 'solicitudes']
    )->name('solicitudes');

    Route::post(
        '/solicitud-anulacion',
        [FacturacionController::class, 'solicitarAnulacion']
    )->name('solicitar.anulacion');
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

    Route::get(
        '/solicitudes-anulacion/{solicitud}',
        [FacturacionController::class, 'showSolicitud']
    )->name('solicitudes.show');

    Route::post(
        '/solicitudes-anulacion/{solicitud}/aprobar',
        [FacturacionController::class, 'aprobarAnulacion']
    )->name('solicitudes.aprobar');

    Route::post(
        '/solicitudes-anulacion/{solicitud}/rechazar',
        [FacturacionController::class, 'rechazarAnulacion']
    )->name('solicitudes.rechazar');
});
