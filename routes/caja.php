<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CajaController;

Route::middleware(['auth'])->prefix('caja')->group(function () {
    Route::get('/', [CajaController::class, 'index'])->name('caja.index');
    Route::get('/create', [CajaController::class, 'create'])->name('caja.create');
    Route::post('/', [CajaController::class, 'store'])->name('caja.store');
    Route::get('/{caja}', [CajaController::class, 'show'])->name('caja.show');
    Route::post('/{caja}/ingreso', [CajaController::class, 'registrarIngreso'])->name('caja.ingreso');
    Route::post('/{caja}/salida', [CajaController::class, 'registrarSalida'])->name('caja.salida');
    Route::post('/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('caja.cerrar');
    Route::get('/ticket/{detalle}/reimprimir', [CajaController::class, 'reimprimir'])
        ->name('caja.ticket.reimprimir');
    Route::post('/ticket/{detalle}/anular', [CajaController::class, 'anular'])
        ->name('caja.ticket.anular');
});
