<?php

namespace App\Http\Controllers;

use App\Models\Pasaje;
use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\MetodoPago;
use App\Models\BilleteraDigital;
use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\Cliente;
use App\Models\Descuento;
use App\Models\Encomienda;
use App\Models\EncomiendaDetalle;
use App\Models\PasajeSobreEquipaje;
use App\Models\Persona;
use App\Models\Pueblito;
use App\Models\RutaPunto;
use App\Models\TipoEncomienda;
use App\Models\Venta;
use App\Services\PagoService;
use App\Services\VentaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PasajeController extends Controller
{

    public function index()
    {
        $hoy = now('America/Lima')->format('Y-m-d');

        $ayer = now('America/Lima')->subDay()->format('Y-m-d');
        $limite = now('America/Lima')->subMinutes(30);

        $salidas = Salida::with([
            'horario.ruta.puntos.pueblito',
            'horario.ruta.puntos.sucursal',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ])
            ->join('horarios', 'horarios.id', '=', 'salidas.horario_id')
            ->whereIn('salidas.estado', ['activo', 'programado'])
            ->whereRaw(
                "TIMESTAMP(salidas.fecha_salida, horarios.hora_salida) >= ?",
                [$limite->format('Y-m-d H:i:s')]
            )
            ->orderBy('salidas.fecha_salida')
            ->orderBy('horarios.hora_salida')
            ->select('salidas.*')
            ->get()
            ->map(function ($salida) {
                $ruta = $salida->horario->ruta;
                $puntos = $ruta->puntos->sortBy('orden')->values();
                $hora = Carbon::parse($salida->fecha_salida);
                $hora->setTimeFromTimeString($salida->horario->hora_salida);
                $puntosConHora = [];

                foreach ($puntos as $i => $p) {

                    if ($i > 0) {
                        $tramo = $ruta->tramos()
                            ->where('punto_origen_id', $puntos[$i - 1]->id)
                            ->where('punto_destino_id', $p->id)
                            ->first();

                        if ($tramo) {
                            $hora->addMinutes($tramo->duracion_minutos);
                        }
                    }

                    $puntosConHora[] = [
                        'pueblito_id' => (string) $p->pueblito_id,
                        'orden' => (int) $p->orden,
                        'nombre' => trim(
                            ($p->pueblito?->descripcion ?? '') .
                                ($p->sucursal ? ' - ' . $p->sucursal->nombre_comercial : '')
                        ),
                        'hora' => $hora->format('H:i')
                    ];
                }

                $salida->puntos_json = json_encode($puntosConHora, JSON_UNESCAPED_UNICODE);

                $origen = $puntos->first();
                $destino = $puntos->last();

                $salida->origen_nombre = trim(
                    ($origen?->pueblito?->descripcion ?? '') .
                        ($origen?->sucursal ? ' - ' . $origen->sucursal->nombre_comercial : '')
                );

                $salida->destino_nombre = trim(
                    ($destino?->pueblito?->descripcion ?? '') .
                        ($destino?->sucursal ? ' - ' . $destino->sucursal->nombre_comercial : '')
                );
                $ruta = $salida->horario->ruta;

                $puntosOrdenados = $ruta->puntos->sortBy('orden')->values();

                $inicio = $puntosOrdenados->first()?->pueblito?->descripcion;
                $fin    = $puntosOrdenados->last()?->pueblito?->descripcion;

                $salida->ruta_completa = $inicio && $fin
                    ? "{$inicio} → {$fin}"
                    : '-';
                $origenId = $puntos->first()?->pueblito_id;
                $destinoId = $puntos->last()?->pueblito_id;
                $asientosMap = $salida->asientosDisponibles($origenId, $destinoId);
                $salida->capacidad_bus = collect($asientosMap)->filter(fn($estado) => $estado === 'libre')->count();

                return $salida;
            });

        $sucursales = Sucursal::where('estado', 'A')
            ->orderBy('nombre_comercial')
            ->get();
        $pueblitos = Pueblito::orderBy('descripcion')
            ->get();
        return view('pasajes.index', compact('hoy', 'salidas', 'sucursales', 'ayer', 'pueblitos'));
    }

    public function listarPasajes(Request $request)
    {
        $query = Pasaje::query()
            ->with([
                'salida.horario.ruta',
                'origen',
                'destino',
                'persona',
                'venta',
                'sobreEquipajes'
            ])
            ->join('salidas', 'pasajes.salida_id', '=', 'salidas.id')
            ->join('personas', 'pasajes.persona_id', '=', 'personas.id')
            ->whereIn('pasajes.estado', ['V', 'F', 'X', 'R'])
            ->orderBy("id", "desc");

        if ($request->filled('documento')) {
            $documento = trim($request->documento);
            $query->where('personas.documento', 'like', "{$documento}%");
        }

        if ($request->filled('fecha')) {
            $query->whereDate('salidas.fecha_salida', $request->fecha);
        }

        if ($request->filled('origen_id')) {
            $query->where('pasajes.origen_pueblito_id', $request->origen_id);
        }

        if ($request->filled('destino_id')) {
            $query->where('pasajes.destino_pueblito_id', $request->destino_id);
        }

        if ($request->filled('estado')) {
            $query->where('pasajes.estado', $request->estado);
        }

        $pasajes = $query
            ->orderBy('salidas.fecha_salida', 'desc')
            ->orderBy('pasajes.asiento_numero', 'asc')
            ->select('pasajes.*')
            ->paginate(20)->withQueryString();

        $sucursales = Sucursal::where('estado', 'A')
            ->orderBy('nombre_comercial')
            ->get();

        if ($request->ajax()) {
            return view('pasajes.partials.tabla', compact('pasajes'))->render();
        }

        return view('pasajes.busqueda', compact('pasajes', 'sucursales'));
    }

    public function datatable()
    {
        $pasajes = Pasaje::with([
            'salida.horario.ruta.puntos.sucursal',
            'salida.horario.tipo_viaje',
            'salida.horario.tipo_vehiculo',
            'origen',
            'destino',
            'persona',
            'venta',
        ])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($pasaje) {
                $ruta = $pasaje->salida?->horario?->ruta;

                if (!$ruta) {
                    $pasaje->puntos_json = json_encode([], JSON_UNESCAPED_UNICODE);
                    $pasaje->origen_nombre = '—';
                    $pasaje->destino_nombre = '—';
                    $pasaje->capacidad_bus = 0;
                    return $pasaje;
                }

                $puntos = $ruta->puntos->sortBy('orden')->values();

                $pasaje->puntos_json = json_encode(
                    $puntos->map(function ($p) {
                        return [
                            'pueblito_id' => (string) $p->pueblito_id,
                            'orden' => (int) $p->orden,
                            'nombre' => $p->sucursal?->nombre_comercial,
                        ];
                    })->values()->toArray(),
                    JSON_UNESCAPED_UNICODE
                );

                $pasaje->origen_nombre = $puntos->first()?->sucursal?->nombre_comercial ?? '—';
                $pasaje->destino_nombre = $puntos->last()?->sucursal?->nombre_comercial ?? '—';

                $origenId = $puntos->first()?->pueblito_id;
                $destinoId = $puntos->last()?->pueblito_id;

                $asientosMap = $pasaje->salida->asientosDisponibles($origenId, $destinoId);
                $pasaje->capacidad_bus = collect($asientosMap)
                    ->filter(fn($estado) => $estado === 'libre')
                    ->count();

                return $pasaje;
            });

        $sucursales = Sucursal::where('estado', 'A')
            ->orderBy('nombre_comercial')
            ->get();

        return view('pasajes.busqueda', compact('pasajes', 'sucursales'));
    }

    public function asientos(Salida $salida, Request $request)
    {
        $request->validate([
            'origen_id' => 'nullable|exists:pueblitos,id',
            'destino_id' => 'nullable|exists:pueblitos,id',
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

            $origenId = $puntos->first()?->pueblito_id;
            $destinoId = $puntos->last()?->pueblito_id;

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
            'origen_id' => 'required|exists:pueblitos,id',
            'destino_id' => 'required|exists:pueblitos,id',
            'dni' => 'required|string|max:20',
            'codigo' => 'nullable|string',
        ]);

        $cantidad = Pasaje::whereHas('persona', function ($q) use ($request) {
            $q->where('documento', $request->dni);
        })
            ->where('origen_pueblito_id', $request->origen_id)
            ->where('destino_pueblito_id', $request->destino_id)
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
    /**
     * Venta de pasajes
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|in:reservar,vender',
            'salida_id' => 'required|exists:salidas,id',
            'origen_id' => 'required|exists:pueblitos,id',
            'destino_id' => 'required|exists:pueblitos,id',
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
            'celular' => 'nullable|array',
            'celular.*' => 'nullable|string|max:20',
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
            'caja_id' => 'nullable|exists:caja,id',
            'pago_tarjeta' => 'nullable|numeric|min:0',
            'pago_yape' => 'nullable|numeric|min:0',
            'pago_plin' => 'nullable|numeric|min:0',
            'pago_transferencia' => 'nullable|numeric|min:0',
        ]);
        if (
            $request->accion === 'vender' &&
            Auth::user()->hasRole('Administrador') &&
            !$request->caja_id
        ) {
            throw ValidationException::withMessages([
                'caja_id' => 'Debe seleccionar una caja para realizar la venta.',
            ]);
        }
        try {
            DB::beginTransaction();

            $accion = $request->accion;
            $estadoPasaje = $accion === 'reservar' ? 'R' : 'V';

            $salida = Salida::with([
                'horario.ruta.puntos.pueblito',
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

                if (!$documento || !$nombres || !$apellidos) {
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
                        'direccion' => $request->direccion,
                        'celular' => $celular,
                        'telefono' => $telefono,
                        'correo' => $correo,
                        'estado' => 'A',
                        'fecha_creacion' => now(),
                    ]
                );

                Cliente::updateOrCreate(
                    ['persona_id' => $persona->id],
                    ['user_id' => Auth::id()]
                );

                $asientosDisponibles = $salida->asientosDisponibles($request->origen_id, $request->destino_id);

                if (($asientosDisponibles[$asientoNumero] ?? 'ocupado') !== 'libre') {
                    throw ValidationException::withMessages([
                        "asientos.$index" => "El asiento {$asientoNumero} ya está ocupado para ese tramo.",
                    ]);
                }

                $descuentoId = $request->descuento_ids[$index] ?? null;
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
                            ->where('origen_pueblito_id', $request->origen_id)
                            ->where('destino_pueblito_id', $request->destino_id)
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

                $precioFinalReal = $precioFinalFront;

                if ($precioFinalReal < 0) {
                    $precioFinalReal = 0;
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

            $detalles = [];

            foreach ($pasajeros as $p) {
                $origenNombre = Pueblito::find($request->origen_id)?->descripcion;
                $destinoNombre = Pueblito::find($request->destino_id)?->descripcion;

                $detalles[] = [
                    'descripcion' =>
                    "Pasaje {$origenNombre} - {$destinoNombre} | Asiento {$p['asiento_numero']}",

                    'costo' => $p['precio_final'],
                    'descuento' => $p['descuento_monto'],
                ];
            }


            if ($request->filled('numero_documento_id')) {
                $personaFacturacion = Persona::updateOrCreate(
                    ['documento' => $request->numero_documento_id],
                    [
                        'tipo_documento_id' => $request->tipo_documento_factura_id ?? 1,
                        'nombres' => $request->razon_social ?: 'CLIENTE VARIOS',
                        'direccion' => $request->direccion,
                        'estado' => 'A',
                        'fecha_creacion' => now(),
                    ]
                );
            } else {
                $personaFacturacion = $pasajeros[0]['persona'];
            }

            if ($accion === 'vender') {
                $ventaService = app(VentaService::class);
                $pagoService = app(PagoService::class);

                $totalSobreEquipaje = 0;

                foreach ($request->sobre_equipaje_detalles ?? [] as $grupo) {

                    foreach ($grupo as $item) {

                        $costo = (float)$item['costo'];

                        $detalles[] = [
                            'descripcion' => 'Sobreequipaje - ' . ($item['descripcion'] ?? ''),
                            'costo' => $costo,
                            'descuento' => 0,
                        ];

                        $totalSobreEquipaje += $costo;
                    }
                }

                $totalVenta += $totalSobreEquipaje;

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

                $pagos = $request->pagos ?? [];

                $sumaPagos = collect($pagos)->sum(function ($pago) {
                    return (float) $pago['total'];
                });

                if (round($sumaPagos, 2) !== round($totalVenta, 2)) {
                    throw ValidationException::withMessages([
                        'pagos' => 'La suma de pagos no coincide con el total.',
                    ]);
                }

                foreach ($pagos as $pago) {
                    CajaDetalle::create([
                        'caja_id' => $request->caja_id,
                        'subtipo_movimiento_caja_id' => 1,
                        'metodo_pago_id' => $pago['metodo_pago_id'],
                        'amount' => $pago['total'],
                        'description' => "Venta de pasaje #{$venta->id}",
                        'anulado' => false,
                        'venta_id' => $venta->id,
                        'billetera_digital_id' => $pago['billetera_id'] ?? null,
                    ]);
                }

                $emision = $ventaService->emitirVenta($venta);
            }

            $pasajesCreados = [];

            foreach ($pasajeros as $pasajeroData) {
                $pasaje = Pasaje::create([
                    'venta_id' => $venta?->id ?? null,
                    'usuario_id' => Auth::id(),
                    'persona_id' => $pasajeroData['persona']->id,
                    'pasajero_menor' => $pasajeroData['pasajero_menor'],
                    'autorizacion_pdf' => $pasajeroData['autorizacion_pdf'],
                    'descuento_id' => $pasajeroData['descuento_id'],
                    'asiento_numero' => $pasajeroData['asiento_numero'],
                    'salida_id' => $salida->id,
                    'origen_pueblito_id' => $request->origen_id,
                    'destino_pueblito_id' => $request->destino_id,
                    'precio_pasaje' => $request->precio_manual,
                    'estado' => $estadoPasaje,
                    'es_promocion' => $pasajeroData['es_promocion'],
                    'precio_cobrado' => $pasajeroData['precio_final'],
                    'fecha_creacion' => now(),
                    'fecha_inactivacion' => null,
                ]);

                $pasajesCreados[] = $pasaje->id;
                $pasaje->tramos()->attach($tramos->pluck('id')->toArray());

                $index = $pasajeroData['index'];

                if (
                    isset($request->sobre_equipaje_detalles[$index])
                    && is_array($request->sobre_equipaje_detalles[$index])
                ) {

                    foreach ($request->sobre_equipaje_detalles[$index] as $sobre) {

                        $encomienda = Encomienda::create([
                            'usuario_id' => Auth::id(),
                            'emisor_persona_id' => $pasajeroData['persona']->id,
                            'venta_id' => $venta->id,
                            'estado' => "A",
                            'total' => $sobre['costo'],
                            'fecha_creacion' => now(),
                            'pago_instantaneo' => true,
                            'sobre_equipaje' => true,
                            'origen_pueblito_id' => $request->origen_id,
                            'destino_pueblito_id' => $request->destino_id,
                        ]);

                        EncomiendaDetalle::create([
                            'encomienda_id' => $encomienda->id,
                            'tipo_encomienda_id' => $sobre['tipo_encomienda_id'],
                            'descripcion'        => $sobre['descripcion'],
                            'peso'               => $sobre['peso'],
                            'costo'              => $sobre['costo'],

                        ]);

                        PasajeSobreEquipaje::create([
                            'pasaje_id'     => $pasaje->id,
                            'encomienda_id' => $encomienda->id,
                        ]);
                    }
                }
            }

            DB::commit();

            if ($accion === 'reservar') {
                return response()->json([
                    'success' => true,
                    'ticket_url' => route('pasajes.ticket', $pasajesCreados[0]),
                    'redirect' => route('pasajes.index'),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $accion === 'reservar'
                    ? 'Reserva realizada correctamente.'
                    : 'Venta realizada correctamente.',
                'venta_id' => $venta->id,
                'tickets' => collect($pasajesCreados)->map(fn($id) => route('ventas.tickets', $id)),
                'ticket_url' => route('ventas.tickets', $venta->id),
                'comprobante' => $emision['nombre'] ?? null,
                'xml_path' => $emision['xml_path'] ?? null,
                'cdr_path' => $emision['cdr_path'] ?? null,
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
                'trace' => $e->getTraceAsString(),
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
            'origen' => $pasaje->origenPueblito?->descripcion,
            'destino' => $pasaje->destinoPueblito?->descripcion,
            'pasajero' => $pasaje->persona ? [
                'documento' => $pasaje->persona->documento,
                'nombres' => $pasaje->persona->nombres,
                'apellidos' => $pasaje->persona->apellidos,
                'celular' => $pasaje->persona->celular,
            ] : null,
            'pagos' => $pasaje->venta?->pagos ?? [],
        ]);
    }

    public function showSobreEquipaje(Pasaje $pasaje)
    {
        $pasaje->load([
            'sobreEquipajes.encomienda.detalles.tipo_encomienda'
        ]);

        return response()->json([
            'success' => true,
            'data' => $pasaje->sobreEquipajes
        ]);
    }

    public function editar(Pasaje $pasaje)
    {
        $pasaje->load([
            'persona',
            'salida.horario',
            'sobreEquipajes',
            'descuento',
        ]);

        $salida  = $pasaje->salida;
        $origen  = Pueblito::find($pasaje->origen_pueblito_id);
        $destino = Pueblito::find($pasaje->destino_pueblito_id);
        $sobreEquipajes = $pasaje->sobreEquipajes;

        $asientos = [$pasaje->asiento_numero];
        $user = Auth::user();

        // precio_manual = SIEMPRE precio_pasaje (el precio base del asiento, sin descuento)
        $precioUnitario   = $pasaje->precio_pasaje;
        $tiposEncomienda  = TipoEncomienda::all();
        $cajas_emision = Caja::with('sucursal.serie')
            ->where('usuario_id', $user->id)
            ->where('estado', 'A')
            ->get();
        $tipos_documentos = TipoDocumentoPersona::all();
        $user             = auth()->user();

        // El monto del descuento se CALCULA contra precio_pasaje, no se guarda en BD
        $descuentosConfig = [];
        if ($pasaje->descuento_id && $pasaje->descuento) {
            $tipo  = $pasaje->descuento->tipo;            // 'porcentaje' | 'monto_fijo'
            $valor = (float) $pasaje->descuento->valor;

            $monto = $tipo === 'porcentaje'
                ? $precioUnitario * ($valor / 100)
                : $valor;

            $descuentosConfig[(string) $pasaje->asiento_numero] = [
                'descuento_id' => $pasaje->descuento->id,
                'codigo'       => $pasaje->descuento->codigo,
                'tipo'         => $tipo,
                'valor'        => $valor,
                'monto'        => $monto,
            ];
        }

        // Ya no usamos precio_cobrado aquí: es el total de la venta, no el precio del asiento.
        // El precio final del asiento lo calcula el JS = precio_pasaje - monto_descuento
        $preciosFinales = [];

        return view('pasajes.editar', compact(
            'pasaje',
            'salida',
            'origen',
            'destino',
            'asientos',
            'precioUnitario',
            'tipos_documentos',
            'tiposEncomienda',
            'cajas_emision',
            'user',
            'sobreEquipajes',
            'descuentosConfig',
            'preciosFinales'
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
            ->orderBy('fecha_salida')
            ->get();

        return view('pasajes.cambiar-horario', compact('pasaje', 'salidas'));
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
        $user = Auth::user();

        $request->validate([
            'salida' => 'required|exists:salidas,id',
            'asientos' => 'required|string',
            'origen_id' => 'required|exists:pueblitos,id',
            'destino_id' => 'required|exists:pueblitos,id',
        ]);

        $salida = Salida::with([
            'horario.ruta.puntos.pueblito',
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'horario.tipo_viaje',
        ])->findOrFail($request->salida);

        $cajas_emision = Caja::with('sucursal.serie')
            ->where('usuario_id', $user->id)
            ->where('estado', 'A')
            ->get();

        $asientos = collect(explode(',', $request->asientos))
            ->map(fn($a) => (int) trim($a))
            ->filter(fn($a) => $a > 0)
            ->values()
            ->toArray();

        if (empty($asientos)) {
            return redirect()->route('pasajes.index')
                ->withErrors('No se recibieron asientos válidos.');
        }

        $origen = Pueblito::findOrFail($request->origen_id);
        $destino = Pueblito::findOrFail($request->destino_id);

        $tramos = $salida->obtenerTramosDeViaje($origen->id, $destino->id);

        if ($tramos->isEmpty()) {
            return redirect()->route('pasajes.index')
                ->withErrors('No se pudo determinar el tramo del viaje.');
        }

        $asientosDisponibles = $salida->asientosDisponibles($origen->id, $destino->id);
        $tiposEncomienda = TipoEncomienda::all();
        foreach ($asientos as $asiento) {
            if (($asientosDisponibles[$asiento] ?? 'ocupado') !== 'libre') {
                return redirect()->route('pasajes.index')
                    ->withErrors("El asiento {$asiento} ya no está disponible.");
            }
        }

        $precioUnitario = $salida->calcularCostoPorTramos($origen->id, $destino->id);

        $tipos_documentos = TipoDocumentoPersona::whereNotIn('id', [2])->get();
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
            'billeteras_digitales',
            'cajas_emision',
            'user',
            'tiposEncomienda'
        ));
    }

    public function actualizarVentaReserva(Request $request, Pasaje $pasaje)
    {
        if ($pasaje->estado !== 'R') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar pasajes reservados. Este pasaje ya fue vendido o no está disponible para edición.'
            ], 422);
        }

        $request->validate([
            'accion' => 'required|in:reservar,vender',
            'tipo_documento_id' => 'required|array',
            'tipo_documento_id.*' => 'required|integer',
            'documento' => 'required|array',
            'documento.*' => 'required|string|max:20',
            'nombres' => 'required|array',
            'nombres.*' => 'required|string|max:200',
            'apellidos' => 'required|array',
            'apellidos.*' => 'required|string|max:200',
            'celular' => 'nullable|array',
            'telefono' => 'nullable|array',
            'correo' => 'nullable|array',
            'descuento_ids' => 'nullable|array',
            'precios_finales' => 'nullable|array',
            'sobre_equipaje_detalles' => 'nullable|array',
            'tipo_doc_sunat' => 'nullable',
            'numero_documento_id' => 'nullable|string|max:20',
            'razon_social' => 'nullable|string|max:255',
            'caja_id' => 'nullable|exists:caja,id',
            'metodo_pago_id' => 'nullable|integer',
            'pago_efectivo' => 'nullable|numeric|min:0',
            'pago_tarjeta' => 'nullable|numeric|min:0',
            'pago_yape' => 'nullable|numeric|min:0',
            'pago_plin' => 'nullable|numeric|min:0',
            'pago_transferencia' => 'nullable|numeric|min:0',
        ]);

        if (
            $request->accion === 'vender' &&
            Auth::user()->hasRole('Administrador') &&
            !$request->caja_id
        ) {
            throw ValidationException::withMessages([
                'caja_id' => 'Debe seleccionar una caja para realizar la venta.',
            ]);
        }

        try {
            DB::beginTransaction();

            $accion = $request->accion;
            $index = 0; // un solo pasajero en edición

            $documento = trim($request->documento[$index] ?? '');
            $nombres = trim($request->nombres[$index] ?? '');
            $apellidos = trim($request->apellidos[$index] ?? '');
            $celular = trim($request->celular[$index] ?? '');
            $telefono = $request->telefono[$index] ?? null;
            $correo = $request->correo[$index] ?? null;

            if (!$documento || !$nombres || !$apellidos) {
                throw ValidationException::withMessages([
                    "documento.$index" => "Faltan datos del pasajero.",
                ]);
            }

            $persona = Persona::updateOrCreate(
                ['documento' => $documento],
                [
                    'tipo_documento_id' => $request->tipo_documento_id[$index],
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'direccion' => $request->direccion,
                    'celular' => $celular,
                    'telefono' => $telefono,
                    'correo' => $correo,
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            Cliente::updateOrCreate(
                ['persona_id' => $persona->id],
                ['user_id' => Auth::id()]
            );

            // --- descuento, recalculado contra precio_pasaje guardado ---
            $precioBase = (float) $pasaje->precio_pasaje;
            $descuentoId = $request->descuento_ids[$index] ?? null;
            $precioFinalFront = (float) ($request->precios_finales[$index] ?? $precioBase);
            $descuentoMontoReal = 0;
            $esPromocion = false;

            if ($descuentoId) {
                $descuento = Descuento::find($descuentoId);

                if (!$descuento) {
                    throw ValidationException::withMessages([
                        "descuento_ids.$index" => "El descuento no existe.",
                    ]);
                }

                if ((int) $descuento->id === 1) {
                    $cantidad = Pasaje::whereHas('persona', function ($q) use ($documento) {
                        $q->where('documento', $documento);
                    })
                        ->where('origen_pueblito_id', $pasaje->origen_pueblito_id)
                        ->where('destino_pueblito_id', $pasaje->destino_pueblito_id)
                        ->whereIn('estado', ['V', 'F'])
                        ->where('id', '!=', $pasaje->id)
                        ->count();

                    if (($cantidad + 1) % 10 !== 0) {
                        throw ValidationException::withMessages([
                            "descuento_ids.$index" => "El descuento promocional solo aplica en el viaje número 10 del mismo tramo.",
                        ]);
                    }

                    $descuentoMontoReal = $precioBase;
                    $esPromocion = true;
                } elseif (!empty($descuento->monto_efectivo)) {
                    $descuentoMontoReal = (float) $descuento->monto_efectivo;
                } elseif (!empty($descuento->porcentaje)) {
                    $descuentoMontoReal = $precioBase * ((float) $descuento->porcentaje / 100);
                }
            }

            $precioFinalReal = max(0, $precioFinalFront);

            $totalSobreEquipaje = 0;
            $detallesEquipaje = $request->sobre_equipaje_detalles[$index] ?? [];

            foreach ($detallesEquipaje as $item) {
                $totalSobreEquipaje += (float) ($item['costo'] ?? 0);
            }

            $totalVenta = $precioFinalReal + $totalSobreEquipaje;
            $venta = $pasaje->venta; // null si nunca se vendió

            $emision = null;

            if ($accion === 'vender') {
                $origenNombre = Pueblito::find($pasaje->origen_pueblito_id)?->descripcion;
                $destinoNombre = Pueblito::find($pasaje->destino_pueblito_id)?->descripcion;

                $detalle = [[
                    'descripcion' => "Pasaje {$origenNombre} - {$destinoNombre} | Asiento {$pasaje->asiento_numero}",
                    'costo' => $precioFinalReal,
                    'descuento' => $descuentoMontoReal,
                ]];

                if ($request->filled('numero_documento_id')) {
                    $personaFacturacion = Persona::updateOrCreate(
                        ['documento' => $request->numero_documento_id],
                        [
                            'tipo_documento_id' => $request->tipo_documento_factura_id ?? 1,
                            'nombres' => $request->razon_social ?: 'CLIENTE VARIOS',
                            'direccion' => $request->direccion,
                            'estado' => 'A',
                            'fecha_creacion' => now(),
                        ]
                    );
                } else {
                    $personaFacturacion = $persona;
                }

                if (!$venta) {
                    $ventaService = app(VentaService::class);

                    $totalSobreEquipaje = 0;

                    foreach ($request->sobre_equipaje_detalles ?? [] as $grupo) {

                        foreach ($grupo as $item) {

                            $costo = (float)$item['costo'];

                            $detalles[] = [
                                'descripcion' => 'Sobreequipaje - ' . ($item['descripcion'] ?? ''),
                                'costo' => $costo,
                                'descuento' => 0,
                            ];

                            $totalSobreEquipaje += $costo;
                        }
                    }

                    $totalVenta += $totalSobreEquipaje;

                    $ventaData = $ventaService->crearVenta(
                        new Request([
                            'tipo_servicio_id' => 1,
                            'tipo_documento_factura_id' => $request->tipo_doc_sunat,
                            'numero_documento_id' => $personaFacturacion->documento,
                            'razon_social' => $personaFacturacion->nombres,
                            'total' => $totalVenta,
                            'caja_id' => $request->caja_id,
                            'detalles' => $detalle,
                            'origen_nombre' => $origenNombre,
                            'destino_nombre' => $destinoNombre,
                        ]),
                        Pasaje::class,
                        null
                    );

                    $venta = $ventaData['venta'];
                    $emision = app(VentaService::class)->emitirVenta($venta);
                } else {
                    $venta->update(['total' => $totalVenta]);
                }

                $pagos = [
                    1 => (float) $request->pago_efectivo,
                    2 => (float) $request->pago_tarjeta,
                    3 => (float) $request->pago_yape,
                    4 => (float) $request->pago_plin,
                    5 => (float) $request->pago_transferencia,
                ];

                $sumaPagos = array_sum($pagos);

                if (round($sumaPagos, 2) !== round($totalVenta, 2)) {
                    throw ValidationException::withMessages([
                        'pagos' => 'La suma de pagos no coincide con el total a cobrar.',
                    ]);
                }

                foreach ($pagos as $metodoPagoId => $monto) {
                    if ($monto <= 0) continue;

                    CajaDetalle::create([
                        'caja_id' => $request->caja_id,
                        'subtipo_movimiento_caja_id' => 1,
                        'metodo_pago_id' => $metodoPagoId,
                        'amount' => $monto,
                        'description' => "Venta de pasaje #{$venta->id}",
                        'anulado' => false,
                    ]);
                }
            }

            $pasaje->update([
                'persona_id' => $persona->id,
                'venta_id' => $venta?->id,
                'descuento_id' => $descuentoId,
                'es_promocion' => $esPromocion,
                'precio_cobrado' => $precioFinalReal,
                'estado' => $accion === 'vender' ? 'V' : 'R',
            ]);

            $pasaje->sobreEquipajes()->delete();

            foreach ($detallesEquipaje as $sobre) {
                PasajeSobreEquipaje::create([
                    'pasaje_id' => $pasaje->id,
                    'tipo_encomienda_id' => $sobre['tipo_encomienda_id'] ?? null,
                    'descripcion' => $sobre['descripcion'] ?? null,
                    'peso' => $sobre['peso'] ?? 0,
                    'costo' => $sobre['costo'] ?? 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $accion === 'vender' ? 'Venta confirmada.' : 'Reserva actualizada.',
                'ticket_url' => $venta ? route('ventas.tickets', $venta->id) : route('pasajes.ticket', $pasaje->id),
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

    public function buscarPasaje(Request $request)
    {
        $request->validate([
            'salida_id' => 'required|exists:salidas,id',
            'asiento' => 'required|integer|min:1',
        ]);

        $pasaje = Pasaje::where('salida_id', $request->salida_id)
            ->where('asiento_numero', $request->asiento)
            ->whereIn('pasajes.estado', ['R', 'V', 'F'])
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

    public function ticket(Pasaje $pasaje)
    {
        $pasaje->load([
            'persona',
            'usuario.persona',
            'origen.empresa',
            'destino',
            'venta.pagos.metodoPago',
            'salida.horario.ruta',
        ]);

        return view('pasajes.ticket', compact('pasaje'));
    }

    public function ticketsVenta(Venta $venta)
    {
        $venta->load([
            'caja.sucursal.empresa',
            'pasajes.persona',
            'pasajes.usuario.persona',
            'pasajes.origen',
            'pasajes.destino',
            'pagos.metodoPago',
        ]);

        return view('caja.ticket', compact('venta'));
    }
}
