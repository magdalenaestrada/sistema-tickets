<?php

namespace App\Services;

use App\Models\VentaPago;
use App\Models\Caja;
use App\Models\CajaDetalle;
use Illuminate\Support\Facades\Auth;

class PagoService
{
    public function registrarPagos($ventaId, $pagos, $servicio_model, $servicio_id)
    {
        foreach ($pagos as $pago) {

            VentaPago::create([
                'venta_id' => $ventaId,
                'metodo_pago_id' => $pago['metodo_pago_id'],
                'billetera_id' => $pago['billetera_id'] ?? null,
                'total' => $pago['total'],
                'estado' => 'PA',
                'fecha_pago' => now(),
                'fecha_creacion' => now(),
            ]);

            $caja = Caja::where('usuario_id', Auth::id())
                ->where('estado', 'A')
                ->first();

            if ($caja) {

                CajaDetalle::create([
                    'caja_id'       => $caja->id,
                    'subtipo_movimiento_caja_id' => 1, // INGRESO POR VENTA
                    'metodo_pago_id' => $pago['metodo_pago_id'],
                    'amount'        => $pago['total'],
                    'description'   => "Pago de venta #{$ventaId}",
                    'table_name'    => $servicio_model,
                    'table_id'      => $servicio_id,
                ]);
            }
        }
    }
}
