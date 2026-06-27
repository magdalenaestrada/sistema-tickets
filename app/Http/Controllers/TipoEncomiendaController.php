<?php

namespace App\Http\Controllers;

use App\Models\TipoEncomienda;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class TipoEncomiendaController extends Controller
{
    public function index(Request $request)
    {
        return view('tipo-encomienda.index');
    }

    public function datatable()
    {
        $data = TipoEncomienda::orderBy('id', 'desc');

        return DataTables::of($data)
            ->editColumn('precio_base', function ($t) {
                return $t->precio_base ?? '-';
            })
            ->addIndexColumn()
            ->addColumn('acciones', function ($t) {
                return '
                <button class="btn btn-warning btn-xs editar" data-id="' . $t->id . '">
                        <i class="link-icon" data-lucide="pen"></i> 
                </button>
                <button class="btn btn-danger btn-xs eliminar" data-id="' . $t->id . '">
                    <i class="link-icon" data-lucide="trash-2"></i>
                </button>
            ';
            })
            ->orderColumn('id', 'id $1')
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function store(Request $request)
    {
        $request->merge([
            'descripcion' => strtoupper(trim($request->descripcion))
        ]);

        $request->validate([
            'descripcion' => 'required|unique:tipo_encomienda,descripcion',
            'precio_base' => 'nullable|numeric',
            'peso_limite' => 'nullable|numeric',
            'costo_kilo_extra' => 'nullable|numeric',
        ]);

        TipoEncomienda::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de encomienda registrado correctamente'
        ]);
    }

    public function edit($id)
    {
        $tipo = TipoEncomienda::findOrFail($id);
        return response()->json($tipo);
    }

    public function update(Request $request, $id)
    {
        $request->merge([
            'descripcion' => strtoupper(trim($request->descripcion))
        ]);

        $request->validate([
            'descripcion' => [
                'required',
                Rule::unique('tipo_encomienda', 'descripcion')->ignore($id),
            ],
            'precio_base' => 'required|numeric',
            'peso_limite' => 'nullable|numeric',
            'costo_kilo_extra' => 'nullable|numeric',
        ]);

        $tipo = TipoEncomienda::findOrFail($id);
        $tipo->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de encomienda actualizado correctamente'
        ]);
    }

    public function destroy($id)
    {
        try {
            $tipo = TipoEncomienda::findOrFail($id);
            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de encomienda eliminado correctamente.'
            ]);
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lo sentimos, este tipo de encomienda está siendo utilizado y no puede eliminarse.'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el registro.'
            ], 500);
        }
    }
    public function listarTodos()
    {
        $tipos = TipoEncomienda::all();
        return response()->json($tipos);
    }
}
