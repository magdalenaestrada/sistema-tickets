<?php

use App\Http\Controllers\Sucursal\SucursalController;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('sucursales')->name('sucursales.')->group(function () {
    Route::get('/lista', [SucursalController::class, 'lista'])->name('lista');
    Route::get('/{empresa_id}', [SucursalController::class, 'index'])->name('index');
    Route::get('/{empresa_id}/datatable', [SucursalController::class, 'datatable'])->name('datatable');
    Route::post('/', [SucursalController::class, 'guardar'])->name('guardar');
    Route::get('/show/{sucursal}', [SucursalController::class, 'show'])->name('show');
    Route::put('/{sucursal}', [SucursalController::class, 'actualizar'])->name('actualizar');
    Route::get('/detalle/{id}', [SucursalController::class, 'show'])->name('detalle');
    Route::patch('/{sucursal}/activar', [SucursalController::class, 'activar'])
        ->name('activar');
    Route::patch('/{sucursal}/desactivar', [SucursalController::class, 'desactivar'])
        ->name('desactivar');
});
