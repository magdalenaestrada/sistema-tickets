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
            'persona.documento' => 'nullable|string|max:20',
            'persona.nombres' => 'nullable|string|max:200',
            'persona.apellidos' => 'nullable|string|max:200',
            'cantidad_usos' => 'nullable|integer|min:1',
            'fecha_maxima' => 'nullable|date',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'porcentaje' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $persona_id = null;

            if ($request->filled('persona.documento')) {
                $persona = Persona::updateOrCreate(
                    ['documento' => $request->input('persona.documento')],
                    [
                        'nombres' => $request->input('persona.nombres'),
                        'apellidos' => $request->input('persona.apellidos', ''),
                        'tipo_documento_id' => $request->input('persona.tipo_documento_id', 1),
                        'distrito_id' => $request->input('persona.distrito_id', 1),
                        'telefono' => $request->input('persona.telefono'),
                        'correo' => $request->input('persona.correo'),
                        'estado' => 'A',
                    ]
                );
                $persona_id = $persona->id;
            }

            $data = $request->all();
            $data['persona_id'] = $persona_id;

            $descuento = Descuento::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'descuento_id' => $descuento->id
            ]);
        } catch (\Throwable $th) {
            \Log::error('Error al guardar descuento: ' . $th->getMessage());
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function eliminar($id)
    {
        $descuento = Descuento::findOrFail($id);
        $descuento->delete();
        return response()->json(['success' => true]);
    }
}
