<?php

namespace App\Http\Controllers;

use App\Models\Pasaje;
use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\MetodoPago;
use App\Models\BilleteraDigital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PasajeController extends Controller
{
    public function index()
    {
        $hoy = now('America/Lima')->format('Y-m-d');

        $salidas = Salida::with([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ])
            ->where('estado', 'activo')
            ->orderBy('fecha_salida')
            ->get();

        $sucursales = Sucursal::where('estado', 'A')
            ->orderBy('nombre_comercial')
            ->get();

        return view('pasajes.index', compact('hoy', 'salidas', 'sucursales'));
    }

    public function asientos(Salida $salida, Request $request)
    {
        $request->validate([
            'origen_id' => 'nullable|exists:sucursales,id',
            'destino_id' => 'nullable|exists:sucursales,id',
        ]);

        $origenId = $request->origen_id;
        $destinoId = $request->destino_id;

        if ($salida->horario->tipo_viaje_id == 2) {
            if (!$origenId || !$destinoId) {
                return response()->json([
                    'message' => 'Origen y destino son obligatorios para viaje por tramo.'
                ], 422);
            }

            $asientos = $salida->asientosDisponibles($origenId, $destinoId);
            $precio = $salida->calcularCostoPorTramos($origenId, $destinoId);
        } else {
            $ruta = $salida->horario->ruta;
            $puntos = $ruta->puntos()->orderBy('orden')->get();

            $origenId = $puntos->first()?->sucursal_id;
            $destinoId = $puntos->last()?->sucursal_id;

            $asientos = $salida->asientosDisponibles($origenId, $destinoId);
            $precio = $salida->horario->costo_base;
        }

        $svg = file_get_contents(storage_path('app/public/' . $salida->horario->tipo_vehiculo->ruta_svg));
        $svg = preg_replace('/<\?xml.*?\?>/is', '', $svg);
        $svg = preg_replace('/<!DOCTYPE.*?>/is', '', $svg);

        return response()->json([
            'asientos' => $asientos,
            'precio' => $precio,
            'svg' => $svg,
        ]);
    }

    public function buscarReservado(Request $request)
    {
        $request->validate([
            'salida_id' => 'required|exists:salidas,id',
            'asiento' => 'required|integer|min:1',
        ]);

        $pasaje = Pasaje::where('salida_id', $request->salida_id)
            ->where('asiento_numero', $request->asiento)
            ->where('estado', 'R')
            ->first();

        if (!$pasaje) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pasaje_id' => $pasaje->id
        ]);
    }

    public function verificarPromocion(Request $request)
    {
        $request->validate([
            'salida_id' => 'required|exists:salidas,id',
            'origen_id' => 'required|exists:sucursales,id',
            'destino_id' => 'required|exists:sucursales,id',
        ]);

        $cantidad = Pasaje::where('salida_id', $request->salida_id)
            ->where('origen_sucursal_id', $request->origen_id)
            ->where('destino_sucursal_id', $request->destino_id)
            ->whereIn('estado', ['V', 'F'])
            ->count();

        $numeroActual = $cantidad + 1;
        $esGratis = $numeroActual % 10 === 0;

        return response()->json([
            'numero_actual' => $numeroActual,
            'es_gratis' => $esGratis,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'salida_id' => 'required|exists:salidas,id',
            'origen_id' => 'required|exists:sucursales,id',
            'destino_id' => 'required|exists:sucursales,id',
            'asiento_numero' => 'required|integer|min:1',
            'persona_id' => 'nullable|exists:personas,id',
            'descuento_id' => 'nullable|exists:descuentos,id',
        ]);

        DB::beginTransaction();

        try {
            $salida = Salida::with([
                'horario.tipo_vehiculo',
                'horario.ruta.puntos',
                'horario.ruta.tramos.origen',
                'horario.ruta.tramos.destino',
            ])->findOrFail($request->salida_id);

            $asientos = $salida->asientosDisponibles($request->origen_id, $request->destino_id);

            if (($asientos[$request->asiento_numero] ?? 'ocupado') !== 'libre') {
                return response()->json([
                    'ok' => false,
                    'message' => 'El asiento ya está ocupado para ese tramo.'
                ], 422);
            }

            $tramos = $salida->obtenerTramosDeViaje($request->origen_id, $request->destino_id);

            if ($tramos->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo determinar el tramo del viaje.'
                ], 422);
            }

            $cantidad = Pasaje::where('salida_id', $request->salida_id)
                ->where('origen_sucursal_id', $request->origen_id)
                ->where('destino_sucursal_id', $request->destino_id)
                ->whereIn('estado', ['V', 'F'])
                ->lockForUpdate()
                ->count();

            $numeroActual = $cantidad + 1;
            $esPromo10 = $numeroActual % 10 === 0;

            $precioNormal = $tramos->sum('costo_tramo');
            $precioCobrado = $precioNormal;

            if ($request->descuento_id == 1) {
                if (!$esPromo10) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'El descuento promocional solo aplica al pasaje número 10 de ese mismo tramo.'
                    ], 422);
                }

                $precioCobrado = 0;
            }

            $pasaje = Pasaje::create([
                'usuario_id' => Auth::id(),
                'persona_id' => $request->persona_id,
                'salida_id' => $salida->id,
                'origen_sucursal_id' => $request->origen_id,
                'destino_sucursal_id' => $request->destino_id,
                'asiento_numero' => $request->asiento_numero,
                'estado' => 'V',
                'es_promocion' => $request->descuento_id == 1,
                'precio_cobrado' => $precioCobrado,
                'fecha_creacion' => now(),
            ]);

            $pasaje->tramos()->attach($tramos->pluck('id')->toArray());

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Pasaje vendido correctamente',
                'precio_cobrado' => $precioCobrado,
                'numero_actual' => $numeroActual,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function show(Pasaje $pasaje)
    {
        $pasaje->load([
            'persona',
            'salida.horario.ruta.puntos.sucursal',
            'salida.horario.tipo_vehiculo',
            'venta.pagos.metodoPago',
        ]);

        return response()->json([
            'id' => $pasaje->id,
            'estado' => $pasaje->estado,
            'asiento' => $pasaje->asiento_numero,
            'fecha' => $pasaje->salida?->fecha_salida?->format('Y-m-d'),
            'hora' => $pasaje->salida?->horario?->hora_formateada,
            'ruta' => $pasaje->salida?->horario?->ruta?->nombre,
            'origen' => $pasaje->origen?->nombre_comercial,
            'destino' => $pasaje->destino?->nombre_comercial,
            'pasajero' => $pasaje->persona ? [
                'documento' => $pasaje->persona->documento,
                'nombres' => $pasaje->persona->nombres,
                'apellidos' => $pasaje->persona->apellidos,
                'celular' => $pasaje->persona->celular,
            ] : null,
            'pagos' => $pasaje->venta?->pagos ?? [],
        ]);
    }

    public function editar(Pasaje $pasaje)
    {
        $pasaje->load([
            'persona',
            'salida.horario.ruta.puntos.sucursal',
            'salida.horario.tipo_vehiculo',
            'venta.pagos.billetera',
            'venta.pagos.metodoPago',
            'venta.persona',
            'venta.tipoDocumentoFactura',
        ]);

        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $metodos_pago = MetodoPago::all();
        $billeteras_digitales = BilleteraDigital::all();
        $sucursales = Sucursal::where('estado', 'A')->orderBy('nombre_comercial')->get();

        return view('pasajes.editar', compact(
            'pasaje',
            'tipos_documentos',
            'tipos_documentos_facturas',
            'metodos_pago',
            'billeteras_digitales',
            'sucursales'
        ));
    }

    public function abordo(Pasaje $pasaje)
    {
        if ($pasaje->estado !== 'V') {
            return response()->json([
                'success' => false,
                'message' => 'Solo pasajes vendidos pueden marcarse como abordó.'
            ], 422);
        }

        $pasaje->update([
            'estado' => 'F',
            'fecha_inactivacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasajero marcado como abordó correctamente.'
        ]);
    }

    public function noAbordo(Pasaje $pasaje)
    {
        if ($pasaje->estado !== 'V') {
            return response()->json([
                'success' => false,
                'message' => 'Solo pasajes vendidos pueden marcarse como no abordó.'
            ], 422);
        }

        $pasaje->update([
            'estado' => 'X',
            'fecha_inactivacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasajero marcado como no abordó.'
        ]);
    }

    public function cambiarHorario(Pasaje $pasaje)
    {
        if (!in_array($pasaje->estado, ['R', 'V'])) {
            return redirect()->back()->withErrors('Solo se puede cambiar horario a pasajes reservados o vendidos.');
        }

        $pasaje->load([
            'persona',
            'salida.horario.ruta',
            'salida.horario.tipo_vehiculo',
        ]);

        $salidas = Salida::with([
            'horario.ruta',
            'horario.tipo_vehiculo',
        ])
            ->whereHas('horario', function ($q) use ($pasaje) {
                $q->where('ruta_id', $pasaje->salida->horario->ruta_id);
            })
            ->whereDate('fecha_salida', '>=', now()->toDateString())
            ->orderBy('fecha_salida')
            ->get();

        return view('pasajes.cambiar-horario', compact('pasaje', 'salidas'));
    }

    public function actualizarHorario(Request $request, Pasaje $pasaje)
    {
        $request->validate([
            'nueva_salida_id' => 'required|exists:salidas,id',
            'nuevo_asiento_numero' => 'required|integer|min:1',
        ]);

        if (!in_array($pasaje->estado, ['R', 'V'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede cambiar horario a pasajes reservados o vendidos.'
            ], 422);
        }

        if (!$pasaje->salida_id) {
            return response()->json([
                'success' => false,
                'message' => 'El pasaje no tiene una salida asociada.'
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $pasaje) {

                $pasaje->load([
                    'salida.horario.ruta',
                    'tramos',
                ]);

                $nuevaSalida = Salida::with([
                    'horario.tipo_vehiculo',
                    'horario.ruta.puntos.sucursal',
                    'horario.ruta.tramos.origen',
                    'horario.ruta.tramos.destino',
                ])->findOrFail($request->nueva_salida_id);

                $rutaActualId = $pasaje->salida?->horario?->ruta_id;
                $nuevaRutaId = $nuevaSalida->horario?->ruta_id;

                if (!$rutaActualId || !$nuevaRutaId || $rutaActualId != $nuevaRutaId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Solo puedes cambiar el pasaje a una salida de la misma ruta.'
                    ], 422);
                }

                if (!$pasaje->origen_sucursal_id || !$pasaje->destino_sucursal_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El pasaje no tiene origen y destino definidos.'
                    ], 422);
                }

                $asientos = $nuevaSalida->asientosDisponibles(
                    $pasaje->origen_sucursal_id,
                    $pasaje->destino_sucursal_id
                );

                if (empty($asientos)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo calcular la disponibilidad para el nuevo viaje.'
                    ], 422);
                }

                if (($asientos[$request->nuevo_asiento_numero] ?? 'ocupado') !== 'libre') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El asiento seleccionado ya no está disponible.'
                    ], 422);
                }

                $tramos = $nuevaSalida->obtenerTramosDeViaje(
                    $pasaje->origen_sucursal_id,
                    $pasaje->destino_sucursal_id
                );

                if ($tramos->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudieron determinar los tramos del nuevo viaje.'
                    ], 422);
                }

                $pasaje->update([
                    'salida_id' => $nuevaSalida->id,
                    'asiento_numero' => $request->nuevo_asiento_numero,
                    'fecha_inactivacion' => null,
                ]);

                $pasaje->tramos()->sync($tramos->pluck('id')->toArray());

                return response()->json([
                    'success' => true,
                    'message' => 'Salida y asiento actualizados correctamente.',
                    'data' => [
                        'pasaje_id' => $pasaje->id,
                        'salida_id' => $nuevaSalida->id,
                        'asiento_numero' => $pasaje->asiento_numero,
                        'fecha_salida' => optional($nuevaSalida->fecha_salida)->format('Y-m-d'),
                        'hora_salida' => $nuevaSalida->horario?->hora_formateada,
                    ]
                ]);
            });
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar la salida: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancelar(Pasaje $pasaje)
    {
        if (!in_array($pasaje->estado, ['R', 'V'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden cancelar pasajes reservados o vendidos.'
            ], 422);
        }

        $pasaje->update([
            'estado' => 'X',
            'fecha_inactivacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasaje cancelado correctamente.',
        ]);
    }
}
