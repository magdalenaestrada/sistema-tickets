<?php

namespace App\Services;

use App\Models\Encomienda;
use App\Models\EncomiendaDetalle;
use Illuminate\Http\Request;
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
        $data = DB::transaction(function () use ($request, $emisorId, $receptorId, $user_id) {
            $encomienda = Encomienda::create([
                'origen_pueblito_id' => $request->origen_pueblito_id,
                'destino_pueblito_id' => $request->destino_pueblito_id,
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

            $ventaData = null;

            $ventaService = app(VentaService::class);
            $pagoService = app(PagoService::class);

            $ventaData = $ventaService->crearVenta(
                new Request([
                    'tipo_servicio_id' => 1,
                    'tipo_documento_factura_id' => $request->tipo_doc_sunat,
                    'numero_documento_id' => $personaFacturacion->documento,
                    'razon_social' => $personaFacturacion->nombres,
                    'total' => $totalVenta,
                    'caja_id' => $request->caja_id,
                    'detalles' => $detalles,
                    'origen_nombre' => $salida->horario->ruta->puntos->first()?->sucursal?->nombre_comercial,
                    'destino_nombre' => $salida->horario->ruta->puntos->last()?->sucursal?->nombre_comercial,
                ]),
                Pasaje::class,
                null
            );

            $venta = $ventaData['venta'];

            $pagos = [];

            if ((float) $request->pago_efectivo > 0) {
                $pagos[] = [
                    'metodo_pago_id' => 1,
                    'total' => (float) $request->pago_efectivo,
                ];
            }

            $pagoCuentaDigital =
                (float) $request->pago_yape +
                (float) $request->pago_plin +
                (float) $request->pago_transferencia +
                (float) $request->pago_tarjeta;

            if ($pagoCuentaDigital > 0) {
                $pagos[] = [
                    'metodo_pago_id' => 2,
                    'billetera_id' => $request->billetera_id,
                    'total' => $pagoCuentaDigital,
                ];
            }

            $sumaPagos = collect($pagos)->sum('total');

            if (round($sumaPagos, 2) !== round($totalVenta, 2) && $accion === 'vender') {
                throw ValidationException::withMessages([
                    'pago_efectivo' => 'La suma de pagos no coincide con el total de la venta.',
                ]);
            }


            foreach ($pagos as $pago) {
                CajaDetalle::create([
                    'caja_id' => $request->caja_id,
                    'subtipo_movimiento_caja_id' => 1, // venta
                    'metodo_pago_id' => $pago['metodo_pago_id'],
                    'amount' => $pago['total'],
                    'description' => "Venta de pasaje #{$venta->id}",
                    'anulado' => false,
                    'billetera_digital_id' => $pago['billetera_id'] ?? null,
                ]);
            }

            $pagoService->registrarPagos(
                $venta->id,
                $pagos,
                Venta::class,
                $venta->id
            );

            $emision = $ventaService->emitirVenta($venta);

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
