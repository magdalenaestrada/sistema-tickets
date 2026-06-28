<?php

use App\Http\Controllers\PasajeController;

Route::middleware(['auth'])->prefix('pasajes')->name('pasajes.')->group(function () {

    Route::get('/', [PasajeController::class, 'index'])->name('index');
    Route::get('/listar', [PasajeController::class, 'listarPasajes'])->name('listar');
    Route::get('/asientos/{salida}', [PasajeController::class, 'asientos'])->name('asientos');
    Route::get('/buscar', [PasajeController::class, 'buscarReservado'])->name('buscar');
    Route::post('/verificar-promocion', [PasajeController::class, 'verificarPromocion'])->name('verificar_promocion');
    Route::post('/', [PasajeController::class, 'store'])->name('store');
    Route::get('/vender', [PasajeController::class, 'vender'])->name('vender');
    Route::get('/{pasaje}', [PasajeController::class, 'show'])->name('show');
    Route::get('/{pasaje}/editar', [PasajeController::class, 'editar'])->name('editar');
    Route::get('/{pasaje}/ticket', [PasajeController::class, 'ticket'])->name('ticket');
    Route::post('/{pasaje}/actualizar-venta', [PasajeController::class, 'actualizarVentaReserva'])
        ->name('actualizar_venta');
    Route::post('/{pasaje}/abordo', [PasajeController::class, 'abordo'])->name('abordo');
    Route::post('/{pasaje}/no-abordo', [PasajeController::class, 'noAbordo'])->name('noAbordo');
    Route::get('/{pasaje}/cambiar-horario', [PasajeController::class, 'cambiarHorario'])->name('cambiarHorario');
    Route::put('/{pasaje}/actualizar-horario', [PasajeController::class, 'actualizarHorario'])->name('actualizarHorario');
    Route::get('/{pasaje}/sobre-equipaje', [PasajeController::class, 'showSobreEquipaje'])->name('sobre-equipaje');
});
