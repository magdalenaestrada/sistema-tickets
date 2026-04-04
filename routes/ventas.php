<?php

use App\Http\Controllers\VentasController;

Route::middleware(['auth'])->prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/{venta}/imprimir', [VentasController::class, 'imprimir'])
    ->name('imprimir');
});
