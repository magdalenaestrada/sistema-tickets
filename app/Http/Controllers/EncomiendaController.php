<?php

namespace App\Http\Controllers;

use App\Models\BilleteraDigital;
use App\Models\Encomienda;
use App\Models\EncomiendaDetalle;
use App\Models\MetodoPago;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Models\TipoEncomienda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function guardar(Request $request)
    {
        $request->validate([
            'emisor.documento' => 'required|string|max:20',
            'emisor.nombres' => 'required|string|max:200',
            'receptor.documento' => 'required|string|max:20',
            'receptor.nombres' => 'required|string|max:200',
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $emisor = Persona::updateOrCreate(
                [
                    'documento' => $request->emisor_documento
                ],
                [
                    'tipo_documento_id' => $request->emisor_tipo_documento_id,
                    'distrito_id'        => $request->emisor_distrito_id,
                    'nombres'            => $request->emisor_nombre,
                    'apellidos'          => $request->emisor_apellido ?? null,
                    'telefono'           => $request->emisor_telefono ?? null,
                    'celular'            => $request->emisor_celular ?? null,
                    'correo'             => $request->emisor_correo ?? null,
                    'direccion'          => $request->emisor_direccion ?? null,
                    'estado'             => 'A',
                    'fecha_creacion'     => now(),
                ]
            );


            $receptor = Persona::updateOrCreate(
                ['documento' => $request->receptor['documento']],
                [
                    'tipo_documento_id' => $request->receptor['tipo_documento_id'] ?? 1,
                    'distrito_id' => $request->receptor['distrito_id'] ?? 1,
                    'nombres' => $request->receptor['nombres'],
                    'apellidos' => $request->receptor['apellidos'] ?? null,
                    'telefono' => $request->receptor['telefono'] ?? null,
                    'celular' => $request->receptor['celular'] ?? null,
                    'correo' => $request->receptor['correo'] ?? null,
                    'direccion' => $request->receptor['direccion'] ?? null,
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $encomienda = Encomienda::create([
                'sucursal_id' => $request->sucursal_id ?? null,
                'usuario_id' => Auth::id(),
                'emisor_persona_id' => $emisor->id,
                'receptor_persona_id' => $receptor->id,
                'distrito_id' => $request->distrito_id ?? 1,
                'venta_id' => null,
                'estado' => 'A',
                'total' => $request->total,
                'fecha_creacion' => now(),
            ]);

            // 🔹 Crear detalles
            foreach ($request->detalles as $detalle) {
                EncomiendaDetalle::create([
                    'encomienda_id' => $encomienda->id,
                    'tipo_equipaje' => $detalle['tipo_equipaje'],
                    'descripcion' => $detalle['descripcion'],
                    'peso' => $detalle['peso'],
                    'costo' => $detalle['costo'],
                ]);
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Encomienda registrada correctamente']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
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
