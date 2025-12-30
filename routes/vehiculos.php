<?php

use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('vehiculos')->name('vehiculos.')->group(function () {
    Route::get('/', [VehiculoController::class, 'index'])->name('index');
    Route::get('/datatable', [VehiculoController::class, 'datatable'])->name('datatable');
    Route::get('/filtrar', [VehiculoController::class, 'filtrar'])->name('filtrar');
    Route::get('/{vehiculo}', [VehiculoController::class, 'mostrar'])->name('mostrar');
    Route::post('/', [VehiculoController::class, 'guardar'])->name('guardar');
    Route::put('/{vehiculo}', [VehiculoController::class, 'actualizar'])->name('actualizar');
    Route::post('/{vehiculo}/activar', [VehiculoController::class, 'activar'])->name('activar');
    Route::post('/{vehiculo}/desactivar', [VehiculoController::class, 'desactivar'])->name('desactivar');
    Route::post('/{vehiculo}/mantenimiento', [VehiculoController::class, 'mantenimiento'])->name('mantenimiento');
    Route::delete('/{vehiculo}', [VehiculoController::class, 'eliminar'])->name('eliminar');
});
