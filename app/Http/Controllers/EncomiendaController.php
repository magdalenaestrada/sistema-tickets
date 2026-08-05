<?php

namespace App\Http\Controllers;

use App\Models\AsignarHorario;
use App\Models\BilleteraDigital;
use App\Models\Caja;
use App\Models\CajaDetalle;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Empresa;
use App\Models\Encomienda;
use App\Models\EncomiendaDetalle;
use App\Models\EncomiendaSalida;
use App\Models\MetodoPago;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\Provincia;
use App\Models\Pueblito;
use App\Models\Salida;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\TipoEncomienda;
use App\Services\EncomiendaService;
use App\Services\PagoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class EncomiendaController extends Controller
{
    public function index_no_asignadas()
    {
        $sucursales = Sucursal::where('estado', 'A')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();

        $pueblitos = Pueblito::all();

        $asignaciones = AsignarHorario::with('horario')->get();

        $user = Auth::user();

        $tipos_documentos = TipoDocumentoPersona::all();

        $salidas = Salida::with([
            'horario.ruta.puntos.sucursal'
        ])
            ->whereIn('estado', ['en_ruta'])
            ->whereDate('fecha_salida', '>=', Carbon::today()->subDay())
            ->orderBy('fecha_salida')
            ->get() // 👈 Traemos los datos de la BD primero
            ->filter(fn($s) => $s->horario?->ruta) // 👈 Ahora sí filter() funciona sobre la colección
            ->values();


        return view('encomiendas.index', compact(
            'sucursales',
            'user',
            'pueblitos',
            'tipos_documentos',
            'asignaciones',
            'salidas'
        ));
    }

    public function index_asignadas()
    {
        $sucursales = Sucursal::where('estado', 'A')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();
        $asignaciones = AsignarHorario::with('horario')->get();
        $tipos_documentos = TipoDocumentoPersona::all();

        return view('encomiendas.asignadas', compact('sucursales', 'tipos_documentos', 'asignaciones'));
    }


    public function formulario()
    {
        Carbon::now();
        $user = Auth::user();

        if (!$user->hasRole('Administrador')) {
            $cajaAbierta = Caja::where('usuario_id', $user->id)
                ->where('estado', 'A')
                ->exists();

            if (!$cajaAbierta) {
                return redirect()
                    ->route('caja.index')
                    ->with('warning', 'Debe abrir una caja antes de crear una encomienda.');
            }
        }
        $pueblitoOrigenSeleccionado = Pueblito::where('sucursal_id', $user->sucursal_id)
            ->value('id');

        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::with('distrito')
            ->where('estado', 'A')
            ->select('id', 'nombre_comercial', 'distrito_id')
            ->orderBy('nombre_comercial')
            ->get();

        $cajas_emision = Caja::with('sucursal')
            ->where('usuario_id', $user->id)
            ->where('estado', 'A')
            ->get();

        $pueblitos = Pueblito::orderBy("descripcion", "asc")->get();
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $tipo_encomiendas = TipoEncomienda::all();
        $billeteras_digitales = BilleteraDigital::all();

        $seriesSucursal = $cajas_emision
            ->pluck('sucursal.serie')
            ->flatten();

        return view('encomiendas.create', array_merge(
            compact(
                'sucursales',
                'tipos_documentos',
                'pueblitos',
                'user',
                'tipo_encomiendas',
                'tipos_documentos_facturas',
                'metodos_pago',
                'billeteras_digitales',
                'cajas_emision',
                'seriesSucursal',
                'pueblitoOrigenSeleccionado'
            ),
            [
                'esSobreequipaje' => false,
                'pasaje' => null,
            ]
        ));
    }

    public function formularioSobrequipaje(Pasaje $pasaje)
    {
        Carbon::now();
        $user = Auth::user();
        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::with('distrito')
            ->where('estado', 'A')
            ->select('id', 'nombre_comercial', 'distrito_id')
            ->orderBy('nombre_comercial')
            ->get();
        $cajas_emision = Caja::with('sucursal')
            ->where('usuario_id', $user->id)
            ->where('estado', 'A')
            ->get();
        $pueblitos = Pueblito::orderBy("descripcion", "asc")->get();
        $seriesSucursal = $cajas_emision
            ->pluck('sucursal.serie')
            ->flatten();

        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $tipo_encomiendas = TipoEncomienda::all();
        $billeteras_digitales = BilleteraDigital::all();
        return view(
            'sobrequipaje.create',
            array_merge(
                compact(
                    'sucursales',
                    'tipos_documentos',
                    'pueblitos',
                    'user',
                    'tipo_encomiendas',
                    'tipos_documentos_facturas',
                    'metodos_pago',
                    'billeteras_digitales',
                    'cajas_emision',
                    'seriesSucursal'
                ),
                [
                    'esSobreequipaje' => true,
                    'pasaje' => $pasaje,
                ]
            )
        );
    }


    public function datatable_no_asignadas(Request $request)
    {
        $data = Encomienda::with([
            'emisor:id,documento,nombres,apellidos',
            'receptor:id,documento,nombres,apellidos',
            'origenPueblito:id,descripcion',
            'destinoPueblito:id,descripcion',
        ])
            ->where('estado', 'SA')
            ->where('sobre_equipaje', false)
            ->when($request->filled('documento'), function ($q) use ($request) {
                $doc = trim($request->documento);

                $q->where(function ($sub) use ($doc) {
                    $sub->whereHas('emisor', function ($p) use ($doc) {
                        $p->where('documento', 'like', "%{$doc}%");
                    })->orWhereHas('receptor', function ($p) use ($doc) {
                        $p->where('documento', 'like', "%{$doc}%");
                    });
                });
            })
            ->when($request->filled('fecha'), function ($q) use ($request) {
                $q->whereDate('fecha_creacion', $request->fecha);
            })
            ->when($request->filled('origen_id'), function ($q) use ($request) {
                $q->where('origen_pueblito_id', $request->origen_id);
            })
            ->when($request->filled('destino_id'), function ($q) use ($request) {
                $q->where('destino_pueblito_id', $request->destino_id);
            })
            ->orderByDesc('id');

        return DataTables::of($data)
            ->addColumn('checkbox', function ($e) {
                return '<input type="checkbox" class="check-encomienda" value="' . $e->id . '">';
            })
            ->addColumn('fecha', function ($e) {
                return optional($e->fecha_creacion)?->format('d/m/Y H:i');
            })
            ->addColumn('emisor', fn($e) => trim(($e->emisor?->nombres ?? '') . ' ' . ($e->emisor?->apellidos ?? '')))
            ->addColumn('dni_emisor', fn($e) => $e->emisor?->documento ?? '-')
            ->addColumn('receptor', fn($e) => trim(($e->receptor?->nombres ?? '') . ' ' . ($e->receptor?->apellidos ?? '')))
            ->addColumn('dni_receptor', fn($e) => $e->receptor?->documento ?? '-')
            ->addColumn('origen', fn($e) => $e->origenPueblito?->descripcion ?? '-')
            ->addColumn('destino', fn($e) => $e->destinoPueblito?->descripcion ?? '-')
            ->addColumn('total', fn($e) => 'S/ ' . number_format($e->total ?? 0, 2))
            ->addColumn('estado', fn() => '<span class="badge bg-danger">Sin asignar</span>')
            ->addColumn('acciones', function ($e) {
                return '
                <button class="btn btn-xs btn-info imprimir" data-id="' . $e->id . '" title="Imprimir">
                    <i class="link-icon" data-lucide="printer"></i>
                </button>
                
                
            ';
            })
            ->rawColumns(['checkbox', 'estado', 'acciones'])
            ->make(true);
    }

    public function datatable_asignadas(Request $request)
    {
        $query = Encomienda::query()
            ->with([
                'receptor',
                'receptor2',
                'emisor',
                'origenPueblito',
                'destinoPueblito',
                'salidaActual'
            ])->when($request->documento, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {

                    $query->whereHas('receptor', function ($sub) use ($request) {
                        $sub->where('documento', 'like', $request->documento . '%');
                    })
                        ->orWhereHas('receptor2', function ($sub) use ($request) {
                            $sub->where('documento', 'like', $request->documento . '%');
                        });
                });
            })
            ->when(
                $request->origen_id,
                fn($q) => $q->where('origen_pueblito_id', $request->origen_id)
            )
            ->whereNotIn('estado', ['SA'])
            ->where("sobre_equipaje", false)
            ->when($request->destino_id, fn($q) => $q->where('destino_id', $request->destino_id))
            ->when($request->salida_id, fn($q) => $q->where('salida_id', $request->salida_id))
            ->orderBy("fecha_creacion", "desc");

        return datatables()->of($query)
            ->addColumn('checkbox', function ($row) {

                if ($row->estado !== 'EC') {
                    return '';
                }

                return '<input type="checkbox" class="check-llegada" value="' . $row->id . '">';
            })
            ->addColumn('fecha', function ($row) {
                return optional($row->created_at)->format('d/m/Y H:i');
            })
            ->addColumn('receptor', function ($row) {

                if (!$row->receptor) {
                    return '-';
                }

                return '
        <div style="line-height:1.2">
            <div class="fw-semibold">'
                    . e($row->receptor->nombre_completo) .
                    '</div>
            <small class="text-muted">'
                    . e($row->receptor->documento) .
                    '</small>
        </div>';
            })
            ->addColumn('receptor2', function ($row) {

                if (!$row->receptor2) {
                    return '<span class="text-muted">-</span>';
                }

                return '
        <div style="line-height:1.2">
            <div class="fw-semibold">'
                    . e($row->receptor2->nombre_completo) .
                    '</div>
            <small class="text-muted">'
                    . e($row->receptor2->documento) .
                    '</small>
        </div>';
            })
            ->addColumn('origen', function ($row) {
                return $row->origenPueblito->descripcion ?? '-';
            })
            ->addColumn('destino', function ($row) {
                return $row->destinoPueblito->descripcion ?? '-';
            })
            ->addColumn('salida', function ($row) {
                $salida = $row->salidaActual?->salida;

                if (!$salida) {
                    return '<span class="text-muted">Sin salida</span>';
                }

                $ruta = $salida->horario?->ruta?->nombre ?? 'Sin ruta';
                $fecha = $salida->fecha_salida
                    ? Carbon::parse($salida->fecha_salida)->format('d/m/Y')
                    : '-';
                $hora = $salida->horario?->hora_salida
                    ? Carbon::parse($salida->horario->hora_salida)->format('h:i A')
                    : '-';

                return '
        <div>
            <div class="fw-semibold text-primary">' . e($ruta) . '</div>
            <small class="text-muted">
                <i data-lucide="calendar" class="icon-sm"></i> ' . $fecha . '
                &nbsp;|&nbsp;
                <i data-lucide="clock" class="icon-sm"></i> ' . $hora . '
            </small>
        </div>
    ';
            })
            // ->editColumn('estado', function ($row) {
            //     if ($row->estado === 'E') {
            //         return '<span class="badge bg-success">ENTREGADO</span>';
            //     }

            //     if ($row->estado === 'P') {
            //         return '<span class="badge bg-warning text-dark">EN CAMINO</span>';
            //     }

            //     if ($row->estado === 'A') {
            //         return '<span class="badge bg-info text-dark">SIN ASIGNAR</span>';
            //     }

            //     return '<span class="badge bg-secondary">' . e($row->estado) . '</span>';
            // })
            ->editColumn('estado', function ($row) {

                return match ($row->estado) {

                    'ET' => '
            <span class="badge rounded-pill bg-success-subtle text-dark border border-success-subtle px-3 py-2">
                <i data-lucide="check-circle" class="me-1"></i> Entregado
            </span>
        ',

                    'PE' => '
            <span class="badge rounded-pill bg-warning-subtle text-dark border border-warning-subtle px-3 py-2">
                <i data-lucide="package-check" class="me-1"></i> Por entregar
            </span>
        ',

                    'EC' => '
            <span class="badge rounded-pill bg-info-subtle text-dark border border-info-subtle px-3 py-2">
                <i data-lucide="truck" class="me-1"></i> En camino
            </span>
        ',

                    'X' => '
            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                <i data-lucide="x-circle" class="me-1"></i> Anulado
            </span>
        ',

                    'SA' => '
            <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">
                <i data-lucide="clock-3" class="me-1"></i> Sin asignar
            </span>
        ',

                    default => '
            <span class="badge rounded-pill bg-light text-dark px-3 py-2">
                ' . e($row->estado) . '
            </span>
        ',
                };
            })
            ->addColumn('acciones', function ($row) {
                $botones = '<button type="button" class="btn btn-xs btn-secondary imprimir" data-id="' . $row->id . '"><i class="link-icon" data-lucide="printer"></i></button> </button> ';

                if ($row->estado === 'PE') {
                    $botones .= '<button type="button" class="btn btn-xs btn-success entregar" data-id="' . $row->id . '"><i class="link-icon" data-lucide="check"></i></button> </button>';
                }

                // if ($row->estado === 'EC') {
                //     $botones .= '<button type="button" class="btn btn-xs btn-success enagencia" data-id="' . $row->id . '"><i class="link-icon" data-lucide="check"></i></button> </button>';
                // }

                return $botones;
            })
            ->rawColumns([
                'checkbox',
                'receptor',
                'receptor2',
                'estado',
                'acciones',
                'salida'
            ])->make(true);
    }

    public function entregarMasivo(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:encomienda,id',
        ]);

        DB::beginTransaction();

        try {
            $encomiendas = Encomienda::whereIn('id', $request->ids)
                ->where('estado', 'EC')
                ->get();

            foreach ($encomiendas as $encomienda) {
                $encomienda->update([
                    'estado' => 'PE',
                    'fecha_procesado' => now(),
                ]);

                EncomiendaSalida::where('encomienda_id', $encomienda->id)
                    ->where('estado', 'SA')
                    ->update([
                        'estado' => 'L',
                        'fecha_llegada' => now(),
                        'updated_at' => now(),
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Llegada confirmada correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function salidasDisponiblesParaAsignacion(Request $request)
    {
        $request->validate([
            'origen_id' => 'required|exists:pueblitos,id',
            'destino_id' => 'required|exists:pueblitos,id',
        ]);

        $salidas = Salida::with([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_viaje',
            'vehiculo',
        ])
            ->whereIn('estado', ['activo', 'programado'])
            ->whereDate('fecha_salida', '>=', now()->toDateString())
            ->orderBy('fecha_salida')
            ->get()
            ->filter(function ($salida) use ($request) {
                return $salida->puedeTransportarEncomienda(
                    $request->origen_id,
                    $request->destino_id
                );
            })
            ->values()
            ->map(function ($salida) {
                $ruta = $salida->horario?->ruta;
                $puntos = $ruta?->puntos?->sortBy('orden')->values();

                return [
                    'id' => $salida->id,
                    'text' => ($salida->fecha_salida?->format('d/m/Y') ?? '-') .
                        ' - ' .
                        ($ruta?->nombre ?? 'Ruta') .
                        ' - ' .
                        ($puntos->first()?->sucursal?->nombre_comercial ?? 'Origen') .
                        ' → ' .
                        ($puntos->last()?->sucursal?->nombre_comercial ?? 'Destino') .
                        ' - ' .
                        ($salida->horario?->hora_formateada ?? ''),
                ];
            });

        return response()->json($salidas);
    }

    public function asignarASalida(Request $request)
    {
        $request->validate([
            'encomienda_ids' => 'required|array|min:1',
            'encomienda_ids.*' => 'required|exists:encomienda,id',
            'salida_id' => 'required|exists:salidas,id',
        ]);

        DB::beginTransaction();

        try {
            $salida = Salida::with(['horario.ruta.puntos.sucursal', 'horario.tipo_viaje'])->findOrFail($request->salida_id);

            $encomiendas = Encomienda::whereIn('id', $request->encomienda_ids)
                ->where('estado', 'SA')
                ->get();

            foreach ($encomiendas as $encomienda) {

                DB::table('encomienda_salida')->insert([
                    'encomienda_id' => $encomienda->id,
                    'salida_id' => $salida->id,
                    'usuario_id' => Auth::id(),
                    'fecha_asignacion' => now(),
                    'estado' => 'A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $encomienda->update([
                    'estado' => 'EC', // EN CAMINO
                    'fecha_procesado' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Encomiendas asignadas correctamente.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function entregar($id)
    {
        $user_id = Auth::id();
        $encomienda = Encomienda::with([
            'emisor.tipoDocumento',
            'receptor.tipoDocumento',
            'detalles.tipo_encomienda',
            'sucursal_origen',
            'sucursal_destino',
            'origenPueblito',
            'destinoPueblito',
            'usuario',
            'venta',
        ])->findOrFail($id);

        $encomienda->update([
            'estado' => 'ET',
            'entregado_por' => $user_id,
            'fecha_entrega' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Encomienda entregada.',
            'ticket_url' => route('encomiendas.ticket-entrega', $encomienda->id),
            'data' => $encomienda,
        ]);
    }

    public function ticketEntrega(Encomienda $encomienda)
    {
        $encomienda->load([
            'emisor.tipoDocumento',
            'receptor.tipoDocumento',
            'usuario.persona',
            'sucursal_origen',
            'sucursal_destino',
            'origenPueblito',
            'destinoPueblito',
            'detalles.tipo_encomienda',
            'venta',
        ]);

        $empresa = Empresa::first();
        return Pdf::loadView(
            'encomiendas.ticket_entrega',
            compact('encomienda', 'empresa')
        )->stream("ENTREGA-{$encomienda->id}.pdf");
    }
    public function enAgencia($id)
    {
        $encomienda = Encomienda::findOrFail($id);

        $encomienda->update([
            "estado" => "PE"
        ]);

        return response()->json([
            "success" => true,
            "message" => "Encomienda en agencia",
            "data" => $encomienda
        ]);
    }

    public function editar($id)
    {
        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::where('estado', 'A')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();
        $user = Auth::user();
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $tipo_encomiendas = TipoEncomienda::all();
        $billeteras_digitales = BilleteraDigital::all();
        $encomienda = Encomienda::with([
            'emisor.distrito.provincia.departamento',
            'receptor.distrito.provincia.departamento',
            'detalles.tipo_encomienda',
            'venta.pagos',
            'venta.pagos.billetera',
            'venta.pagos.metodoPago',
            'origenPueblito',
            'destinoPueblito'
        ])->findOrFail($id);

        return view('encomiendas.edit', compact('metodos_pago', 'encomienda', 'sucursales', 'user', 'tipos_documentos', 'tipo_encomiendas', 'tipos_documentos_facturas', 'billeteras_digitales'));
    }

    public function actualizar(
        Request $request,
        Encomienda $encomienda,
        EncomiendaService $encomiendaService
    ) {
        $request->validate([
            'emisor.documento' => 'required|string|max:20',
            'emisor.nombres' => 'required|string|max:200',
            'receptor.documento' => 'nullable|string|max:20',
            'receptor.nombres' => 'required|string|max:200',
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
        ]);



        try {
            $emisor = Persona::updateOrCreate(
                [
                    'tipo_documento_id' => $request->input('emisor.tipo_documento_id'),
                    'documento' => $request->input('emisor.documento'),
                ],
                [
                    'tipo_documento_id' => $request->input('emisor.tipo_documento_id'),
                    'distrito_id' => $request->input('emisor.distrito_id'),
                    'nombres' => $request->input('emisor.nombres'),
                    'apellidos' => $request->input('emisor.apellidos'),
                    'telefono' => $request->input('emisor.telefono'),
                    'celular' => $request->input('emisor.celular'),
                    'correo' => $request->input('emisor.correo'),
                    'direccion' => $request->input('emisor.direccion'),
                    'estado' => 'A',
                    'fecha_creacion' => now(),

                ]
            );

            $receptorDocumento = $request->input('receptor.documento');
            $receptorTipo = $request->input('receptor.tipo_documento_id');

            if ($receptorDocumento) {
                $receptor = Persona::updateOrCreate(
                    [
                        'documento' => $request->input('receptor.documento'),
                        'tipo_documento_id' => $request->input('receptor.tipo_documento_id'),
                    ],
                    [
                        'distrito_id' => $request->input('receptor.distrito_id', 1),
                        'nombres' => $request->input('receptor.nombres'),
                        'apellidos' => $request->input('receptor.apellidos'),
                        'telefono' => $request->input('receptor.telefono'),
                        'celular' => $request->input('receptor.celular'),
                        'correo' => $request->input('receptor.correo'),
                        'direccion' => $request->input('receptor.direccion'),
                        'estado' => 'A',
                        'fecha_creacion' => now(),
                    ]
                );
            } else {
                $receptor = $encomienda->receptor;
                $receptor->update([
                    'nombres' => $request->input('receptor.nombres'),
                    'apellidos' => $request->input('receptor.apellidos'),
                    'telefono' => $request->input('receptor.telefono'),
                    'celular' => $request->input('receptor.celular'),
                    'direccion' => $request->input('receptor.direccion'),
                ]);
            }
            $encomiendaService->actualizarEncomienda(
                $request,
                $encomienda,
                $emisor->id,
                $receptor->id
            );

            return response()->json([
                'success' => true,
                'redirect' => route('encomiendas.index-no-asignadas'),
                'ticket_id' => $encomienda->id
            ]);
        } catch (\Throwable $th) {
            \Log::error($th);
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 500);
        }
    }

    public function guardar(Request $request, EncomiendaService $encomiendaService)
    {

        $request->validate([
            'emisor.documento' => 'required|string|max:20',
            'emisor.nombres' => 'required|string|max:200',
            'receptor.documento' => 'nullable|string|max:20',
            'receptor.nombres' => 'nullable|string|max:200',
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
        ]);

        try {
            $emisor = Persona::updateOrCreate(
                [
                    'tipo_documento_id' => $request->input('emisor.tipo_documento_id'),
                    'documento' => $request->input('emisor.documento'),
                ],
                [
                    'distrito_id' => $request->input('emisor.distrito_id', 1),
                    'nombres' => $request->input('emisor.nombres'),
                    'apellidos' => $request->input('emisor.apellidos'),
                    'telefono' => $request->input('emisor.telefono'),
                    'celular' => $request->input('emisor.celular'),
                    'correo' => $request->input('emisor.correo'),
                    'direccion' => $request->input('emisor.direccion'),
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $receptor = null;

            if ($request->has('receptor.documento')) {

                $receptorDocumento = $request->input('receptor.documento');

                if ($receptorDocumento) {
                    $receptor = Persona::updateOrCreate(
                        [
                            'documento' => $request->input('receptor.documento'),
                            'tipo_documento_id' => $request->input('receptor.tipo_documento_id'),
                        ],
                        [
                            'distrito_id' => $request->input('receptor.distrito_id', 1),
                            'nombres' => $request->input('receptor.nombres'),
                            'apellidos' => $request->input('receptor.apellidos'),
                            'telefono' => $request->input('receptor.telefono'),
                            'celular' => $request->input('receptor.celular'),
                            'correo' => $request->input('receptor.correo'),
                            'direccion' => $request->input('receptor.direccion'),
                            'estado' => 'A',
                            'fecha_creacion' => now(),

                        ]
                    );
                }
            }
            $receptor2 = null;

            if ($request->filled('receptor2.nombres')) {

                $receptor2 = Persona::updateOrCreate([
                    'tipo_documento_id' => $request->input('receptor2.tipo_documento_id'),
                    'documento' => $request->input('receptor2.documento'),
                ], [

                    'nombres' => $request->input('receptor2.nombres'),
                    'apellidos' => $request->input('receptor2.apellidos'),
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]);
            }

            $user_id = Auth::id();

            Cliente::updateOrCreate(
                ['persona_id' => $emisor->id],
                ['user_id' => $user_id]
            );

            if ($receptor) {
                Cliente::updateOrCreate(
                    ['persona_id' => $receptor->id],
                    ['user_id' => $user_id]
                );
            }

            $receptorId = $receptor ? $receptor->id : null;
            $receptor2Id = $receptor2 ? $receptor2->id : null;

            $encomienda = $encomiendaService->crearEncomienda(
                $request,
                $receptor2Id,
                $emisor->id,
                $receptorId,
                $user_id
            );

            return response()->json([
                'success' => true,
                'redirect' => route('encomiendas.index-no-asignadas'),
                'ticket_id' => $encomienda->id
            ]);
        } catch (\Throwable $th) {
            \Log::error('Error al guardar encomienda: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function mostrar($id)
    {
        $encomienda = Encomienda::with(['emisor', 'receptor', 'detalles'])->findOrFail($id);
        return response()->json($encomienda);
    }

    public function anular($id)
    {
        $e = Encomienda::findOrFail($id);
        $e->estado = 'X';
        $e->save();

        return response()->json(['success' => true]);
    }


    public function salidasDisponibles(Request $request)
    {
        $request->validate([
            'fecha_salida' => 'required|date',
            'origen_id' => 'nullable|exists:pueblitos,id',
            'destino_id' => 'nullable|exists:pueblitos,id',
        ]);

        $salidas = Salida::with([
            'horario.ruta.puntos.sucursal',
            'horario.tipo_viaje',
            'horario.tipo_vehiculo',
        ])
            ->whereIn('estado', ['en_ruta', 'programado'])
            ->whereDate('fecha_salida', $request->fecha_salida)
            ->orderBy('fecha_salida')
            ->get();

        if ($request->origen_id && $request->destino_id) {
            $salidas = $salidas->filter(function ($salida) use ($request) {
                return $salida->puedeTransportarEncomienda(
                    $request->origen_id,
                    $request->destino_id
                );
            });
        }

        return response()->json(
            $salidas->map(function ($salida) {
                $ruta = $salida->horario?->ruta;
                $puntos = $ruta?->puntos?->sortBy('orden')->values();

                $origenNombre = $puntos?->first()?->pueblito?->descripcion ?? 'Origen';
                $destinoNombre = $puntos?->last()?->pueblito?->descripcion ?? 'Destino';
                $hora = $salida->horario?->hora_formateada ?? '-';

                $estado = match ($salida->estado) {
                    'en_ruta'      => 'EN RUTA',
                    'programado'   => 'PROGRAMADO',
                    'finalizado'   => 'FINALIZADO',
                    'cancelado'    => 'CANCELADO',
                    'reprogramado' => 'REPROGRAMADO',
                    default        => $salida->estado,
                };


                return [
                    'value' => $salida->id,
                    'text' => strtoupper($ruta?->nombre) . ' ( ' . $hora . ' ) - ' . strtoupper($estado),
                    'puntos' => $puntos
                        ?->map(fn($p) => $p->pueblito?->descripcion ?? 'Punto')
                        ->values()
                        ->all() ?? [],
                ];
            })->values()
        );
    }

    public function asignarSalida(Request $request)
    {
        $request->validate([
            'salida_id' => 'required|exists:salidas,id',
            'encomienda_ids' => 'required|array|min:1',
            'encomienda_ids.*' => 'required|exists:encomienda,id',
        ]);

        DB::beginTransaction();

        try {
            $salida = Salida::with([
                'horario.ruta.puntos.sucursal',
                'horario.tipo_viaje',
            ])->findOrFail($request->salida_id);

            $encomiendas = Encomienda::whereIn('id', $request->encomienda_ids)
                ->where('estado', 'SA')
                ->get();

            if ($encomiendas->isEmpty()) {
                throw new \Exception('No hay encomiendas válidas para asignar.');
            }

            foreach ($encomiendas as $encomienda) {
                $asignacionActiva = EncomiendaSalida::where('encomienda_id', $encomienda->id)
                    ->where('estado', 'SA')
                    ->exists();

                if ($asignacionActiva) {
                    throw new \Exception("La encomienda {$encomienda->id} ya tiene una salida asignada.");
                }

                EncomiendaSalida::create([
                    'encomienda_id' => $encomienda->id,
                    'salida_id' => $salida->id,
                    'usuario_id' => Auth::id(),
                    'fecha_asignacion' => now(),
                    'estado' => 'A',
                ]);

                $encomienda->update([
                    'estado' => 'EC',
                    'fecha_procesado' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Encomiendas asignadas correctamente.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function ticket(Encomienda $encomienda)
    {
        $encomienda->load([
            'emisor',
            'receptor',
            'usuario.persona',
            'detalles.tipo_encomienda',
            'origenPueblito',
            'destinoPueblito',
            'sucursal_origen.empresa',
            'sucursal_destino',
            'venta.tipoDocumentoFactura',
            'venta.sucursal.empresa',
            'venta.pagos.metodoPago',
        ]);
        $venta = $encomienda->venta;
        return view('encomiendas.ticket', compact('encomienda', 'venta'));
    }
}
