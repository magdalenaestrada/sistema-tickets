<?php

use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardVendedorController;
use App\Http\Controllers\PasajeController;
use App\Http\Controllers\ProfileController;
use App\Models\Caja;
use App\Models\Venta;
use App\Services\FixService;
use App\Services\VentaService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->hasRole('Administrador')
            ? redirect()->route('dashboard.admin')
            : redirect()->route('dashboard.vendedor');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'caja.abierta'])->group(function () {

    Route::get('/pasajes', [PasajeController::class, 'vender'])
        ->name('pasajes.vender');

});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/vendedor', DashboardVendedorController::class)
        ->name('dashboard.vendedor');

    Route::get('/dashboard/admin', DashboardAdminController::class)
        ->name('dashboard.admin');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('factura', function () {
    $service = new VentaService;
    $service->prueba();
});

Route::get('fix', function () {
    $service = new FixService();
    //$service->anular('BBB1', '14', '15');
    //$service->fix(14);
});

Route::get('/caja/verificar', function () {
    $user = Auth::user();

    $tieneCaja = Caja::where('sucursal_id', $user->sucursal_id)
        ->where('estado', 'A')
        ->exists();

    return response()->json(['abierta' => $tieneCaja]);
})->middleware('auth')->name('caja.verificar');



require __DIR__ . '/api.php';
require __DIR__ . '/areas.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/buscar.php';
require __DIR__ . '/cargos.php';
require __DIR__ . '/console.php';
require __DIR__ . '/empleados.php';
require __DIR__ . '/empresas.php';
require __DIR__ . '/encomiendas.php';
require __DIR__ . '/eventos.php';
require __DIR__ . '/horario.php';
require __DIR__ . '/listas.php';
require __DIR__ . '/sucursales.php';
require __DIR__ . '/tipo-encomienda.php';
require __DIR__ . '/ubigeos.php';
require __DIR__ . '/vehiculos.php';
require __DIR__ . '/caja.php';
require __DIR__ . '/asignaciones.php';
require __DIR__ . '/facturacion.php';
require __DIR__ . '/pasaje.php';
require __DIR__ . '/permisos.php';
require __DIR__ . '/descuentos.php';
require __DIR__ . '/roles.php';
require __DIR__ . '/rutas.php';
require __DIR__ . '/reportes.php';
require __DIR__ . '/salidas.php';
require __DIR__ . '/series.php';
require __DIR__ . '/clientes.php';
require __DIR__ . '/usuarios.php';
require __DIR__ . '/ventas.php';
require __DIR__ . '/tipo_cupones.php';
require __DIR__ . '/encomienda-asignacion.php';
