<?php

namespace App\Http\Controllers;

use App\Models\Descuento;
use App\Models\Persona;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DescuentoController extends Controller
{
    public function index()
    {
        $tipos_documentos = TipoDocumentoPersona::all();
        return view('descuentos.index', compact("tipos_documentos"));
    }

    public function datatable()
    {
        $data = Descuento::with('persona')->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addColumn('persona', fn($d) => $d->persona?->nombres ?? '-')
            ->addColumn('activo', fn($d) => $d->activo ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>')
            ->addColumn('acciones', function ($d) {
                return '
                    <button class="btn btn-sm btn-warning editar" data-id="' . $d->id . '">
                        <i class="link-icon" data-lucide="pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-danger eliminar" data-id="' . $d->id . '">
                        <i class="link-icon" data-lucide="trash-2"></i>
                    </button>
                ';
            })
            ->rawColumns(['activo', 'acciones'])
            ->make(true);
    }

    public function mostrar($id)
    {
        $descuento = Descuento::with('persona')->findOrFail($id);
        return response()->json($descuento);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|unique:descuentos,codigo,' . $request->id,
            'tipo_documento_id' => 'required|integer',
            'documento' => 'nullable|string|max:20',
            'nombres' => 'nullable|string|max:200',
            'apellidos' => 'nullable|string|max:200',
            'razon_social' => 'nullable|string|max:255',
            'cantidad_usos' => 'nullable|integer|min:1',
            'fecha_maxima' => 'nullable|date',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        try {

            $persona_id = null;

            if ($request->filled('documento')) {
                $persona = Persona::updateOrCreate(
                    [
                        'documento' => $request->documento
                    ],
                    [
                        'tipo_documento_id' => $request->tipo_documento_id,
                        'nombres' => $request->tipo_documento_id == 1 ? $request->nombres : null,
                        'apellidos' => $request->tipo_documento_id == 1 ? $request->apellidos : null,
                        'razon_social' => $request->tipo_documento_id == 2 ? $request->razon_social : null,
                        'estado' => 'A'
                    ]
                );

                $persona_id = $persona->id;
            }

            $cantidad_usos = $request->filled('cantidad_usos')
                ? $request->cantidad_usos
                : null;

            $descuento = Descuento::updateOrCreate(
                ['id' => $request->id],
                [
                    'codigo' => $request->codigo,
                    'cantidad_usos' => $cantidad_usos,
                    'fecha_maxima' => $request->fecha_maxima,
                    'monto_efectivo' => $request->monto_efectivo,
                    'porcentaje' => $request->porcentaje,
                    'activo' => 1,
                    'persona_id' => $persona_id,
                ]
            );
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function eliminar($id)
    {
        $descuento = Descuento::findOrFail($id);
        $descuento->delete();
        return response()->json(['success' => true]);
    }

    public function buscar(Request $request)
    {
        $codigo = $request->codigo;
        $descuento = Descuento::where('codigo', $codigo)->first();

        if (!$descuento) {
            return response()->json(['error' => 'Código no encontrado']);
        }

        if (!$descuento->isActivo()) {
            return response()->json(['error' => 'Descuento inactivo o vencido']);
        }

        return response()->json([
            'monto_efectivo' => $descuento->monto_efectivo,
            'porcentaje' => $descuento->porcentaje,
        ]);
    }
}
