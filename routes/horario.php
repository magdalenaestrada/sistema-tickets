<?php

use App\Http\Controllers\HorarioController;

Route::middleware(['auth'])->prefix('horarios')->name('horarios.')->group(function () {
    Route::get('/', [HorarioController::class, 'index'])->name('index');
    Route::get('/datatable', [HorarioController::class, 'datatable'])->name('datatable');
    Route::post('/', [HorarioController::class, 'store'])->name('store');
    Route::get('/{id}', [HorarioController::class, 'show'])->name('show');
    Route::put('/{id}', [HorarioController::class, 'update'])->name('update');
    Route::delete('/{id}', [HorarioController::class, 'destroy'])->name('destroy');
});
