<?php

namespace App\Http\Controllers;

use App\Models\BilleteraDigital;
use App\Models\Encomienda;
use App\Models\MetodoPago;
use App\Models\Persona;
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
    public function index()
    {
        $sucursales = Sucursal::select('id', 'nombre_comercial')->get();
        $tipos_documentos = TipoDocumentoPersona::all();
        return view('encomiendas.index', compact('sucursales', 'tipos_documentos'));
    }

    public function formulario()
    {
        Carbon::now();
        $metodos_pago = MetodoPago::all();
        $sucursales = Sucursal::select('id', 'nombre_comercial')->get();
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $tipo_encomiendas = TipoEncomienda::all();
        $billeteras_digitales = BilleteraDigital::all();
        return view('encomiendas.create', compact('sucursales', 'tipos_documentos', 'tipo_encomiendas', 'tipos_documentos_facturas', 'metodos_pago', 'billeteras_digitales'));
    }
    public function datatable()
    {
        $data = Encomienda::with(['emisor', 'receptor'])->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addColumn('emisor', fn($e) => $e->emisor->nombres . ' ' . $e->emisor->apellidos)
            ->addColumn('receptor', fn($e) => $e->receptor->nombres . ' ' . $e->receptor->apellidos)
            ->addColumn('acciones', function ($e) {
                return '
                    <button class="btn btn-secondary btn-xs ver" data-id="' . $e->id . '">
                        <i class="link-icon" data-lucide="eye"></i>
                    </button>
                    <button class="btn btn-warning btn-xs editar" data-id="' . $e->id . '">
                        <i class="link-icon" data-lucide="pen"></i>
                    </button>
                    <button class="btn btn-danger btn-xs anular" data-id="' . $e->id . '">
                        <i class="link-icon" data-lucide="trash-2"></i>
                    </button>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request, EncomiendaService $encomiendaService)
    {
        Log::info('Datos del request', $request->all());

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
                'message' => 'Encomienda registrada correctamente',
                'data' => $encomienda
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
        $encomienda = Encomienda::findOrFail($id);
        $encomienda->update([
            'estado' => 'P',
            'fecha_procesado' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
