<?php

use App\Http\Controllers\CargoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('cargos')->name('cargos.')->group(function () {
    Route::get('/', [CargoController::class, 'index'])->name('index');
    Route::get('/datatable', [CargoController::class, 'datatable'])->name('datatable');
    Route::get('/{cargo}', [CargoController::class, 'mostrar'])->name('mostrar'); // 👈 nuevo
    Route::post('/', [CargoController::class, 'guardar'])->name('guardar');
    Route::put('/{cargo}', [CargoController::class, 'actualizar'])->name('actualizar');
    Route::post('/{cargo}/activar', [CargoController::class, 'activar'])->name('activar');
    Route::post('/{cargo}/desactivar', [CargoController::class, 'desactivar'])->name('desactivar');
    Route::delete('/{cargo}', [CargoController::class, 'eliminar'])->name('eliminar');

});
