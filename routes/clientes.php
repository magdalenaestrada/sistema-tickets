<?php

use App\Http\Controllers\ClientesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('clientes')->name('clientes.')->group(function () {
    Route::get('/', [ClientesController::class, 'index'])->name('index');
    Route::get('/datatable', [ClientesController::class, 'datatable'])->name('datatable');
    Route::post('/', [ClientesController::class, 'store'])->name('store');
    Route::get('/{cliente}/edit', [ClientesController::class, 'edit'])->name('edit');
    Route::put('/{cliente}', [ClientesController::class, 'update'])->name('update');
    Route::delete('/{cliente}', [ClientesController::class, 'destroy'])->name('destroy');
});
