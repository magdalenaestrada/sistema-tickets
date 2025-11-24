<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function crearVenta($request, $servicio_model, $servicio_id)
    {
        $user = Auth::user();

        $comprobante = $this->generarSerieYNumero($request['tipo_documento_factura_id'], $user->sucursal_id);
        $request['serie'] = $comprobante['serie'];
        $request['numero'] = $comprobante['numero'];

        DB::beginTransaction();
        try {
            $venta = Venta::create([
                'tipo_servicio_id'  => $request['tipo_servicio_id'],
                'sucursal_id'       => $user->sucursal_id,
                'usuario_id'        => $user->id,
                'persona_id'        => $user->persona_id,
                'tipo_documento_factura_id' => $request['tipo_documento_factura_id'],
                'serie'             => $request['serie'],
                'numero'            => $request['numero'],
                'total'             => $request['total'],
                'fecha_emision'     => now(),
            ]);

            foreach ($request['detalles'] as $detalle) {

                if ($request['tipo_servicio_id'] == 1) {
                    $descripcion = $request['origen_nombre'] . ' → ' . $request['destino_nombre'];
                    $tipoServicio = 1;
                } else if ($request['tipo_servicio_id'] == 2) {
                    $descripcion = 'Encomienda: ' . $detalle['tipo_encomienda_nombre'] . ' - ' . $detalle['peso'] . 'kg';
                    $tipoServicio = 2;
                } else if ($request['tipo_servicio_id'] == 3) {
                    $descripcion = 'Equipaje extra - ' . $detalle['peso'] . 'kg';
                    $tipoServicio = 3;
                }

                $venta->detalles()->create([
                    'tipo_servicio_id' => $tipoServicio,
                    'descripcion'      => $descripcion,
                    'cantidad'         => 1,
                    'precio_venta'     => $detalle['costo'],
                    'total'            => $detalle['costo'],
                    'descuento'        => $detalle['descuento'] ?? 0,
                ]);
            }

            DB::commit();
            return [
                'venta'          => $venta,
                'servicio_model' => $servicio_model,
                'servicio_id'    => $servicio_id,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function generarSerieYNumero($tipo_documento_factura_id, $sucursal_id)
    {
        $series = [
            1 => "B001",
            2 => "F001",
        ];

        $prefijoSucursal = str_pad($sucursal_id, 2, "0", STR_PAD_LEFT);
        $serieBase = $series[$tipo_documento_factura_id] ?? "B001";
        $serie = $prefijoSucursal . $serieBase;

        $ultimoNumero = Venta::where('tipo_documento_factura_id', $tipo_documento_factura_id)
            ->where('sucursal_id', $sucursal_id)
            ->where('serie', $serie)
            ->max('numero');

        $numero = $ultimoNumero ? $ultimoNumero + 1 : 1;

        return [
            'serie'  => $serie,
            'numero' => $numero,
        ];
    }
}
