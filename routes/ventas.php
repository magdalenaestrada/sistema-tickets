<?php

use App\Http\Controllers\PasajeController;
use App\Http\Controllers\VentasController;

Route::middleware(['auth'])->prefix('ventas')->name('ventas.')->group(function () {
    Route::get('/{venta}/tickets', [PasajeController::class, 'ticketsVenta'])
        ->name('tickets');
    Route::get('/{venta}/imprimir', [VentasController::class, 'imprimir'])
        ->name('imprimir');

    Route::get('/{venta}/ticket-pdf', [PasajeController::class, 'ticketVentaPdf'])->name('ticket.pdf');
});
