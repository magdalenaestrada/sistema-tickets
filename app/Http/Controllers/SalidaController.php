<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Ruta;
use App\Models\Salida;
use App\Models\TipoVehiculo;
use App\Models\Vehiculo;
use App\Services\PdfService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SalidaController extends Controller
{
    public function index()
    {
        $rutas = Ruta::all();

        $vehiculos = Vehiculo::with('tipo_vehiculo')->where('estado', 'A')->get();
        $conductores = Empleado::with('persona')->where('cargo_id', 3)->get();
        $tiposVehiculo = TipoVehiculo::all();
        $hoy = now()->toDateString();
        $horaActual = now()->format('H:i:s');

        $horariosSalida = Horario::with(['ruta', 'tipo_vehiculo'])
            ->join('rutas', 'horarios.ruta_id', '=', 'rutas.id')
            ->leftJoin('salidas', function ($join) use ($hoy) {
                $join->on('horarios.id', '=', 'salidas.horario_id')
                    ->where('salidas.fecha_salida', '=', $hoy);
            })
            ->where(function ($q) use ($horaActual) {
                $q->whereNull('salidas.id') // no existe salida para hoy
                    ->orWhere('horarios.hora_salida', '>=', $horaActual); // aún no ha pasado la hora
            })
            ->orderBy('horarios.hora_salida')
            ->select('horarios.*')
            ->distinct()
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'tipo_vehiculo_id' => $h->tipo_vehiculo_id,
                    'nombre' => ($h->ruta?->nombre ?? 'Sin ruta')
                        . ' - '
                        . ($h->hora_formateada ?? '')
                        . ' - '
                        . ($h->tipo_vehiculo?->descripcion ?? ''),
                ];
            });
        return view('salidas.index', compact('vehiculos', 'tiposVehiculo', 'conductores', 'horariosSalida', 'rutas'));
    }

    public function index_vendedor()
    {
        // Sucursal del vendedor autenticado (ajusta según tu relación real)
        $sucursalId = auth()->user()->empleado->sucursal_id;

        $rutas = Ruta::all();

        $vehiculos = Vehiculo::with('tipo_vehiculo')->where('estado', 'A')->get();


        $conductores = Empleado::with('persona')
            ->where('cargo_id', 3)
            ->get();

        $tiposVehiculo = TipoVehiculo::all();
        $hoy = now()->toDateString();
        $horaActual = now()->format('H:i:s');

        $horariosSalida = Horario::with(['ruta', 'tipo_vehiculo'])
            ->join('rutas', 'horarios.ruta_id', '=', 'rutas.id')
            ->leftJoin('salidas', function ($join) use ($hoy) {
                $join->on('horarios.id', '=', 'salidas.horario_id')
                    ->where('salidas.fecha_salida', '=', $hoy);
            })
            ->where(function ($q) use ($horaActual) {
                $q->whereNull('salidas.id') // no existe salida para hoy
                    ->orWhere('horarios.hora_salida', '>=', $horaActual); // aún no ha pasado la hora
            })
            ->orderBy('horarios.hora_salida')
            ->select('horarios.*')
            ->distinct()
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'tipo_vehiculo_id' => $h->tipo_vehiculo_id,
                    'nombre' => ($h->ruta?->nombre ?? 'Sin ruta')
                        . ' - '
                        . ($h->hora_formateada ?? '')
                        . ' - '
                        . ($h->tipo_vehiculo?->descripcion ?? ''),
                ];
            });

        return view('salidas.index-vendedor', compact('vehiculos', 'tiposVehiculo', 'conductores', 'horariosSalida', 'rutas'));
    }

    public function datatable(Request $request)
    {
        $nowDate = now()->format('Y-m-d');
        $nowTime = now()->format('H:i:s');

        $isAdmin = auth()->user()->hasRole('Administrador');
        $sucursalId = auth()->user()->empleado->sucursal_id ?? null;

        $salidas = Salida::with([
            'horario.ruta.puntos.pueblito.sucursal',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ])
            ->join('horarios', 'salidas.horario_id', '=', 'horarios.id')
            ->join('rutas', 'horarios.ruta_id', '=', 'rutas.id')
            ->select('salidas.*')
            ->selectRaw("
        CASE 
            WHEN salidas.fecha_salida < ? THEN 1
            WHEN salidas.fecha_salida = ? AND horarios.hora_salida < ? THEN 1
            ELSE 0
        END as vencida
    ", [$nowDate, $nowDate, $nowTime])
            ->selectRaw("
        CASE 
            WHEN (salidas.fecha_salida < ? OR (salidas.fecha_salida = ? AND horarios.hora_salida < ?)) 
                 AND salidas.estado IN ('programado', 'reprogramado') THEN 2
            WHEN salidas.estado = 'programado' THEN 0
            ELSE 1
        END as orden_prioridad
    ", [$nowDate, $nowDate, $nowTime]);

        if ($request->filled('estado')) {
            if ($request->estado === 'vencido') {
                $salidas->where(function ($q) use ($nowDate, $nowTime) {
                    $q->whereIn('salidas.estado', ['programado', 'reprogramado'])
                        ->where(function ($q2) use ($nowDate, $nowTime) {
                            $q2->where('salidas.fecha_salida', '<', $nowDate)
                                ->orWhere(function ($q3) use ($nowDate, $nowTime) {
                                    $q3->where('salidas.fecha_salida', '=', $nowDate)
                                        ->where('horarios.hora_salida', '<', $nowTime);
                                });
                        });
                });
            } else {
                $salidas->where('salidas.estado', $request->estado);
            }
        }

        if ($request->filled('ruta_id')) {
            $salidas->where('rutas.id', $request->ruta_id);
        }

        return DataTables::of($salidas)
            ->orderColumn('vencida', 'vencida $1')
            ->order(function ($query) {
                $query->orderBy('orden_prioridad', 'asc')
                    ->orderBy('salidas.fecha_salida', 'asc')
                    ->orderBy('horarios.hora_salida', 'asc');
            })
            ->addColumn('ruta', function ($salida) {
                return $salida->horario?->ruta?->nombre ?? '-';
            })
            ->addColumn('hora_salida', function ($salida) {
                return $salida->horario?->hora_formateada ?? '-';
            })
            ->addColumn('hora_llegada', function ($salida) {
                return $salida->horario?->hora_llegada ?? '-';
            })
            ->addColumn('fecha_formateada', function ($salida) {
                return $salida->fecha_formateada;
            })
            ->addColumn('estado_badge', function ($salida) {

                if ($salida->estado === 'programado' && $salida->horario) {

                    $fechaHoraSalida = $salida->fecha_salida
                        ->copy()
                        ->setTimeFromTimeString($salida->horario->hora_formateada);

                    if (now()->gte($fechaHoraSalida->copy()->addMinutes(20))) {
                        return '<span class="badge bg-secondary">VENCIDO</span>';
                    }
                }

                return match ($salida->estado) {
                    'en_ruta'      => '<span class="badge bg-warning">EN RUTA</span>',
                    'programado'   => '<span class="badge bg-primary">PROGRAMADO</span>',
                    'finalizado'   => '<span class="badge bg-success">FINALIZADO</span>',
                    'cancelado'    => '<span class="badge bg-danger">CANCELADO</span>',
                    'reprogramado' => '<span class="badge bg-info">REPROGRAMADO</span>',
                    default        => '',
                };
            })
            ->addColumn('acciones', function ($salida) use ($isAdmin, $sucursalId) {

                $botones = '
        <button class="btn btn-light btn-xs ver" data-id="' . $salida->id . '">
            <i class="link-icon" data-lucide="info"></i>
        </button>
    ';

                if ($isAdmin) {
                    // Admin: solo Ver + Editar
                    $botones .= '
            <button class="btn btn-warning btn-xs editar" data-id="' . $salida->id . '">
                <i class="link-icon" data-lucide="pen"></i>
            </button>
        ';

                    return $botones;
                }

                $puntos = $salida->horario?->ruta?->puntos;

                $puntosOrdenados = $puntos && $puntos->count()
                    ? $puntos->sortBy('orden')->values()
                    : null;

                $puntoInicio = $puntosOrdenados?->first();

                $origenSucursalId = $puntoInicio?->pueblito?->sucursal_id;
                $destinoSucursalId = $puntosOrdenados?->last()?->pueblito?->sucursal_id;

                if (in_array($salida->estado, ['programado', 'reprogramado'])) {

                    $puedeIniciar = $origenSucursalId
                        && (int) $origenSucursalId === (int) $sucursalId;

                    if ($puedeIniciar) {
                        $botones .= '
            <button class="btn btn-success btn-xs iniciar-ruta"
                data-id="' . $salida->id . '"
                title="Iniciar ruta">
                <i class="link-icon" data-lucide="rocket"></i>
            </button>
        ';
                    }
                }
                if ($salida->estado === 'en_ruta') {
                    $puedeFinalizar = $destinoSucursalId && (int) $destinoSucursalId === (int) $sucursalId;

                    if ($puedeFinalizar) {
                        $botones .= '
                <button class="btn btn-primary btn-xs finalizar-ruta" data-id="' . $salida->id . '" title="Finalizar ruta">
                    <i class="link-icon" data-lucide="flag"></i>
                </button>
            ';
                    }
                }

                return $botones;
            })
            ->rawColumns(['acciones', 'estado_badge'])
            ->addIndexColumn()
            ->make(true);
    }

    public function manifiestoPasajeros(Salida $salida, PdfService $pdfService, Request $request)
    {
        $salida->load([
            'horario.ruta.puntos.pueblito',
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'pasajes.persona.tipoDocumento',
            'pasajes.origen',
            'pasajes.destino',
            'pasajes.venta',
        ]);

        $empresa = Empresa::first();
        $user = auth()->user();

        $sucursalId = $user->hasRole('Administrador')
            ? $request->sucursal_id
            : $user->sucursal_id;

        abort_unless($sucursalId, 422, 'Debe indicar una sucursal.');

        $perteneceARuta = $salida->sucursalesRuta()->contains('id', $sucursalId);
        abort_unless($perteneceARuta, 404, 'La sucursal no pertenece a la ruta.');

        $datos = $salida->datosManifiesto($sucursalId);
        $origenNombre  = $datos['origen']  ?? 'Sin origen definido';
        $destinoNombre = $datos['destino'] ?? 'Sin destino definido';

        $pasajes = $salida->pasajerosEnTramo($sucursalId); // puede venir vacío, está bien

        $capacidad = $salida->horario->tipo_vehiculo->capacidad
            ?? $salida->horario->tipo_vehiculo->asientos
            ?? 46;

        $html = view('salidas.manifiestos.pasajeros', [
            'salida'        => $salida,
            'empresa'       => $empresa,
            'pasajes'       => $pasajes,
            'origenNombre'  => $datos['origen'] ?? '',
            'destinoNombre' => $datos['destino'] ?? '',
            'capacidad'     => $capacidad ?? '',
        ])->render();

        return $pdfService->generar(
            $html,
            "manifiesto_pasajeros_{$salida->id}.pdf",
            'P'
        );
    }

    public function recursosDisponibles(Salida $salida)
    {
        $tipoVehiculoId = $salida->horario->tipo_vehiculo_id;

        $vehiculosOcupados = Salida::where('id', '!=', $salida->id)
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->whereNotNull('vehiculo_id')
            ->pluck('vehiculo_id');

        $vehiculos = Vehiculo::with('tipo_vehiculo')
            ->where('tipo_vehiculo_id', $tipoVehiculoId)
            // ->where(function ($q) use ($vehiculosOcupados, $salida) {
            //     $q->whereNotIn('id', $vehiculosOcupados)
            //         ->orWhere('id', $salida->vehiculo_id);
            // })
            ->get();

        $ocupadosPrincipal = Salida::where('id', '!=', $salida->id)
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->whereNotNull('conductor_principal_id')
            ->pluck('conductor_principal_id');

        $ocupadosSecundario = Salida::where('id', '!=', $salida->id)
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->whereNotNull('conductor_secundario_id')
            ->pluck('conductor_secundario_id');

        $conductoresOcupados = $ocupadosPrincipal->merge($ocupadosSecundario)->unique();

        $permitidosDeEstaSalida = collect([
            $salida->conductor_principal_id,
            $salida->conductor_secundario_id,
        ])->filter();

        $conductores = Empleado::with('persona')
            ->where("cargo_id", 3)
            // ->where(function ($q) use ($conductoresOcupados, $permitidosDeEstaSalida) {
            //     $q->whereNotIn('id', $conductoresOcupados)
            //         ->orWhereIn('id', $permitidosDeEstaSalida);
            // })
            ->get();

        return response()->json([
            'vehiculos'   => $vehiculos,
            'conductores' => $conductores,
        ]);
    }

    public function manifiestoPasajerosTodos(Salida $salida, PdfService $pdfService)
    {
        abort_unless(
            $salida->estado === 'finalizado',
            403,
            'Solo se pueden imprimir todos los manifiestos de una salida finalizada.'
        );

        $salida->load([
            'horario.ruta.puntos.pueblito',
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'pasajes.persona.tipoDocumento',
            'pasajes.origen',
            'pasajes.destino',
            'pasajes.venta',
        ]);

        $empresa = Empresa::first();
        $sucursales = $salida->sucursalesRuta();

        abort_if($sucursales->isEmpty(), 404, 'La ruta no tiene sucursales asignadas.');

        $capacidad = $salida->horario->tipo_vehiculo->capacidad
            ?? $salida->horario->tipo_vehiculo->asientos
            ?? 46;

        $bloques = [];

        foreach ($sucursales as $sucursal) {
            $datos = $salida->datosManifiesto($sucursal->id);
            if (!$datos) {
                continue;
            }

            $bloques[] = [
                'salida'        => $salida,
                'empresa'       => $empresa,
                'pasajes'       => $salida->pasajerosEnTramo($sucursal->id),
                'origenNombre'  => $datos['origen'],
                'destinoNombre' => $datos['destino'],
                'capacidad'     => $capacidad,
            ];
        }

        $html = view('salidas.manifiestos.pasajeros_todos', compact('bloques'))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_pasajeros_todas_sucursales_{$salida->id}.pdf",
            'P'
        );
    }

    public function sucursalesRuta(Salida $salida)
    {
        return response()->json(
            $salida->sucursalesRuta()->map(fn($s) => [
                'id' => $s->id,
                'nombre' => $s->nombre_comercial,
            ])
        );
    }


    public function manifiestoPasajerosReal(Salida $salida, PdfService $pdfService)
    {


        $salida->load([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'pasajes.persona.tipoDocumento',
            'pasajes.origen',
            'pasajes.destino',
            'pasajes.venta',
        ]);

        $empresa = Empresa::first();

        $puntos = $salida->horario->ruta->puntos->sortBy('orden')->values();
        $origenNombre = $puntos->first()?->pueblito?->descripcion ?? '-';
        $destinoNombre = $puntos->last()?->pueblito?->descripcion ?? '-';

        $pasajes = $salida->pasajes
            ->whereIn('estado', ['V', 'F'])
            ->sortBy('asiento_numero')
            ->values();

        $capacidad = $salida->horario->tipo_vehiculo->capacidad
            ?? $salida->horario->tipo_vehiculo->asientos
            ?? 46;

        $html = view('salidas.manifiestos.pasajeros_real', compact(
            'salida',
            'empresa',
            'pasajes',
            'origenNombre',
            'destinoNombre',
            'capacidad'
        ))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_pasajeros_{$salida->id}.pdf",
            'P'
        );
    }

    public function destroyBulk(Request $request)
    {
        $ids = $request->ids;

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'IDs inválidos'], 422);
        }

        Salida::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Eliminadas correctamente']);
    }

    public function manifiestoEncomiendas(Salida $salida, PdfService $pdfService, Request $request)
    {
        $salida->load([
            'horario.ruta.puntos.pueblito',
            'horario.ruta.puntos.sucursal',
            'horario.tipo_vehiculo',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'encomiendas.emisor',   // ajusta a tus relaciones reales
            'encomiendas.receptor',
            'encomiendas.receptor2',
            'encomiendas.origenPueblito',
            'encomiendas.destinoPueblito',
            'encomiendas.venta',
        ]);

        $empresa = Empresa::first();
        $user = auth()->user();

        $sucursalId = $user->hasRole('Administrador')
            ? $request->sucursal_id
            : $user->sucursal_id;

        abort_unless($sucursalId, 422, 'Debe indicar una sucursal.');

        $perteneceARuta = $salida->sucursalesRuta()->contains('id', $sucursalId);
        abort_unless($perteneceARuta, 404, 'La sucursal no pertenece a la ruta.');

        $datos = $salida->datosManifiesto($sucursalId);
        $encomiendas = $salida->encomiendasEnTramo($sucursalId)->where('sobre_equipaje', false);


        $origenNombre  = $datos['origen']  ?? 'Sin origen definido';
        $destinoNombre = $datos['destino'] ?? 'Sin destino definido';


        $html = view('salidas.manifiestos.encomiendas', [
            'salida'        => $salida,
            'empresa'       => $empresa,
            'encomiendas'   => $encomiendas,
            'origenNombre'  => $origenNombre,
            'destinoNombre' => $destinoNombre,
        ])->render();

        return $pdfService->generar(
            $html,
            "manifiesto_encomiendas_{$salida->id}.pdf",
            'P'
        );
    }

    public function manifiestoBodega(Salida $salida, PdfService $pdfService)
    {
        $salida->load([
            'horario.ruta.puntos.sucursal',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
            'encomiendas',
        ]);

        $puntos = $salida->horario->ruta->puntos
            ->sortBy('orden')
            ->values();

        $origenNombre = $puntos->first()?->pueblito?->descripcion ?? '-';
        $destinoNombre = $puntos->last()?->pueblito?->descripcion ?? '-';

        $encomiendas = $salida->encomiendas()
            ->wherePivot('estado', 'A')
            ->get();

        $tipoManifiesto = 'bodega';

        $html = view('salidas.manifiestos.bodega', compact(
            'salida',
            'encomiendas',
            'origenNombre',
            'destinoNombre',
            'tipoManifiesto'
        ))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_bodega_{$salida->id}.pdf",
            'P'
        );
    }

    public function manifiestoConductores(Salida $salida, PdfService $pdfService)
    {
        $salida->load([
            'horario.ruta.puntos.sucursal',
            'vehiculo',
            'conductorPrincipal',
            'conductorSecundario',
        ]);

        $puntos = $salida->horario->ruta->puntos->sortBy('orden')->values();
        $origenNombre = $puntos->first()?->pueblito?->descripcion ?? '-';
        $destinoNombre = $puntos->last()?->pueblito?->descripcion ?? '-';

        $html = view('salidas.manifiestos.conductores', compact(
            'salida',
            'origenNombre',
            'destinoNombre'
        ))->render();

        return $pdfService->generar(
            $html,
            "manifiesto_conductores_{$salida->id}.pdf",
            'P'
        );
    }


    public function show($id)
    {
        $salida = Salida::with([
            'horario.ruta.puntos.pueblito.sucursal',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
            'checks',
        ])->findOrFail($id);

        $puntos = $salida->horario?->ruta?->puntos?->sortBy('orden')->values() ?? collect();

        $bloqueados = $salida->puntosBloqueadosIds();

        $indiceActual = $puntos->search(
            fn($p) => !$bloqueados->contains($p->id)
        );

        $asientosVendidos = \DB::table('pasajes')
            ->where('salida_id', $salida->id)
            ->whereIn('estado', ['R', 'V'])
            ->distinct()
            ->count('asiento_numero');

        $isAdmin = auth()->user()->hasRole('Administrador');
        $sucursalId = auth()->user()->empleado->sucursal_id ?? null;

        $origenPunto = $puntos->first();

        // IMPORTANTE:
        // La sucursal sale del PUEBLITO.
        $origenSucursalId = $origenPunto?->pueblito?->sucursal_id;

        $esOrigen = $origenSucursalId
            && (int) $origenSucursalId === (int) $sucursalId;

        $origenYaConfirmado = $origenPunto
            ? $bloqueados->contains($origenPunto->id)
            : false;

        $puedeEditarAsignacion = !$isAdmin
            && $salida->estado === 'en_ruta'
            && $esOrigen
            && !$origenYaConfirmado;

        return response()->json([
            'id' => $salida->id,
            'horario_id' => $salida->horario_id,
            'vehiculo_id' => $salida->vehiculo_id,
            'conductor_principal_id' => $salida->conductor_principal_id,
            'conductor_secundario_id' => $salida->conductor_secundario_id,

            'fecha_salida' => $salida->fecha_salida?->format('Y-m-d'),
            'fecha_formateada' => $salida->fecha_formateada,
            'estado' => $salida->estado,

            'fecha_cambio_estado' => $salida->fecha_cambio_estado?->format('Y-m-d'),
            'hora_cambio_estado' => $salida->hora_cambio_estado?->format('H:i'),
            'motivo_cambio_estado' => $salida->motivo_cambio_estado,

            'hora_salida' => $salida->horario?->hora_formateada,
            'hora_llegada' => $salida->horario?->hora_llegada,
            'tipo_viaje' => $salida->horario?->tipo_viaje?->descripcion,
            'tipo_vehiculo' => $salida->horario?->tipo_vehiculo?->descripcion,

            'asientos_vendidos' => $asientosVendidos,
            'parada_actual_index' => $bloqueados->count(),

            'puede_editar_asignacion' => $puedeEditarAsignacion,

            'ruta' => [
                'nombre' => $salida->horario?->ruta?->nombre,

                'puntos' => $puntos->map(
                    function ($p, $i) use ($salida, $bloqueados, $indiceActual) {

                        $sucursal = $p->pueblito?->sucursal;

                        return [
                            'id' => $p->id,

                            'pueblito_id' => $p->pueblito_id,

                            // SIEMPRE desde el pueblito
                            'sucursal_id' => $p->pueblito?->sucursal_id,

                            'orden' => $p->orden,

                            'nombre' => $p->pueblito?->descripcion,

                            'sucursal' => $sucursal ? [
                                'id' => $sucursal->id,
                                'nombre_comercial' => $sucursal->nombre_comercial,
                            ] : null,

                            'hora' => $salida->horario->horaEnPunto($p->id),

                            'check_registrado' => $bloqueados->contains($p->id),

                            'es_actual' => $i === $indiceActual,
                        ];
                    }
                )->values(),
            ],
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha_salida' => 'required|date',
            'estado' => 'required|in:programado,reprogramado,en_ruta,finalizado,cancelado',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'conductor_principal_id' => 'nullable|exists:personas,id',
            'conductor_secundario_id' => 'nullable|exists:personas,id',
        ]);

        try {
            $existe = Salida::where('horario_id', $request->horario_id)
                ->where('fecha_salida', $request->fecha_salida)
                ->where('hora_salida', $request->hora_salida)

                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'Ya existe una salida programada para esta hora'
                ], 422);
            }

            if ($request->estado === 'en_ruta') {
                if (!$request->vehiculo_id || !$request->conductor_principal_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para poner la salida en ruta debes asignar vehículo y conductor principal.'
                    ], 422);
                }

                if ($request->conductor_principal_id == $request->conductor_secundario_id && $request->conductor_secundario_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes repetir el mismo conductor en ambos campos.'
                    ], 422);
                }
            }

            $salida = Salida::create([
                'horario_id' => $request->horario_id,
                'fecha_salida' => $request->fecha_salida,
                'hora_salida' => $request->hora_salida,
                'estado' => $request->estado,
            ]);

            return response()->json([
                'ok' => true,
                'id' => $salida->id,
                'mensaje' => 'Salida creada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generar(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'dias' => 'required|array|min:1',
        ]);

        try {
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $fechaFin = Carbon::parse($request->fecha_fin);
            $dias = collect($request->dias)->map(fn($d) => (int) $d)->toArray();

            $creadas = 0;

            while ($fechaInicio->lte($fechaFin)) {
                $dayOfWeek = $fechaInicio->dayOfWeekIso;

                if (in_array($dayOfWeek, $dias)) {
                    $existe = Salida::where('horario_id', $request->horario_id)
                        ->where('fecha_salida', $fechaInicio->format('Y-m-d'))
                        ->exists();

                    if (!$existe) {
                        Salida::create([
                            'horario_id' => $request->horario_id,
                            'fecha_salida' => $fechaInicio->format('Y-m-d'),
                            'estado' => 'programado',
                        ]);

                        $creadas++;
                    }
                }

                $fechaInicio->addDay();
            }

            return response()->json([
                'ok' => true,
                'mensaje' => "Se generaron {$creadas} salidas correctamente."
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeDirecta(Request $request)
    {
        $salida = DB::transaction(function () use ($request) {

            $horario = Horario::create([
                'ruta_id' => $request->ruta_id,
                'tipo_viaje_id' => 1,
                'tipo_vehiculo_id' => $request->tipo_vehiculo_id,
                'hora_salida' => $request->hora_salida,
            ]);

            return Salida::create([
                'horario_id' => $horario->id,
                'fecha_salida' => $request->fecha_salida,
            ]);
        });

        return response()->json([
            'success' => true,
            'id' => $salida->id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Administrador');
        $sucursalId = $user->empleado->sucursal_id ?? null;

        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'fecha_cambio_estado' => 'nullable|date|after_or_equal:today',
            'fecha_salida' => 'required|date',
            'estado' => 'required|in:programado,reprogramado,en_ruta,finalizado,cancelado',
            'vehiculo_id' => 'nullable|exists:vehiculos,id',
            'conductor_principal_id' => 'nullable|exists:empleados,id',
            'conductor_secundario_id' => 'nullable|exists:empleados,id',
            'hora_cambio_estado' => 'nullable|date_format:H:i',
            'motivo_cambio_estado' => 'nullable|string|max:500',
            'aplicar_a_pasajes' => 'nullable|boolean',
        ]);

        if (in_array($request->estado, ['reprogramado', 'cancelado'])) {
            if (!$request->fecha_cambio_estado || !$request->hora_cambio_estado || !$request->motivo_cambio_estado) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Debe registrar fecha, hora y motivo.'
                ], 422);
            }
        }

        try {
            $salida = Salida::with(['pasajes', 'horario.ruta.puntos'])->findOrFail($id);

            // 🔒 Autorización: un vendedor (no admin) solo puede hacer 3 cosas puntuales
            if (!$isAdmin) {
                $error = $this->validarAccionVendedor($request, $salida, $sucursalId);

                if ($error) {
                    return response()->json([
                        'ok' => false,
                        'message' => $error,
                    ], 403);
                }
            }

            $existe = Salida::where('horario_id', $request->horario_id)
                ->where('fecha_salida', $request->fecha_salida)
                ->where('hora_salida', $request->hora_salida)
                ->where('id', '!=', $id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'message' => 'Ya existe una salida programada para esta hora'
                ], 422);
            }

            if ($request->estado === 'en_ruta') {
                if (!$request->vehiculo_id || !$request->conductor_principal_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Para poner la salida en ruta debes asignar vehículo y conductor principal.'
                    ], 422);
                }

                if ($request->conductor_principal_id == $request->conductor_secundario_id && $request->conductor_secundario_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No puedes repetir el mismo conductor en ambos campos.'
                    ], 422);
                }
            }

            $salida->update([
                'horario_id' => $request->horario_id,
                'fecha_salida' => $request->fecha_salida,
                'estado' => $request->estado,
                'usuario_cambio_estado_id' => $user->id,
                'vehiculo_id' => $request->vehiculo_id,
                'hora_salida' => $request->hora_salida,
                'conductor_principal_id' => $request->conductor_principal_id,
                'conductor_secundario_id' => $request->conductor_secundario_id,
                'fecha_cambio_estado' => in_array($request->estado, ['reprogramado', 'cancelado']) ? $request->fecha_cambio_estado : null,
                'hora_cambio_estado' => in_array($request->estado, ['reprogramado', 'cancelado']) ? $request->hora_cambio_estado : null,
                'motivo_cambio_estado' => in_array($request->estado, ['reprogramado', 'cancelado']) ? $request->motivo_cambio_estado : null,
            ]);

            if (in_array($request->estado, ['reprogramado', 'cancelado'])) {
                foreach ($salida->pasajes as $pasaje) {
                    $pasaje->update([
                        'estado' => $request->estado,
                        'fecha_cambio_estado' => $request->fecha_cambio_estado,
                        'hora_cambio_estado' => $request->hora_cambio_estado,
                        'motivo_cambio_estado' => $request->motivo_cambio_estado,
                    ]);
                }
            }
            return response()->json([
                'ok' => true,
                'mensaje' => 'Salida actualizada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Valida que un usuario NO administrador solo pueda ejecutar una
     * de estas 3 acciones puntuales, sin tocar horario/fecha/motivo/etc.
     * Devuelve un mensaje de error (string) si está prohibido, o null si está permitido.
     */
    private function validarAccionVendedor(Request $request, Salida $salida, $sucursalId)
    {
        if (
            (string) $request->horario_id !== (string) $salida->horario_id
            || (string) $request->fecha_salida !== (string) $salida->fecha_salida?->format('Y-m-d')
        ) {
            return 'No tienes permiso para modificar el horario o la fecha de esta salida.';
        }

        if (in_array($request->estado, ['reprogramado', 'cancelado'])) {
            return 'No tienes permiso para reprogramar o cancelar salidas.';
        }

        if ($request->estado === 'en_ruta' && in_array($salida->estado, ['programado', 'reprogramado'])) {
            return $salida->esSucursalOrigen($sucursalId)
                ? null
                : 'Solo la sucursal de origen puede iniciar esta ruta.';
        }

        if ($request->estado === 'finalizado' && $salida->estado === 'en_ruta') {
            return $salida->esSucursalDestino($sucursalId)
                ? null
                : 'Solo la sucursal de destino puede finalizar esta ruta.';
        }

        if ($request->estado === 'en_ruta' && $salida->estado === 'en_ruta') {
            return $salida->puedeEditarAsignacion($sucursalId)
                ? null
                : 'No tienes permiso para editar la asignación de esta salida.';
        }

        return 'No tienes permiso para realizar este cambio de estado.';
    }

    public function destroy($id)
    {
        try {
            $salida = Salida::withCount('pasajes')->findOrFail($id);

            if ($salida->pasajes_count > 0) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se puede eliminar la salida porque ya tiene pasajes registrados.'
                ], 422);
            }

            $salida->delete();

            return response()->json([
                'ok' => true,
                'mensaje' => 'Salida eliminada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function registrarCheck(Request $request, Salida $salida)
    {
        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
        ]);

        $esAdmin = auth()->user()->hasRole('Administrador');

        // Un usuario de sucursal solo puede dar check en la suya propia.
        if (!$esAdmin && (int) $validated['sucursal_id'] !== (int) auth()->user()->sucursal_id) {
            return response()->json([
                'message' => 'No tienes permiso para registrar el check en esa sucursal.',
            ], 403);
        }

        $punto = $salida->horario->ruta->puntos()
            ->where('sucursal_id', $validated['sucursal_id'])
            ->first();

        if (!$punto) {
            return response()->json([
                'message' => 'Esa sucursal no forma parte de la ruta de esta salida.',
            ], 422);
        }

        $yaExiste = $salida->checks()->where('punto_id', $punto->id)->exists();

        if ($yaExiste) {
            return response()->json([
                'message' => 'Ya se registró el check para esta sucursal.',
            ], 422);
        }

        $salida->checks()->create([
            'punto_id' => $punto->id,
            'usuario_id' => auth()->id(),
            'registrado_en' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Check registrado. Ventas bloqueadas desde esta sucursal.',
        ]);
    }
}
