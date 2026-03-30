<?php

use App\Http\Controllers\SalidaController;

Route::middleware(['auth'])->prefix('salidas')->name('salidas.')->group(function () {
    Route::get('/', [SalidaController::class, 'index'])->name('index');
    Route::get('/datatable', [SalidaController::class, 'datatable'])->name('datatable');
    Route::post('/', [SalidaController::class, 'store'])->name('store');
    Route::post('/generar', [SalidaController::class, 'generar'])->name('generar');
    Route::get('/{id}', [SalidaController::class, 'show'])->name('show');
    Route::put('/{id}', [SalidaController::class, 'update'])->name('update');
    Route::delete('/{id}', [SalidaController::class, 'destroy'])->name('destroy');
});
