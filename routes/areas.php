<?php

use App\Http\Controllers\AreaController;
use Illuminate\Support\Facades\Route;

Route::prefix('areas')->name('areas.')->group(function () {
    Route::get('/', [AreaController::class, 'index'])->name('index');
    Route::get('/datatable', [AreaController::class, 'datatable'])->name('datatable');
    Route::get('/{area}', [AreaController::class, 'mostrar'])->name('mostrar'); // 👈 nuevo
    Route::post('/', [AreaController::class, 'guardar'])->name('guardar');
    Route::put('/{area}', [AreaController::class, 'actualizar'])->name('actualizar');
    Route::post('/{area}/activar', [AreaController::class, 'activar'])->name('activar');
    Route::post('/{area}/desactivar', [AreaController::class, 'desactivar'])->name('desactivar');
    Route::delete('/{area}', [AreaController::class, 'eliminar'])->name('eliminar');

});
