<?php

use App\Http\Controllers\DescuentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('descuentos')->name('descuentos.')->group(function () {
    Route::get('/', [DescuentoController::class, 'index'])->name('index');
    Route::get('/datatable', [DescuentoController::class, 'datatable'])->name('datatable');
    Route::get('/{descuento}', [DescuentoController::class, 'mostrar'])->name('mostrar');
    Route::post('/', [DescuentoController::class, 'guardar'])->name('guardar');
    Route::delete('/{descuento}', [DescuentoController::class, 'eliminar'])->name('eliminar');
});
