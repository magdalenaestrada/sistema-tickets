<?php

use App\Http\Controllers\PermisosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('permisos')->name('permisos.')->group(function () {
    Route::get('/', [PermisosController::class, 'index'])->name('index');
    Route::get('/datatable', [PermisosController::class, 'datatable'])->name('datatable');
    Route::get('/{rol}', [PermisosController::class, 'mostrar'])->name('mostrar');
    Route::post('/', [PermisosController::class, 'guardar'])->name('guardar');
    Route::put('/{rol}', [PermisosController::class, 'actualizar'])->name('actualizar');
    Route::post('/{rol}/activar', [PermisosController::class, 'activar'])->name('activar');
    Route::post('/{rol}/desactivar', [PermisosController::class, 'desactivar'])->name('desactivar');
    Route::delete('/{rol}', [PermisosController::class, 'eliminar'])->name('eliminar');
});
