<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CajaController;

Route::middleware(['auth', 'can:gestionar caja'])->prefix('cajas')->name('caja.')->group(function () {
    Route::get('/', [CajaController::class, 'index'])->name('index');
    Route::post('/', [CajaController::class, 'store'])->name('store');
    Route::get('/{caja}', [CajaController::class, 'show'])->name('show');

    Route::post('/{caja}/ingreso', [CajaController::class, 'registrarIngreso'])->name('ingreso');
    Route::post('/{caja}/salida', [CajaController::class, 'registrarSalida'])->name('salida');

    Route::post('/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('cerrar');
    Route::get('/{caja}/print-corte', [CajaController::class, 'print_corte'])->name('print_corte');
    Route::get('/{caja}/print-corte-masivo', [CajaController::class, 'print_corte_masivo'])->name('print_corte_masivo');

    Route::get('/caja-detalle/{detalle}/reimprimir', [CajaController::class, 'reimprimir'])->name('reimprimir');
    Route::post('/caja-detalle/{detalle}/anular', [CajaController::class, 'anular'])->name('anular');
});
