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
        $tipos_documentos = TipoDocumentoPersona::all();

        return view('encomiendas.index', compact('sucursales', 'tipos_documentos', 'asignaciones'));
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
        $departamentos = Departamento::select('id', 'nombre')->get();
        $provincias = Provincia::select('id', 'nombre')->get();
        $distritos = Distrito::select('id', 'nombre')->get();
        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::where('estado', 'A')
    ->select('id', 'nombre_comercial')
    ->orderBy('nombre_comercial')
    ->get();;
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $tipo_encomiendas = TipoEncomienda::all();
        $billeteras_digitales = BilleteraDigital::all();
        return view('encomiendas.create', compact('sucursales', 'tipos_documentos', 'user', 'tipo_encomiendas', 'tipos_documentos_facturas', 'metodos_pago', 'billeteras_digitales', 'departamentos', 'provincias', 'distritos'));
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
                    'E' => '<span class="badge bg-warning">Espera</span>',
                    'P' => '<span class="badge bg-info">Pendiente</span>',
                    'A' => '<span class="badge bg-danger">Sin asignar</span>',
                    'EN' => '<span class="badge bg-success">Entregada</span>',
                ];
                return $estados[$e->estado] ?? '<span class="badge bg-secondary">Desconocido</span>';
            })
            ->addColumn('acciones', function ($e) {
                return '
                <button class="btn btn-sm btn-info imprimir" data-id="' . $e->id . '" title="Imprimir">
                    <i class="link-icon" data-lucide="printer"></i>
                </button>
                <button class="btn btn-sm btn-warning editar" data-id="' . $e->id . '" title="Editar">
                    <i class="link-icon" data-lucide="pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger anular" data-id="' . $e->id . '" title="Anular">
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
            ->where('estado', 'P')
            ->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addColumn('emisor', fn($e) => ($e->emisor?->nombres ?? '') . ' ' . ($e->emisor?->apellidos ?? ''))
            ->addColumn('dni_emisor', fn($e) => $e->emisor?->documento ?? '-')
            ->addColumn('receptor', fn($e) => ($e->receptor?->nombres ?? '') . ' ' . ($e->receptor?->apellidos ?? ''))
            ->addColumn('origen', fn($e) => $e->sucursal_origen?->nombre_comercial ?? '-')
            ->addColumn('destino', fn($e) => $e->sucursal_destino?->nombre_comercial ?? '-')
            ->addColumn('total', fn($e) => 'S/ ' . number_format($e->total ?? 0, 2))
            ->addColumn('estado', function ($e) {
                $estados = [
                    'E' => '<span class="badge bg-warning">Espera</span>',
                    'P' => '<span class="badge bg-info">Pendiente</span>',
                    'A' => '<span class="badge bg-danger">Sin asignar</span>',
                    'EN' => '<span class="badge bg-success">Entregada</span>',
                ];
                return $estados[$e->estado] ?? '<span class="badge bg-secondary">Desconocido</span>';
            })
            ->addColumn('acciones', function ($e) {
                return '
                <button class="btn btn-sm btn-info imprimir" data-id="' . $e->id . '" title="Imprimir">
                    <i class="link-icon" data-lucide="printer"></i>
                </button>
                <button class="btn btn-sm btn-warning editar" data-id="' . $e->id . '" title="Editar">
                    <i class="link-icon" data-lucide="pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger anular" data-id="' . $e->id . '" title="Anular">
                    <i class="link-icon" data-lucide="trash-2"></i>
                </button>
            ';
            })
            ->rawColumns(['checkbox', 'estado', 'acciones'])
            ->make(true);
    }
    public function guardar(Request $request, EncomiendaService $encomiendaService)
    {
        $request->validate([
            'emisor.documento' => 'required|string|max:20',
            'emisor.nombres' => 'required|string|max:200',
            'receptor.documento' => 'required|string|max:20',
            'receptor.nombres' => 'required|string|max:200',
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
        ]);

        try {
            $emisor = Persona::updateOrCreate(
                ['documento' => $request->input('emisor.documento')],
                [
                    'tipo_documento_id' => $request->input('emisor.tipo_documento_id'),
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

            $receptor = Persona::updateOrCreate(
                ['documento' => $request->input('receptor.documento')],
                [
                    'tipo_documento_id' => $request->input('receptor.tipo_documento_id', 1),
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
