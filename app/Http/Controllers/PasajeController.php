<?php

namespace App\Http\Controllers;

use App\Models\BilleteraDigital;
use App\Models\Cliente;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\Horario;
use App\Models\HorarioPunto;
use App\Models\HorarioTramo;
use App\Models\MetodoPago;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\TipoVehiculo;
use App\Models\TipoViaje;
use App\Services\VentaService;
use App\Services\PagoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class PasajeController extends Controller
{
    protected $ventaService;
    protected $pagoService;

    public function __construct(VentaService $ventaService, PagoService $pagoService)
    {
        $this->ventaService = $ventaService;
        $this->pagoService = $pagoService;
    }

    public function vender(Request $request)
    {
        $asientos = explode(',', $request->asientos);
        $horario = Horario::with([
            'punto_origen',
            'punto_destino',
            'tipo_vehiculo'
        ])->findOrFail($request->horario);
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $metodos_pago = MetodoPago::all();
        $billeteras_digitales = BilleteraDigital::all();
        $hoy = Carbon::now('America/Lima')->format("Y-m-d");
        return view('pasajes.venta', compact(
            'asientos',
            'horario',
            'tipos_documentos',
            'billeteras_digitales',
            'tipos_documentos_facturas',
            'metodos_pago',
            'hoy'
        ));
    }

    public function index_busqueda()
    {
        $pasajes = Pasaje::with([
            'persona',
            'horario.punto_origen',
            'horario.punto_destino',
            'venta'
        ])->get();

        $sucursales = Sucursal::where('estado', 'A')->get();
        return view('pasajes.busqueda', compact('pasajes', 'sucursales'));
    }

    public function listarVendidos(Request $request)
    {
        $query = Pasaje::with([
            'persona',
            'horario.punto_origen',
            'horario.punto_destino'
        ])->orderBy('created_at', 'desc');

        if ($request->dni) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('documento', 'like', '%' . $request->dni . '%');
            });
        }

        if ($request->fecha) {
            $query->whereHas('horario', function ($q) use ($request) {
                $q->whereDate('fecha_salida', $request->fecha);
            });
        }

        if ($request->origen) {
            $query->whereHas('horario', function ($q) use ($request) {
                $q->where('punto_origen_id', $request->origen);
            });
        }

        if ($request->destino) {
            $query->whereHas('horario', function ($q) use ($request) {
                $q->where('punto_destino_id', $request->destino);
            });
        }

        return response()->json($query->get());
    }
    protected function registrarCliente($personaId)
    {
        Cliente::firstOrCreate([
            'user_id' => Auth::id(),
            'persona_id' => $personaId,
        ]);
    }

    public function index()
    {
        $puntos_origen = Sucursal::where('estado', 'A')->get();
        $puntos_destino = Sucursal::where('estado', 'A')->get();
        $hoy = Carbon::now('America/Lima')->format("Y-m-d");
        $horarios = Horario::with(['tipo_vehiculo', 'punto_origen', 'punto_destino', 'tipo_viaje', 'fechas'])
            ->withCount('pasajes')
            ->get();

        return view('pasajes.index', compact('hoy', 'horarios', 'puntos_origen', 'puntos_destino'));
    }

    protected function validarTerminarVenta($request, $i)
    {
        $rules = [
            "tipo_documento_id.$i" => 'required|integer',
            "documento.$i" => 'required|string|max:20',
            "nombres.$i" => 'required|string|max:200',
            "apellidos.$i" => 'required|string|max:200',
            "celular.$i" => 'required|string',
            "pago_efectivo.$i" => 'nullable|numeric|min:0',
            "pago_billetera.$i" => 'nullable|numeric|min:0',
        ];

        $request->validate($rules);
    }

    protected function crearPasajeMultiple(Request $request, $personaId, $i, $estado, $cantidadPasajes)
    {
        $asiento = $request->asientos[$i];
        $horario_id = $request->horario_id[$i];

        $reserva = Pasaje::where('horario_id', $horario_id)
            ->where('asiento_numero', $asiento)
            ->where('estado', 'R')
            ->first();

        $horario = Horario::findOrFail($horario_id);
        $origenOrden = null;
        $destinoOrden = null;
        if ($horario->tipo_viaje_id == 2) {
            $origenOrden = HorarioPunto::where('horario_id', $horario_id)
                ->where('sucursal_id', $request->origen_id[$i])
                ->value('orden');

            $destinoOrden = HorarioPunto::where('horario_id', $horario_id)
                ->where('sucursal_id', $request->destino_id[$i])
                ->value('orden');

            $ocupado = $this->asientoOcupadoEnTramo($horario_id, $asiento, $origenOrden, $destinoOrden);
        } else {
            $ocupado = Pasaje::where('horario_id', $horario_id)
                ->where('asiento_numero', $asiento)
                ->whereIn('estado', ['V', 'R'])
                ->exists();
        }

        if ($ocupado) {
            throw new \Exception("El asiento $asiento ya está ocupado.");
        }


        $pdf = null;
        if ($request->hasFile("autorizacion_pdf.$i")) {
            $pdf = $request->file("autorizacion_pdf.$i")->store('pasajes', 'public');
        }

        $ventaData = null;
        $venta_id = null;

        if ($estado === 'V') {
            $horario = Horario::with(['punto_origen', 'punto_destino'])->findOrFail($horario_id);
            $precioPasaje = floatval($request->precio[$i] ?? 0);
            $descuento = $request->descuento[$i] ?? 0;

            $ventaData = $this->ventaService->crearVentaPasaje(
                $horario,
                $asiento,
                $precioPasaje,
                $descuento,
                $request->tipo_documento_factura_id ?? 1
            );

            $venta_id = $ventaData['venta']->id;

            if ($reserva) {
                $reserva->update([
                    'estado' => 'V',
                    'venta_id' => $venta_id,
                ]);

                if ($ventaData) {
                    $pagoData = [];

                    $pagoEfectivoTotal = floatval(str_replace(',', '.', $request->pago_efectivo ?? 0));
                    $pagoBilleteraTotal = floatval(str_replace(',', '.', $request->pago_billetera ?? 0));

                    $pagoEfectivo = $pagoEfectivoTotal / $cantidadPasajes;
                    $pagoBilletera = $pagoBilleteraTotal / $cantidadPasajes;

                    if ($pagoEfectivo > 0) {
                        $pagoData[] = [
                            'metodo_pago_id' => 1,
                            'total' => $pagoEfectivo,
                            'billetera_id' => null,
                        ];
                    }

                    if ($pagoBilletera > 0) {
                        $pagoData[] = [
                            'metodo_pago_id' => 2,
                            'total' => $pagoBilletera,
                            'billetera_id' => $request->billetera_id[$i] ?? null,
                        ];
                    }

                    $this->pagoService->registrarPagos(
                        $ventaData['venta']->id,
                        $pagoData,
                        Pasaje::class,
                        $reserva->id
                    );
                }
                return $reserva;
            }
        }

        if ($horario->tipo_viaje_id == 2) {
            $tramos = HorarioTramo::where('horario_id', $horario_id)
                ->whereHas('origen', fn($q) => $q->where('orden', '>=', $origenOrden))
                ->whereHas('destino', fn($q) => $q->where('orden', '<=', $destinoOrden))
                ->get();

            $primerPasaje = null;
            foreach ($tramos as $tramo) {
                $p = Pasaje::create([
                    'usuario_id'       => Auth::id(),
                    'persona_id'       => $personaId,
                    'horario_id'       => $horario_id,
                    'tramo_id'         => $tramo->id,
                    'asiento_numero'   => $asiento,
                    'pasajero_menor'   => isset($request->pasajero_menor[$i]) ? true : false,
                    'autorizacion_pdf' => $pdf,
                    'venta_id'         => $venta_id,
                    'estado'           => $estado,
                    'fecha_creacion'   => now(),
                ]);
                if (!$primerPasaje) $primerPasaje = $p;
            }
            $pasaje = $primerPasaje;
        } else {
            $pasaje = Pasaje::create([
                'usuario_id'       => Auth::id(),
                'persona_id'       => $personaId,
                'horario_id'       => $horario_id,
                'asiento_numero'   => $asiento,
                'pasajero_menor'   => isset($request->pasajero_menor[$i]) ? true : false,
                'autorizacion_pdf' => $pdf,
                'venta_id'         => $venta_id,
                'estado'           => $estado,
                'fecha_creacion'   => now(),
            ]);
        }

        if ($ventaData) {
            $pagoData = [];
            $pagoEfectivoTotal = floatval(str_replace(',', '.', $request->pago_efectivo ?? 0));
            $pagoBilleteraTotal = floatval(str_replace(',', '.', $request->pago_billetera ?? 0));

            $pagoEfectivo = $pagoEfectivoTotal / $cantidadPasajes;
            $pagoBilletera = $pagoBilleteraTotal / $cantidadPasajes;

            if ($pagoEfectivo > 0) {
                $pagoData[] = [
                    'metodo_pago_id' => 1,
                    'total' => $pagoEfectivo,
                    'billetera_id' => null
                ];
            }

            if ($pagoBilletera > 0) {
                $pagoData[] = [
                    'metodo_pago_id' => 2,
                    'total' => $pagoBilletera,
                    'billetera_id' => $request->billetera_id[$i] ?? null
                ];
            }
            $this->pagoService->registrarPagos(
                $ventaData['venta']->id,
                $pagoData,
                Pasaje::class,
                $pasaje->id
            );
        }


        return $pasaje;
    }

    public function guardar(Request $request)
    {
        $accion = $request->accion;

        try {
            DB::beginTransaction();

            $asientos = $request->asientos;

            $estado = ($accion === 'terminar') ? 'V' : 'R';

            $cantidadPasajes = count($asientos);

            foreach ($asientos as $index => $asiento) {

                if ($accion === 'terminar') {
                    $this->validarTerminarVenta($request, $index);

                    $persona = Persona::updateOrCreate(
                        ['documento' => $request->documento[$index] ?? null],
                        [
                            'tipo_documento_id' => $request->tipo_documento_id[$index] ?? 1,
                            'nombres' => $request->nombres[$index] ?? null,
                            'apellidos' => $request->apellidos[$index] ?? null,
                            'telefono' => $request->telefono[$index] ?? null,
                            'celular' => $request->celular[$index] ?? null,
                            'correo' => $request->direccion[$index] ?? null,
                            'fecha_creacion' => now(),

                        ]
                    );

                    $this->registrarCliente($persona->id);


                    $this->crearPasajeMultiple(
                        $request,
                        $persona->id,
                        $index,
                        $estado,
                        $cantidadPasajes
                    );
                }
            }

            DB::commit();

            Log::info('Pasajes guardados exitosamente', ['estado' => $estado]);

            return response()->json([
                'success' => true,
                'message' => $estado === 'V' ? 'Venta realizada correctamente' : 'Reserva realizada correctamente',
                'redirect' => route('pasajes.index')
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Error al guardar pasajes', [
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    protected function crearPasaje(Request $request, $personaId, $user_id)
    {
        return DB::transaction(function () use ($request, $personaId, $user_id) {
            $autorizacionPath = null;
            if ($request->hasFile('autorizacion_pdf')) {
                $autorizacionPath = $request->file('autorizacion_pdf')->store('pasajes', 'public');
            }

            $ventaData = $this->ventaService->crearVenta(
                $request->all(),
                Pasaje::class,
                null
            );

            $pasaje = Pasaje::create([
                'usuario_id' => $user_id,
                'persona_id' => $personaId,
                'horario_id' => $request->horario_id,
                'asiento_numero' => $request->asiento_numero,
                'pasajero_menor' => $request->pasajero_menor ?? false,
                'autorizacion_pdf' => $autorizacionPath,
                'estado' => 'R',
                'fecha_creacion' => now(),
                'venta_id' => $ventaData['venta']->id,
            ]);

            if ($ventaData) {
                $pagoData = [];

                $pagoEfectivo = floatval(str_replace(',', '.', $request->pago_efectivo ?? 0));
                $pagoBilletera = floatval(str_replace(',', '.', $request->pago_billetera ?? 0));

                if ($pagoEfectivo > 0) {
                    $pagoData[] = [
                        'metodo_pago_id' => 1,
                        'total' => $pagoEfectivo,
                        'billetera_id' => null
                    ];
                }

                if ($pagoBilletera > 0) {
                    $pagoData[] = [
                        'metodo_pago_id' => 2,
                        'total' => $pagoBilletera,
                        'billetera_id' => $request->billetera_id ?? null
                    ];
                }

                $this->pagoService->registrarPagos(
                    $ventaData['venta']->id,
                    $pagoData,
                    Pasaje::class,
                    $pasaje->id
                );
            }
            return $pasaje;
        });
    }
    public function asientosHorario(Horario $horario, Request $request)
    {
        $capacidad = $horario->tipo_vehiculo->capacidad;
        $asientos = [];
        $precios = [];

        $esConTramos = $horario->tipo_viaje_id == 2
            && $request->filled('origen_id')
            && $request->filled('destino_id');

        if ($esConTramos) {
            $origenOrden = HorarioPunto::where('horario_id', $horario->id)
                ->where('sucursal_id', $request->origen_id)
                ->value('orden');

            $destinoOrden = HorarioPunto::where('horario_id', $horario->id)
                ->where('sucursal_id', $request->destino_id)
                ->value('orden');

            if (!$origenOrden || !$destinoOrden) {
                return response()->json(['error' => 'Tramo no encontrado'], 422);
            }
            $costoTramo = HorarioTramo::where('horario_id', $horario->id)
                ->whereHas('origen', fn($q) => $q->where('orden', '>=', $origenOrden))
                ->whereHas('destino', fn($q) => $q->where('orden', '<=', $destinoOrden))
                ->sum('costo_tramo');

            for ($i = 1; $i <= $capacidad; $i++) {
                $ocupado = $this->asientoOcupadoEnTramo(
                    $horario->id,
                    $i,
                    $origenOrden,
                    $destinoOrden
                );
                $asientos[$i] = $ocupado ? 'ocupado' : 'libre';
                $precios[$i] = $costoTramo;
            }
        } else {
            $pasajes = $horario->pasajes()->whereIn('estado', ['V', 'R'])->get();
            for ($i = 1; $i <= $capacidad; $i++) {
                $pasaje = $pasajes->firstWhere('asiento_numero', $i);
                $asientos[$i] = match ($pasaje?->estado) {
                    'R' => 'reservado',
                    'V' => 'ocupado',
                    default => 'libre'
                };
                $precios[$i] = $horario->costo_base ?? 0;
            }
        }

        $rawSvg = file_get_contents(storage_path('app/public/' . $horario->tipo_vehiculo->ruta_svg));
        $cleanSvg = preg_replace('/<\?xml.*?\?>/is', '', $rawSvg);
        $cleanSvg = preg_replace('/<!DOCTYPE.*?>/is', '', $cleanSvg);

        return response()->json([
            'asientos' => $asientos,
            'precios'  => $precios,
            'svg'      => $cleanSvg
        ]);
    }

    public function reservar(Request $request)
    {
        // Log para debugging
        Log::info('Datos recibidos en reservar:', $request->all());

        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'asiento_numero' => 'required|integer|min:1',
            'persona.documento' => 'nullable|string|max:20',
            'persona.nombres' => 'nullable|string|max:200',
            'persona.apellidos' => 'nullable|string|max:200',
        ]);

        try {

            $horario = Horario::findOrFail($request->horario_id);

            if ($horario->tipo_viaje_id == 2) {
                $request->validate([
                    'origen_id' => 'required|exists:sucursales,id',
                    'destino_id' => 'required|exists:sucursales,id',
                ]);

                $origenOrden = HorarioPunto::where('horario_id', $horario->id)
                    ->where('sucursal_id', $request->origen_id)
                    ->value('orden');

                $destinoOrden = HorarioPunto::where('horario_id', $horario->id)
                    ->where('sucursal_id', $request->destino_id)
                    ->value('orden');

                $ocupado = $this->asientoOcupadoEnTramo(
                    $horario->id,
                    $request->asiento_numero,
                    $origenOrden,
                    $destinoOrden
                );

                if ($ocupado) {
                    return response()->json([
                        'success' => false,
                        'message' => "El asiento {$request->asiento_numero} ya está ocupado en ese tramo."
                    ], 422);
                }

                $tramos = HorarioTramo::where('horario_id', $horario->id)
                    ->whereHas('origen', fn($q) => $q->where('orden', '>=', $origenOrden))
                    ->whereHas('destino', fn($q) => $q->where('orden', '<=', $destinoOrden))
                    ->get();

                $primerPasaje = null;
                foreach ($tramos as $tramo) {
                    $p = Pasaje::create([
                        'usuario_id'     => Auth::id(),
                        'horario_id'     => $horario->id,
                        'tramo_id'       => $tramo->id,
                        'asiento_numero' => $request->asiento_numero,
                        'persona_id'     => $persona?->id,
                        'pasajero_menor' => false,
                        'estado'         => 'R',
                        'fecha_creacion' => now(),
                        'venta_id'       => null,
                    ]);
                    if (!$primerPasaje) $primerPasaje = $p;
                }

                return response()->json([
                    'success'        => true,
                    'pasaje_id'      => $primerPasaje->id,
                    'asiento_numero' => $request->asiento_numero,
                    'message'        => 'Asiento reservado correctamente'
                ]);
            }

            $existe = Pasaje::where('horario_id', $request->horario_id)
                ->where('asiento_numero', $request->asiento_numero)
                ->whereIn('estado', ['V', 'R'])
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => "El asiento {$request->asiento_numero} ya está ocupado."
                ], 422);
            }

            $persona = null;
            if ($request->has('persona.documento') && !empty($request->input('persona.documento'))) {
                $persona = Persona::updateOrCreate(
                    ['documento' => $request->input('persona.documento')],
                    [
                        'tipo_documento_id' => $request->input('persona.tipo_documento_id', 1),
                        'nombres'  => $request->input('persona.nombres'),
                        'apellidos' => $request->input('persona.apellidos'),
                        'celular'  => $request->input('persona.celular'),
                        'estado'   => 'A',
                        'fecha_creacion' => now(),
                    ]
                );
                $this->registrarCliente($persona->id);
            }

            $pasaje = Pasaje::create([
                'usuario_id'     => Auth::id(),
                'horario_id'     => $request->horario_id,
                'asiento_numero' => $request->asiento_numero,
                'persona_id'     => $persona?->id,
                'pasajero_menor' => false,
                'estado'         => 'R',
                'fecha_creacion' => now(),
                'venta_id'       => null,
            ]);

            return response()->json([
                'success'        => true,
                'pasaje_id'      => $pasaje->id,
                'asiento_numero' => $request->asiento_numero,
                'message'        => 'Asiento reservado correctamente'
            ]);
        } catch (ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $th) {
            Log::error('Error al reservar:', [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reservar: ' . $th->getMessage()
            ], 500);
        }
    }

    public function editar(Pasaje $pasaje)
    {
        $puntos_origen = Sucursal::where('estado', 'A')->get();
        $puntos_destino = Sucursal::where('estado', 'A')->get();
        $tipos_viaje = TipoViaje::all();
        $tipos_vehiculos = TipoVehiculo::all();

        $pasaje->load([
            'persona',
            'horario.punto_origen',
            'horario.punto_destino',
            'horario.tipo_vehiculo',
            'horario.fechas',
            'venta.pagos.billetera',
            'venta.pagos.metodoPago',
            'venta.persona', // AGREGADO: Cargar la persona de la venta
            'venta.tipoDocumentoFactura' // AGREGADO: Cargar tipo documento
        ]);

        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $metodos_pago = MetodoPago::all();
        $billeteras_digitales = BilleteraDigital::all();

        $asientos = [$pasaje->asiento_numero];
        $horario = $pasaje->horario;

        return view('pasajes.editar', compact(
            'pasaje',
            'asientos',
            'horario',
            'tipos_documentos',
            'billeteras_digitales',
            'tipos_documentos_facturas',
            'metodos_pago',
            'tipos_viaje',
            'tipos_vehiculos',
            'puntos_origen',
            'puntos_destino'
        ));
    }

    public function buscarPasaje(Request $request)
    {
        try {
            $pasaje = Pasaje::where('horario_id', $request->horario_id)
                ->where('asiento_numero', $request->asiento)
                ->where('estado', 'R')
                ->first();

            if ($pasaje) {
                return response()->json([
                    'success' => true,
                    'pasaje_id' => $pasaje->id
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Pasaje no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar pasaje: ' . $e->getMessage()
            ], 500);
        }
    }

    public function filtrarHorarios(Request $request)
    {
        $query = Horario::with(['tipo_vehiculo', 'punto_origen', 'punto_destino'])
            ->withCount('pasajes');

        if ($request->fecha) {
            $query->whereDate('fecha_salida', $request->fecha);
        }

        if ($request->origen) {
            $query->where('punto_origen_id', $request->origen);
        }

        if ($request->destino) {
            $query->where('punto_destino_id', $request->destino);
        }

        $horarios = $query->get();

        return response()->json(['horarios' => $horarios]);
    }

    public function abordo(Pasaje $pasaje)
    {
        if ($pasaje->estado !== 'V') {
            return response()->json([
                'success' => false,
                'message' => 'Solo pasajes vendidos pueden finalizarse'
            ], 422);
        }

        $pasaje->update([
            'estado' => 'F',
            'fecha_inactivacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasajero abordó correctamente'
        ]);
    }
    public function show(Pasaje $pasaje)
    {
        $pasaje->load([
            'persona',
            'horario.punto_origen',
            'horario.punto_destino',
            'horario.tipo_vehiculo',
            'venta.pagos.metodoPago'
        ]);

        return response()->json([
            'id' => $pasaje->id,
            'estado' => $pasaje->estado,
            'asiento' => $pasaje->asiento_numero,
            'fecha' => $pasaje->horario->fecha_salida,
            'hora' => $pasaje->horario->hora_embarque,
            'origen' => $pasaje->horario->punto_origen->nombre_comercial,
            'destino' => $pasaje->horario->punto_destino->nombre_comercial,
            'pasajero' => $pasaje->persona ? [
                'documento' => $pasaje->persona->documento,
                'nombres' => $pasaje->persona->nombres,
                'apellidos' => $pasaje->persona->apellidos,
                'celular' => $pasaje->persona->celular,
            ] : null,
            'pagos' => $pasaje->venta?->pagos ?? [],
        ]);
    }

    public function noAbordo(Pasaje $pasaje)
    {
        if ($pasaje->estado !== 'V') {
            return response()->json([
                'success' => false,
                'message' => 'Solo pasajes vendidos pueden cancelarse'
            ], 422);
        }

        $pasaje->update([
            'estado' => 'X',
            'fecha_inactivacion' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pasajero marcado como NO abordó'
        ]);
    }


    public function cambiarHorario(Pasaje $pasaje)
    {
        $pasaje->load(['persona', 'horario.punto_origen', 'horario.punto_destino', 'horario.tipo_vehiculo']);

        $horarios = Horario::where('punto_origen_id', $pasaje->horario->punto_origen_id)
            ->where('punto_destino_id', $pasaje->horario->punto_destino_id)
            ->where('fecha_salida', '>=', now()->toDateString())
            ->with(['punto_origen', 'punto_destino', 'tipo_vehiculo'])
            ->orderBy('fecha_salida')
            ->orderBy('hora_salida')
            ->get();

        return view('pasajes.cambiar-horario', compact('pasaje', 'horarios'));
    }

    private function asientoOcupadoEnTramo($horarioId, $asientoNumero, $origenOrden, $destinoOrden)
    {
        return Pasaje::where('horario_id', $horarioId)
            ->where('asiento_numero', $asientoNumero)
            ->whereIn('estado', ['V', 'R'])
            ->whereHas('tramo', function ($q) use ($origenOrden, $destinoOrden) {
                $q->whereHas(
                    'origen',
                    fn($q2) =>
                    $q2->where('orden', '<', $destinoOrden)
                )
                    ->whereHas(
                        'destino',
                        fn($q2) =>
                        $q2->where('orden', '>', $origenOrden)
                    );
            })
            ->exists();
    }

    public function asientosDisponibles(Horario $horario)
    {
        $asientosOcupados = Pasaje::where('horario_id', $horario->id)
            ->whereIn('estado', ['V', 'R'])
            ->pluck('asiento_numero')
            ->toArray();

        $totalAsientos = $horario->tipo_vehiculo->cantidad_asientos ?? 40;

        $asientosDisponibles = [];
        for ($i = 1; $i <= $totalAsientos; $i++) {
            $asientosDisponibles[] = [
                'numero' => $i,
                'disponible' => !in_array($i, $asientosOcupados)
            ];
        }

        return response()->json([
            'asientos' => $asientosDisponibles,
            'precio' => $horario->costo_pasaje
        ]);
    }

    public function actualizarHorario(Request $request, Pasaje $pasaje)
    {
        $request->validate([
            'nuevo_horario_id' => 'required|exists:horarios,id',
            'nuevo_asiento_numero' => 'required|integer|min:1',
        ]);

        $nuevoHorario = Horario::findOrFail($request->nuevo_horario_id);
        $nuevoAsiento = $request->nuevo_asiento_numero;

        $asientoOcupado = Pasaje::where('horario_id', $nuevoHorario->id)
            ->where('asiento_numero', $nuevoAsiento)
            ->whereIn('estado', ['V', 'R'])
            ->exists();

        if ($asientoOcupado) {
            return response()->json([
                'success' => false,
                'message' => 'El asiento seleccionado ya no está disponible'
            ], 422);
        }

        $horarioAnterior = $pasaje->horario_id;
        $asientoAnterior = $pasaje->asiento_numero;

        $pasaje->update([
            'horario_id' => $nuevoHorario->id,
            'asiento_numero' => $nuevoAsiento,
        ]);

        if ($pasaje->venta) {
            $diferenciaPrecio = $nuevoHorario->costo_pasaje - $pasaje->horario->costo_pasaje;

            if ($diferenciaPrecio != 0) {
                $pasaje->venta->update([
                    'total' => $pasaje->venta->total + $diferenciaPrecio,
                    'subtotal' => $pasaje->venta->subtotal + $diferenciaPrecio,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Horario y asiento actualizados correctamente',
            'redirect' => route('pasajes.index')
        ]);
    }
}
