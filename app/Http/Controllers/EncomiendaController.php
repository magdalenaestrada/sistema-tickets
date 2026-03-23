<?php

namespace App\Http\Controllers;

use App\Models\AsignarHorario;
use App\Models\BilleteraDigital;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Encomienda;
use App\Models\MetodoPago;
use App\Models\Persona;
use App\Models\Provincia;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\TipoEncomienda;
use App\Services\EncomiendaService;
use App\Services\PagoService;
use Carbon\Carbon;
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
            ->get();;
        $asignaciones = AsignarHorario::with('horario')->get();
        $user = Auth::user();
        $tipos_documentos = TipoDocumentoPersona::all();

        return view('encomiendas.index', compact('sucursales', 'user', 'tipos_documentos', 'asignaciones'));
    }

    public function index_asignadas()
    {
        $sucursales = Sucursal::where('estado', 'A')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();;
        $asignaciones = AsignarHorario::with('horario')->get();
        $tipos_documentos = TipoDocumentoPersona::all();

        return view('encomiendas.asignadas', compact('sucursales', 'tipos_documentos', 'asignaciones'));
    }

    public function formulario()
    {
        Carbon::now();
        $user = Auth::user();
        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::where('estado', 'A')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();;
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $tipo_encomiendas = TipoEncomienda::all();
        $billeteras_digitales = BilleteraDigital::all();
        return view('encomiendas.create', compact('sucursales', 'tipos_documentos', 'user', 'tipo_encomiendas', 'tipos_documentos_facturas', 'metodos_pago', 'billeteras_digitales'));
    }
    public function datatable_no_asignadas()
    {
        $data = Encomienda::with([
            'emisor',
            'receptor',
            'sucursal_origen',
            'sucursal_destino'
        ])
            ->where('estado', 'A')
            ->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addColumn('checkbox', function ($e) {
                return '<input type="checkbox" class="check-encomienda" value="' . $e->id . '">';
            })
            ->addColumn('emisor', fn($e) => ($e->emisor?->nombres ?? '') . ' ' . ($e->emisor?->apellidos ?? ''))
            ->addColumn('dni_emisor', fn($e) => $e->emisor?->documento ?? '-')
            ->addColumn('receptor', fn($e) => ($e->receptor?->nombres ?? '') . ' ' . ($e->receptor?->apellidos ?? ''))
            ->addColumn('origen', fn($e) => $e->sucursal_origen?->nombre_comercial ?? '-')
            ->addColumn('destino', fn($e) => $e->sucursal_destino?->nombre_comercial ?? '-')
            ->addColumn('total', fn($e) => 'S/ ' . number_format($e->total ?? 0, 2))
            ->addColumn('estado', function ($e) {
                $estados = [
                    'E' => '<span class="badge bg-success">Entregado</span>',
                    'P' => '<span class="badge bg-info">Pendiente</span>',
                    'A' => '<span class="badge bg-danger">Sin asignar</span>',
                ];
                return $estados[$e->estado] ?? '<span class="badge bg-secondary">Desconocido</span>';
            })
            ->addColumn('acciones', function ($e) {
                return '
                <button class="btn btn-xs btn-info imprimir" data-id="' . $e->id . '" title="Imprimir">
                    <i class="link-icon" data-lucide="printer"></i>
                </button>
                <button class="btn btn-xs btn-warning editar" data-id="' . $e->id . '" title="Editar">
                    <i class="link-icon" data-lucide="pencil"></i>
                </button>
                <button class="btn btn-xs btn-danger anular" data-id="' . $e->id . '" title="Anular">
                    <i class="link-icon" data-lucide="trash-2"></i>
                </button>
            ';
            })
            ->rawColumns(['checkbox', 'estado', 'acciones'])
            ->make(true);
    }

    public function datatable_asignadas()
    {
        $data = Encomienda::with([
            'emisor',
            'receptor',
            'sucursal_origen',
            'sucursal_destino'
        ])
            ->whereIn('estado', ['P', 'E'])
            ->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addColumn('dni_receptor', fn($e) => $e->receptor?->documento ?? '-')
            ->addColumn('receptor', fn($e) => ($e->receptor?->nombres ?? '') . ' ' . ($e->receptor?->apellidos ?? ''))
            ->addColumn('emisor', fn($e) => ($e->emisor?->nombres ?? '') . ' ' . ($e->emisor?->apellidos ?? ''))
            ->addColumn('origen', fn($e) => $e->sucursal_origen?->nombre_comercial ?? '-')
            ->addColumn('destino', fn($e) => $e->sucursal_destino?->nombre_comercial ?? '-')
            ->addColumn('total', fn($e) => 'S/ ' . number_format($e->total ?? 0, 2))
            ->filterColumn('dni_receptor', function ($query, $keyword) {
                $query->whereHas('receptor', function ($q) use ($keyword) {
                    $q->where('documento', 'like', "%$keyword%");
                });
            })
            ->filterColumn('receptor', function ($query, $keyword) {
                $query->whereHas('receptor', function ($q) use ($keyword) {
                    $q->where('nombres', 'like', "%$keyword%")
                        ->orWhere('apellidos', 'like', "%$keyword%");
                });
            })
            ->addColumn('estado', function ($e) {
                $estados = [
                    'E' => '<span class="badge bg-success">Entregado</span>',
                    'P' => '<span class="badge bg-info">Pendiente</span>',
                    'A' => '<span class="badge bg-danger">Sin asignar</span>',
                ];
                return $estados[$e->estado] ?? '<span class="badge bg-secondary">Desconocido</span>';
            })
            ->addColumn('acciones', function ($e) {

                $botones = '
        <button class="btn btn-xs btn-info imprimir" data-id="' . $e->id . '">
            <i data-lucide="printer"></i>
        </button>
    ';

                if ($e->estado !== 'E') {
                    $botones .= '
           <button type="button" class="btn btn-xs btn-success entregar" data-id="' . $e->id . '">
                <i data-lucide="check"></i>
            </button>
        ';
                }
                return $botones;
            })
            ->rawColumns(['estado', 'acciones'])
            ->make(true);
    }

    public function entregar($id)
    {
        $encomienda = Encomienda::findOrFail($id);

        $encomienda->update([
            "estado" => "E"
        ]);

        return response()->json([
            "success" => true,
            "message" => "Encomienda entregada",
            "data" => $encomienda
        ]);
    }

    public function editar($id)
    {
        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::where('estado', 'A')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->get();;
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
            'sucursal_origen',
            'sucursal_destino'
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

            $receptorDocumento = $request->input('receptor.documento');
            $receptorTipo = $request->input('receptor.tipo_documento_id');

            if ($receptorDocumento) {
                $receptor = Persona::updateOrCreate(
                    ['documento' => $request->input('receptor.documento')],
                    [
                        'tipo_documento_id' => $request->input('receptor.tipo_documento_id'),
                        'distrito_id' => $request->input('receptor.distrito_id'),
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
                $receptor = Persona::create([
                    'tipo_documento_id' => $receptorTipo,
                    'documento' => null,
                    'distrito_id' => $request->input('receptor.distrito_id'),
                    'nombres' => $request->input('receptor.nombres'),
                    'apellidos' => $request->input('receptor.apellidos'),
                    'telefono' => $request->input('receptor.telefono'),
                    'celular' => $request->input('receptor.celular'),
                    'correo' => $request->input('receptor.correo'),
                    'direccion' => $request->input('receptor.direccion'),
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]);
            }

            $user_id = Auth::id();

            $encomienda = $encomiendaService->crearEncomienda($request, $emisor->id, $receptor->id, $user_id);

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


    public function ticket($id)
    {
        $encomienda = Encomienda::with(['emisor', 'receptor', 'detalles', 'sucursal_origen', 'sucursal_destino'])->findOrFail($id);

        return view('encomiendas.ticket', compact('encomienda'));
    }
}
