<?php

use App\Http\Controllers\BuscarDocumentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('buscar')->name('buscar.')->group(function () {
    Route::get('/', [BuscarDocumentoController::class, 'buscarDocumento'])->name('buscar');
});
