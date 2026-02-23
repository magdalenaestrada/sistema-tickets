<?php

use App\Http\Controllers\TipoCuponController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'can:gestionar tipo cupones'])->prefix('tipo-cupones')->name('tipo-cupones.')->group(function () {
    Route::get('/', [TipoCuponController::class, 'index'])->name('index');
    Route::get('/datatable', [TipoCuponController::class, 'datatable'])->name('datatable');
    Route::get('/{tipo_cupon}', [TipoCuponController::class, 'mostrar'])->name('mostrar');
    Route::post('/', [TipoCuponController::class, 'guardar'])->name('guardar');
    Route::put('/{tipo_cupon}', [TipoCuponController::class, 'actualizar'])->name('actualizar');
    Route::post('/{tipo_cupon}/activar', [TipoCuponController::class, 'activar'])->name('activar');
    Route::post('/{tipo_cupon}/desactivar', [TipoCuponController::class, 'desactivar'])->name('desactivar');
    Route::delete('/{tipo_cupon}', [TipoCuponController::class, 'eliminar'])->name('eliminar');
});
