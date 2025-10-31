<?php

use App\Http\Controllers\ListaController;

Route::prefix('listas')->name('listas.')->group(function () {
    Route::get('/', [ListaController::class, 'obtenerListas'])->name('all');
});
