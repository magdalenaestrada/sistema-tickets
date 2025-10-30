<?php

use App\Http\Controllers\Empresa\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::prefix('empresas')->name('empresas.')->group(function () {
    Route::get('/', [EmpresaController::class, 'index'])->name('index');
    Route::get('/datatable', [EmpresaController::class, 'datatable'])->name('datatable');
    Route::get('/{empresa}', [EmpresaController::class, 'mostrar'])->name('mostrar'); // 👈 nuevo
    Route::post('/', [EmpresaController::class, 'guardar'])->name('guardar');
    Route::put('/{empresa}', [EmpresaController::class, 'actualizar'])->name('actualizar');
    Route::post('/{empresa}/activar', [EmpresaController::class, 'activar'])->name('activar');
    Route::post('/{empresa}/desactivar', [EmpresaController::class, 'desactivar'])->name('desactivar');
});
