<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\Encomienda;
use App\Models\EncomiendaDetalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EncomiendaService
{
    protected $ventaService;
    protected $pagoService;

    public function __construct(VentaService $ventaService, PagoService $pagoService)
    {
        $this->ventaService = $ventaService;
        $this->pagoService = $pagoService;
    }

    public function crearEncomienda($request, $emisorId, $receptorId, $user_id)
    {
        return DB::transaction(function () use ($request, $emisorId, $receptorId, $user_id) {
            $encomienda = Encomienda::create([
                'origen' => $request->origen,
                'destino' => $request->destino,
                'usuario_id' => $user_id,
                'emisor_persona_id' => $emisorId,
                'receptor_persona_id' => $receptorId,
                'distrito_id' => $request->distrito_id,
                'total' => $request->total,
                'estado' => 'A',
                'pago_instantaneo' => $request->boolean('pago_instantaneo'),
                'fecha_creacion' => now(),
            ]);

            foreach ($request->detalles as $detalle) {
                EncomiendaDetalle::create([
                    'encomienda_id' => $encomienda->id,
                    'tipo_encomienda_id' => $detalle['tipo_encomienda_id'],
                    'descripcion' => $detalle['descripcion'],
                    'peso' => $detalle['peso'],
                    'costo' => $detalle['costo'],
                ]);
            }

            if ($request->boolean('pago_instantaneo')) {
                $ventaData = $this->ventaService->crearVenta(
                    $request,
                    Encomienda::class,
                    $encomienda->id
                );
                $encomienda->venta_id = $ventaData['venta']->id;
                $encomienda->save();

                $this->pagoService->registrarPagos(
                    $ventaData['venta']->id,
                    $request->pagos ?? [],
                    $ventaData['servicio_model'],
                    $ventaData['servicio_id']
                );
            }
            return $encomienda;
        });
    }
    public function actualizarEncomienda(
        $request,
        Encomienda $encomienda,
        int $emisorId,
        int $receptorId
    ) {
        return DB::transaction(function () use ($request, $encomienda, $emisorId, $receptorId) {

            $encomienda->update([
                'origen' => $request->origen,
                'destino' => $request->destino,
                'emisor_persona_id' => $emisorId,
                'receptor_persona_id' => $receptorId,
                'distrito_id' => $request->distrito_id,
                'pago_instantaneo' => $request->boolean('pago_instantaneo'),
                'total' => $request->total,
            ]);

            EncomiendaDetalle::where('encomienda_id', $encomienda->id)->delete();

            foreach ($request->detalles as $detalle) {
                EncomiendaDetalle::create([
                    'encomienda_id' => $encomienda->id,
                    'tipo_encomienda_id' => $detalle['tipo_encomienda_id'],
                    'descripcion' => $detalle['descripcion'],
                    'peso' => $detalle['peso'],
                    'costo' => $detalle['costo'],
                ]);
            }
            $antesTeniaPago = $encomienda->venta_id !== null;
            $registrarPago  = $request->boolean('pago_instantaneo');

            if ($registrarPago) {

                if ($antesTeniaPago) {
                    $ventaData = $this->ventaService->reemplazarVenta(
                        $encomienda->venta,
                        $request,
                        Encomienda::class,
                        $encomienda->id
                    );
                } else {
                    $ventaData = $this->ventaService->crearVenta(
                        $request,
                        Encomienda::class,
                        $encomienda->id
                    );
                }

                $encomienda->venta_id = $ventaData['venta']->id;
                $encomienda->save();

                $ventaData['venta']->pagos()->delete();

                $this->pagoService->registrarPagos(
                    $ventaData['venta']->id,
                    $request->pagos ?? [],
                    $ventaData['servicio_model'],
                    $ventaData['servicio_id']
                );
            }

            if (!$registrarPago && $antesTeniaPago) {

                $encomienda->venta->pagos()->delete();
                $encomienda->venta->detalles()->delete();
                $encomienda->venta()->delete();

                $encomienda->venta_id = null;
                $encomienda->save();
            }
            return $encomienda;
        });
    }
}
