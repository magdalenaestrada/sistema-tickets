<?php

namespace App\Services;

use App\Models\CajaDetalle;
use App\Models\Encomienda;
use App\Models\EncomiendaDetalle;
use App\Models\PasajeSobreEquipaje;
use App\Models\Persona;
use App\Models\Pueblito;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
        $data = DB::transaction(function () use ($request, $emisorId, $receptorId, $user_id) {
            $encomienda = Encomienda::create([
                'origen_pueblito_id' => $request->origen_pueblito_id,
                'destino_pueblito_id' => $request->destino_pueblito_id,
                'usuario_id' => $user_id,
                'emisor_persona_id' => $emisorId,
                'receptor_persona_id' => $receptorId,
                'distrito_id' => $request->distrito_id,
                'total' => $request->total,
                'estado' => 'SA',
                'pago_instantaneo' => $request->boolean('pago_instantaneo'),
                'transbordo' => $request->boolean('transbordo_incuyo'),
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

            $ventaData = null;

            $ventaService = app(VentaService::class);
            $tipo_documento_persona =  $request->tipo_doc_sunat == 1 ? 2 : 1;
            $personaFacturacion = Persona::updateOrCreate(
                ['documento' => $request->numero_documento_id],
                [
                    'tipo_documento_id' => $tipo_documento_persona ?? 1,
                    'nombres' => $request->razon_social ?: 'CLIENTE VARIOS',
                    'direccion' => $request->direccion,
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $detalles = [];

            foreach ($request->detalles as $detalle) {
                $detalles[] = [
                    'descripcion' => $detalle['descripcion'],
                    'costo' => $detalle['costo'],
                    'descuento' => 0,
                ];
            }
            $origenNombre = Pueblito::find($request->origen_pueblito_id)?->descripcion;
            $destinoNombre = Pueblito::find($request->destino_pueblito_id)?->descripcion;

            $ventaData = $ventaService->crearVenta(
                new Request([
                    'tipo_servicio_id' => 1,
                    'tipo_documento_factura_id' => $request->tipo_doc_sunat,
                    'numero_documento_id' => $personaFacturacion->documento,
                    'razon_social' => $personaFacturacion->nombres,
                    'total' => $request->total,
                    'caja_id' => $request->caja_id,
                    'detalles' => $detalles,
                    'origen_nombre' => $origenNombre,
                    'destino_nombre' => $destinoNombre,
                ]),
                Encomienda::class,
                null
            );

            $venta = $ventaData['venta'];
            $encomienda->update([
                'venta_id' => $venta->id
            ]);
            $pagos = $request->pagos ?? [];

            $sumaPagos = collect($pagos)->sum(function ($pago) {
                return (float) $pago['total'];
            });

            if (round($sumaPagos, 2) !== round((float)$venta->total, 2)) {
                throw ValidationException::withMessages([
                    'pagos' => 'La suma de pagos no coincide con el total.',
                ]);
            }

            foreach ($pagos as $pago) {
                CajaDetalle::create([
                    'caja_id' => $request->caja_id,
                    'subtipo_movimiento_caja_id' => 1, // venta
                    'metodo_pago_id' => $pago['metodo_pago_id'],
                    'amount' => $pago['total'],
                    'venta_id' => $venta->id,
                    'description' => "Venta de encomienda #{$venta->id}",
                    'anulado' => false,
                    'billetera_digital_id' => $pago['billetera_id'] ?? null,
                ]);
            }

            $emision = $ventaService->emitirVenta($venta);
            if ($request->boolean('sobrequipaje')) {
                PasajeSobreEquipaje::create([
                    'pasaje_id'     => $request->pasaje_id,
                    'encomienda_id' => $encomienda->id,
                ]);

            }

            return [
                'encomienda' => $encomienda,
                'ventaData' => $ventaData,
            ];
        });

        return $data['encomienda']->fresh();
    }

    public function actualizarEncomienda(
        $request,
        Encomienda $encomienda,
        int $emisorId,
        int $receptorId
    ) {
        $data = DB::transaction(function () use ($request, $encomienda, $emisorId, $receptorId) {
            $ventaAnterior = $encomienda->venta;

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

            $antesTeniaPago = $ventaAnterior !== null;
            $registrarPago = $request->boolean('pago_instantaneo');
            $ventaNuevaData = null;

            if ($registrarPago) {
                if ($antesTeniaPago) {
                    $this->ventaService->anularVenta($ventaAnterior);
                }

                $ventaNuevaData = $this->ventaService->crearVenta(
                    $request,
                    Encomienda::class,
                    $encomienda->id
                );

                $encomienda->venta_id = $ventaNuevaData['venta']->id;
                $encomienda->save();

                $this->pagoService->registrarPagos(
                    $ventaNuevaData['venta']->id,
                    $request->pagos ?? [],
                    $ventaNuevaData['servicio_model'],
                    $ventaNuevaData['servicio_id']
                );
            }

            if (!$registrarPago && $antesTeniaPago) {
                $this->ventaService->anularVenta($ventaAnterior);

                $encomienda->venta_id = null;
                $encomienda->save();
            }

            return [
                'encomienda' => $encomienda,
                'ventaNuevaData' => $ventaNuevaData,
            ];
        });

        if ($data['ventaNuevaData']) {
            $this->ventaService->emitirVenta($data['ventaNuevaData']['venta']);
        }

        return $data['encomienda']->fresh();
    }
}
