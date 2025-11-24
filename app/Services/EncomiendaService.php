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
                'distrito_id' => $request->distrito_id ?? 1,
                'total' => $request->total,
                'estado' => 'A',
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

            return $encomienda;
        });
    }
}
