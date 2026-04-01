<?php

namespace App\Http\Controllers;

use App\Models\Pasaje;
use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\MetodoPago;
use App\Models\BilleteraDigital;
use App\Models\Descuento;
use App\Models\Persona;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
            ->whereIn('estado', ['activo', 'programado'])
            ->whereDate('fecha_salida', '>=', now()->toDateString())
            ->orderBy('fecha_salida')
            ->get()
            ->map(function ($salida) {
                $ruta = $salida->horario->ruta;
                $puntos = $ruta->puntos->sortBy('orden')->values();

                $salida->puntos_json = json_encode(
                    $puntos->map(function ($p) {
                        return [
                            'sucursal_id' => (string) $p->sucursal_id,
                            'orden' => (int) $p->orden,
                            'nombre' => $p->sucursal?->nombre_comercial,
                        ];
                    })->values()->toArray(),
                    JSON_UNESCAPED_UNICODE
                );

                $salida->origen_nombre = $puntos->first()?->sucursal?->nombre_comercial ?? '—';
                $salida->destino_nombre = $puntos->last()?->sucursal?->nombre_comercial ?? '—';

                $origenId = $puntos->first()?->sucursal_id;
                $destinoId = $puntos->last()?->sucursal_id;
                $asientosMap = $salida->asientosDisponibles($origenId, $destinoId);
                $salida->capacidad_bus = collect($asientosMap)->filter(fn($estado) => $estado === 'libre')->count();

                return $salida;
            });

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
            $puntos = $ruta->puntos->sortBy('orden')->values();

            $origenId = $puntos->first()?->sucursal_id;
            $destinoId = $puntos->last()?->sucursal_id;

            $asientos = $salida->asientosDisponibles($origenId, $destinoId);
            $precio = $salida->horario->costo_base;
        }

        $svg = file_get_contents(storage_path('app/public/' . $salida->horario->tipo_vehiculo->ruta_svg));

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
            'dni' => 'required|string|max:20',
            'codigo' => 'nullable|string',
        ]);

        $cantidad = Pasaje::whereHas('persona', function ($q) use ($request) {
            $q->where('documento', $request->dni);
        })
            ->where('origen_sucursal_id', $request->origen_id)
            ->where('destino_sucursal_id', $request->destino_id)
            ->whereIn('estado', ['V', 'F'])
            ->count();

        $numeroActual = $cantidad + 1;
        $esGratis = $numeroActual % 10 === 0;

        return response()->json([
            'valido' => $esGratis,
            'numero_actual' => $numeroActual,
            'message' => $esGratis
                ? "Este pasajero va en su viaje número {$numeroActual} para este tramo."
                : "Este pasajero aún no califica para la promoción. Va en su viaje número {$numeroActual}.",
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|in:reservar,vender',

            'salida_id' => 'required|exists:salidas,id',
            'origen_id' => 'required|exists:sucursales,id',
            'destino_id' => 'required|exists:sucursales,id',

            'asientos' => 'required|array|min:1',
            'asientos.*' => 'required|integer|min:1',

            'tipo_documento_id' => 'required|array',
            'tipo_documento_id.*' => 'required|integer',

            'documento' => 'required|array',
            'documento.*' => 'required|string|max:20',

            'nombres' => 'required|array',
            'nombres.*' => 'required|string|max:200',

            'apellidos' => 'required|array',
            'apellidos.*' => 'required|string|max:200',

            'celular' => 'required|array',
            'celular.*' => 'required|string|max:20',

            'telefono' => 'nullable|array',
            'correo' => 'nullable|array',

            'descuento_ids' => 'nullable|array',
            'descuento_montos' => 'nullable|array',
            'precios_finales' => 'nullable|array',

            'tipo_documento_factura_id' => 'nullable|integer',
            'numero_documento_id' => 'nullable|string|max:20',
            'razon_social' => 'nullable|string|max:255',

            'metodo_pago_id' => 'nullable|integer',
            'billetera_id' => 'nullable|integer',
            'pago_efectivo' => 'nullable|numeric|min:0',
            'pago_billetera' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $accion = $request->accion;
            $estadoPasaje = $accion === 'reservar' ? 'R' : 'V';

            $salida = Salida::with([
                'horario.ruta.puntos.sucursal',
                'horario.ruta.tramos.origen',
                'horario.ruta.tramos.destino',
                'horario.tipo_vehiculo',
                'horario.tipo_viaje',
            ])->findOrFail($request->salida_id);

            $tramos = $salida->obtenerTramosDeViaje($request->origen_id, $request->destino_id);

            if ($tramos->isEmpty()) {
                throw ValidationException::withMessages([
                    'destino_id' => 'No se pudo determinar el tramo del viaje.',
                ]);
            }

            $cantidadAsientos = count($request->asientos);

            if (
                count($request->documento) !== $cantidadAsientos ||
                count($request->nombres) !== $cantidadAsientos ||
                count($request->apellidos) !== $cantidadAsientos ||
                count($request->celular) !== $cantidadAsientos
            ) {
                throw ValidationException::withMessages([
                    'asientos' => 'La cantidad de pasajeros no coincide con la cantidad de asientos.',
                ]);
            }

            $precioBase = $salida->calcularCostoPorTramos($request->origen_id, $request->destino_id);

            $pasajeros = [];
            $totalVenta = 0;

            foreach ($request->asientos as $index => $asientoNumero) {
                $documento = trim($request->documento[$index] ?? '');
                $nombres = trim($request->nombres[$index] ?? '');
                $apellidos = trim($request->apellidos[$index] ?? '');
                $celular = trim($request->celular[$index] ?? '');
                $telefono = $request->telefono[$index] ?? null;
                $correo = $request->correo[$index] ?? null;

                if (!$documento || !$nombres || !$apellidos || !$celular) {
                    throw ValidationException::withMessages([
                        "documento.$index" => "Faltan datos del pasajero del asiento {$asientoNumero}.",
                    ]);
                }

                $esMenor = isset($request->pasajero_menor[$index]) && $request->pasajero_menor[$index] == 1;
                $autorizacionPdf = null;

                if ($esMenor) {
                    if (!$request->hasFile("autorizacion_pdf.$index")) {
                        throw ValidationException::withMessages([
                            "autorizacion_pdf.$index" => "El asiento {$asientoNumero} corresponde a un menor y requiere PDF de autorización.",
                        ]);
                    }

                    $autorizacionPdf = $request->file("autorizacion_pdf.$index")->store('pasajes', 'public');
                }

                $persona = Persona::updateOrCreate(
                    ['documento' => $documento],
                    [
                        'tipo_documento_id' => $request->tipo_documento_id[$index],
                        'nombres' => $nombres,
                        'apellidos' => $apellidos,
                        'celular' => $celular,
                        'telefono' => $telefono,
                        'correo' => $correo,
                        'estado' => 'A',
                        'fecha_creacion' => now(),
                    ]
                );

                $asientosDisponibles = $salida->asientosDisponibles($request->origen_id, $request->destino_id);

                if (($asientosDisponibles[$asientoNumero] ?? 'ocupado') !== 'libre') {
                    throw ValidationException::withMessages([
                        "asientos.$index" => "El asiento {$asientoNumero} ya está ocupado para ese tramo.",
                    ]);
                }

                $descuentoId = $request->descuento_ids[$index] ?? null;
                $descuentoMontoFront = (float) ($request->descuento_montos[$index] ?? 0);
                $precioFinalFront = (float) ($request->precios_finales[$index] ?? $precioBase);

                $descuentoMontoReal = 0;
                $esPromocion = false;

                if ($descuentoId) {
                    $descuento = Descuento::find($descuentoId);

                    if (!$descuento) {
                        throw ValidationException::withMessages([
                            "descuento_ids.$index" => "El descuento del asiento {$asientoNumero} no existe.",
                        ]);
                    }

                    if ((int) $descuento->id === 1) {
                        $cantidadCompradosMismoDni = Pasaje::whereHas('persona', function ($q) use ($documento) {
                            $q->where('documento', $documento);
                        })
                            ->where('origen_sucursal_id', $request->origen_id)
                            ->where('destino_sucursal_id', $request->destino_id)
                            ->whereIn('estado', ['V', 'F'])
                            ->count();

                        $numeroActual = $cantidadCompradosMismoDni + 1;

                        if ($numeroActual % 10 !== 0) {
                            throw ValidationException::withMessages([
                                "descuento_ids.$index" => "El descuento promocional solo aplica en el viaje número 10 del mismo tramo para ese DNI.",
                            ]);
                        }

                        $descuentoMontoReal = $precioBase;
                        $esPromocion = true;
                    } else {
                        if (!empty($descuento->monto_efectivo)) {
                            $descuentoMontoReal = (float) $descuento->monto_efectivo;
                        } elseif (!empty($descuento->porcentaje)) {
                            $descuentoMontoReal = $precioBase * ((float) $descuento->porcentaje / 100);
                        }
                    }
                }

                $precioFinalReal = max(0, $precioBase - $descuentoMontoReal);

                if (abs($precioFinalReal - $precioFinalFront) > 0.01) {
                    throw ValidationException::withMessages([
                        "precios_finales.$index" => "El precio final del asiento {$asientoNumero} no coincide con la validación del servidor.",
                    ]);
                }

                $totalVenta += $precioFinalReal;

                $pasajeros[] = [
                    'index' => $index,
                    'persona' => $persona,
                    'asiento_numero' => $asientoNumero,
                    'pasajero_menor' => $esMenor,
                    'autorizacion_pdf' => $autorizacionPdf,
                    'descuento_id' => $descuentoId,
                    'descuento_monto' => $descuentoMontoReal,
                    'precio_final' => $precioFinalReal,
                    'es_promocion' => $esPromocion,
                ];
            }

            $venta = null;

            if ($accion === 'vender') {
                $personaFacturacion = null;

                if ($request->filled('numero_documento_id')) {
                    $personaFacturacion = Persona::updateOrCreate(
                        ['documento' => $request->numero_documento_id],
                        [
                            'tipo_documento_id' => $request->tipo_documento_factura_id ?? 1,
                            'nombres' => $request->razon_social ?: 'CLIENTE VARIOS',
                            'estado' => 'A',
                            'fecha_creacion' => now(),
                        ]
                    );
                } else {
                    $primerPasajero = $pasajeros[0]['persona'];
                    $personaFacturacion = $primerPasajero;
                }

                $tipoDoc = $request->tipo_documento_factura_id;

                $serie = match ($tipoDoc) {
                    1 => 'B001',
                    2 => 'F001',
                    default => 'B001',
                };

                $venta = Venta::create([
                    'tipo_servicio_id' => 1,
                    'sucursal_id' => Auth::user()->sucursal_id,
                    'usuario_id' => Auth::id(),
                    'persona_id' => $personaFacturacion->id,
                    'tipo_documento_factura_id' => $tipoDoc,
                    'serie' => $serie,
                    'numero' => 1,
                    'total' => $totalVenta,
                    'fecha_emision' => now(),
                ]);

                foreach ($pasajeros as $pasajeroData) {
                    $venta->detalles()->create([
                        'tipo_servicio_id' => 1,
                        'descripcion' => $salida->horario->ruta->nombre
                            . ' - '
                            . ($salida->horario->ruta->puntos->firstWhere('sucursal_id', $request->origen_id)?->sucursal?->nombre_comercial ?? 'Origen')
                            . ' → '
                            . ($salida->horario->ruta->puntos->firstWhere('sucursal_id', $request->destino_id)?->sucursal?->nombre_comercial ?? 'Destino')
                            . ' - Asiento ' . $pasajeroData['asiento_numero'],
                        'cantidad' => 1,
                        'precio_venta' => $precioBase,
                        'total' => $pasajeroData['precio_final'],
                        'descuento' => $pasajeroData['descuento_monto'],
                    ]);
                }

                $pagoEfectivo = (float) ($request->pago_efectivo ?? 0);
                $pagoBilletera = (float) ($request->pago_billetera ?? 0);
                $hoy = Carbon::now();
                if ($pagoEfectivo > 0) {
                    $venta->pagos()->create([
                        'metodo_pago_id' => 1,
                        'billetera_id' => null,
                        'total' => $pagoEfectivo,
                        'fecha_creacion' => $hoy,
                    ]);
                }

                if ($pagoBilletera > 0) {
                    $venta->pagos()->create([
                        'metodo_pago_id' => 2,
                        'billetera_id' => $request->billetera_id,
                        'total' => $pagoBilletera,
                        'fecha_creacion' => $hoy,

                    ]);
                }
            }

            foreach ($pasajeros as $pasajeroData) {
                $pasaje = Pasaje::create([
                    'venta_id' => $venta?->id,
                    'usuario_id' => Auth::id(),
                    'persona_id' => $pasajeroData['persona']->id,
                    'pasajero_menor' => $pasajeroData['pasajero_menor'],
                    'autorizacion_pdf' => $pasajeroData['autorizacion_pdf'],
                    'asiento_numero' => $pasajeroData['asiento_numero'],
                    'salida_id' => $salida->id,
                    'origen_sucursal_id' => $request->origen_id,
                    'destino_sucursal_id' => $request->destino_id,
                    'estado' => $estadoPasaje,
                    'es_promocion' => $pasajeroData['es_promocion'],
                    'precio_cobrado' => $pasajeroData['precio_final'],
                    'fecha_creacion' => now(),
                    'fecha_inactivacion' => null,
                ]);

                $pasaje->tramos()->attach($tramos->pluck('id')->toArray());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $accion === 'reservar'
                    ? 'Reserva realizada correctamente.'
                    : 'Venta realizada correctamente.',
                'redirect' => route('pasajes.index'),
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
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
            'origen_id' => 'required|exists:sucursales,id',
            'destino_id' => 'required|exists:sucursales,id',
        ]);

        if (!in_array($pasaje->estado, ['R', 'V'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede cambiar horario a pasajes reservados o vendidos.'
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $pasaje) {
                $pasaje->load(['salida.horario.ruta', 'tramos']);

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

                $asientos = $nuevaSalida->asientosDisponibles(
                    $request->origen_id,
                    $request->destino_id
                );

                if (($asientos[$request->nuevo_asiento_numero] ?? 'ocupado') !== 'libre') {
                    return response()->json([
                        'success' => false,
                        'message' => 'El asiento seleccionado ya no está disponible.'
                    ], 422);
                }

                $tramos = $nuevaSalida->obtenerTramosDeViaje(
                    $request->origen_id,
                    $request->destino_id
                );

                if ($tramos->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudieron determinar los tramos del nuevo viaje.'
                    ], 422);
                }

                $nuevoPrecio = $tramos->sum('costo_tramo');

                $descuentoMonto = (float) ($request->descuento_monto ?? 0);
                $nuevoPrecio = max(0, $tramos->sum('costo_tramo') - $descuentoMonto);


                $pasaje->update([
                    'salida_id' => $nuevaSalida->id,
                    'origen_sucursal_id' => $request->origen_id,
                    'destino_sucursal_id' => $request->destino_id,
                    'asiento_numero' => $request->nuevo_asiento_numero,
                    'precio_cobrado' => $nuevoPrecio,
                    'es_promocion' => (int) $request->descuento_id === 1,
                    'fecha_inactivacion' => null,
                ]);

                $pasaje->tramos()->sync($tramos->pluck('id')->toArray());

                return response()->json([
                    'success' => true,
                    'message' => 'Pasaje actualizado correctamente.',
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

    public function vender(Request $request)
    {
        $request->validate([
            'salida' => 'required|exists:salidas,id',
            'asientos' => 'required|string',
            'origen_id' => 'required|exists:sucursales,id',
            'destino_id' => 'required|exists:sucursales,id',
        ]);

        $salida = Salida::with([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'horario.tipo_viaje',
        ])->findOrFail($request->salida);

        $asientos = collect(explode(',', $request->asientos))
            ->map(fn($a) => (int) trim($a))
            ->filter(fn($a) => $a > 0)
            ->values()
            ->toArray();

        if (empty($asientos)) {
            return redirect()->route('pasajes.index')
                ->withErrors('No se recibieron asientos válidos.');
        }

        $origen = Sucursal::findOrFail($request->origen_id);
        $destino = Sucursal::findOrFail($request->destino_id);

        $tramos = $salida->obtenerTramosDeViaje($origen->id, $destino->id);

        if ($tramos->isEmpty()) {
            return redirect()->route('pasajes.index')
                ->withErrors('No se pudo determinar el tramo del viaje.');
        }

        $asientosDisponibles = $salida->asientosDisponibles($origen->id, $destino->id);

        foreach ($asientos as $asiento) {
            if (($asientosDisponibles[$asiento] ?? 'ocupado') !== 'libre') {
                return redirect()->route('pasajes.index')
                    ->withErrors("El asiento {$asiento} ya no está disponible.");
            }
        }

        $precioUnitario = $salida->calcularCostoPorTramos($origen->id, $destino->id);

        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $metodos_pago = MetodoPago::all();
        $billeteras_digitales = BilleteraDigital::all();

        return view('pasajes.venta', compact(
            'salida',
            'asientos',
            'origen',
            'destino',
            'precioUnitario',
            'tipos_documentos',
            'tipos_documentos_facturas',
            'metodos_pago',
            'billeteras_digitales'
        ));
    }

    public function actualizar(Request $request, Pasaje $pasaje)
    {
        if (!in_array($pasaje->estado, ['R', 'V'])) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar pasajes reservados o vendidos.'
            ], 422);
        }

        $request->validate([
            'tipo_documento_id' => 'required|integer',
            'documento' => 'required|string|max:20',
            'nombres' => 'required|string|max:200',
            'apellidos' => 'required|string|max:200',
            'celular' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'pasajero_menor' => 'nullable|boolean',
            'descuento_id' => 'nullable|exists:descuentos,id',
        ]);

        try {
            DB::beginTransaction();

            $persona = Persona::updateOrCreate(
                ['documento' => $request->documento],
                [
                    'tipo_documento_id' => $request->tipo_documento_id,
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'celular' => $request->celular,
                    'telefono' => $request->telefono,
                    'correo' => $request->correo,
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $autorizacionPdf = $pasaje->autorizacion_pdf;

            $esMenor = (bool) $request->pasajero_menor;

            if ($esMenor) {
                if ($request->hasFile('autorizacion_pdf')) {
                    $autorizacionPdf = $request->file('autorizacion_pdf')->store('pasajes', 'public');
                } elseif (!$autorizacionPdf) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El pasajero menor requiere autorización PDF.'
                    ], 422);
                }
            } else {
                $autorizacionPdf = null;
            }

            $pasaje->update([
                'persona_id' => $persona->id,
                'pasajero_menor' => $esMenor,
                'autorizacion_pdf' => $autorizacionPdf,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pasaje actualizado correctamente.',
                'redirect' => route('pasajes.index'),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizarVenta(Request $request, Pasaje $pasaje)
    {
        if (!$pasaje->venta_id) {
            return response()->json([
                'success' => false,
                'message' => 'Este pasaje no tiene venta asociada.'
            ], 422);
        }

        if ($pasaje->estado !== 'V') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede editar la venta de pasajes vendidos.'
            ], 422);
        }

        $request->validate([
            'tipo_documento_factura_id' => 'required|integer',
            'numero_documento_id' => 'nullable|string|max:20',
            'razon_social' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $personaVenta = Persona::updateOrCreate(
                ['documento' => $request->numero_documento_id ?: 'SIN-DOC-' . $pasaje->venta_id],
                [
                    'tipo_documento_id' => $request->tipo_documento_factura_id,
                    'nombres' => $request->razon_social ?: 'CLIENTE',
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $pasaje->venta->update([
                'persona_id' => $personaVenta->id,
                'tipo_documento_factura_id' => $request->tipo_documento_factura_id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta actualizada correctamente.',
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function buscarPasaje(Request $request)
    {
        $request->validate([
            'salida_id' => 'required|exists:salidas,id',
            'asiento' => 'required|integer|min:1',
        ]);

        $pasaje = Pasaje::where('salida_id', $request->salida_id)
            ->where('asiento_numero', $request->asiento)
            ->whereIn('estado', ['R', 'V', 'F'])
            ->latest('id')
            ->first();

        if (!$pasaje) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró pasaje para ese asiento.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pasaje_id' => $pasaje->id,
            'estado' => $pasaje->estado,
        ]);
    }
}
