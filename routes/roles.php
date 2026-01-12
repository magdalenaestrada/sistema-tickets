<?php

use App\Http\Controllers\RolesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RolesController::class, 'index'])->name('index');
    Route::get('/datatable', [RolesController::class, 'datatable'])->name('datatable');
    Route::get('/{rol}', [RolesController::class, 'mostrar'])->name('mostrar');
    Route::post('/', [RolesController::class, 'guardar'])->name('guardar');
    Route::put('/{rol}', [RolesController::class, 'actualizar'])->name('actualizar');
    Route::post('/{rol}/activar', [RolesController::class, 'activar'])->name('activar');
    Route::post('/{rol}/desactivar', [RolesController::class, 'desactivar'])->name('desactivar');
    Route::delete('/{rol}', [RolesController::class, 'eliminar'])->name('eliminar');
    Route::get('/{rol}/permisos', [RolesController::class, 'permisos'])->name('permisos');
    Route::post('/guardar-permisos', [RolesController::class, 'guardarPermisos'])->name('guardarPermisos');
});
