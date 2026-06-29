<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaPago;
use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\SubtipoMovimientoCaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PagoService
{
    public function registrarPagos(Venta $venta, array $pagos): void
    {
        $userId = Auth::id();

        $caja = Caja::where('usuario_id', $userId)
            ->whereIn('estado', ['A', 'abierta'])
            ->latest('fecha_creacion')
            ->first();

        if (!$caja) {
            throw new Exception('No existe una caja abierta para registrar el pago.');
        }

        $subtipoVenta = SubtipoMovimientoCaja::find(1);

        if (!$subtipoVenta) {
            throw new Exception('No existe el subtipo de movimiento de caja para ingreso por venta.');
        }

        foreach ($pagos as $pago) {
            $monto = (float) ($pago['total'] ?? 0);

            if ($monto <= 0) {
                continue;
            }

            VentaPago::create([
                'venta_id'        => $ventaId,
                'metodo_pago_id'  => $pago['metodo_pago_id'],
                'billetera_id'    => $pago['billetera_id'] ?? null,
                'total'           => $monto,
                'estado'          => 'PA',
                'fecha_pago'      => now(),
                'fecha_creacion'  => now(),
            ]);

            CajaDetalle::create([
                'caja_id'                     => $caja->id,
                'subtipo_movimiento_caja_id'  => $subtipoVenta->id,
                'metodo_pago_id'              => $pago['metodo_pago_id'],
                'amount'                      => $monto,
                'description'                 => "Pago de venta #{$ventaId}",
                'table_name'                  => $servicio_model,
                'table_id'                    => $servicio_id,
                'anulado'                     => false,
            ]);
        }
    }
}
