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
});
