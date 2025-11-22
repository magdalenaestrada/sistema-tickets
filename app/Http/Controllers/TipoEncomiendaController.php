<?php

namespace App\Http\Controllers;

use App\Models\TipoEncomienda;
use Illuminate\Http\Request;
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
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required',
            'precio_base' => 'required|numeric',
            'peso_limite' => 'nullable|numeric',
            'costo_kilo_extra' => 'nullable|numeric',
        ]);

        TipoEncomienda::create($request->all());

        return redirect()->route('tipo-encomienda.index')
            ->with('success', 'Tipo de encomienda registrado correctamente');
    }

    public function edit($id)
    {
        $tipo = TipoEncomienda::findOrFail($id);
        return response()->json($tipo);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion' => 'required',
            'precio_base' => 'required|numeric',
            'peso_limite' => 'nullable|numeric',
            'costo_kilo_extra' => 'nullable|numeric',
        ]);

        $tipo = TipoEncomienda::findOrFail($id);
        $tipo->update($request->all());

        return redirect()->route('tipo-encomienda.index')
            ->with('success', 'Tipo de encomienda actualizado correctamente');
    }

    public function destroy($id)
    {
        TipoEncomienda::destroy($id);

        return redirect()->route('tipo-encomienda.index')
            ->with('success', 'Tipo de encomienda eliminado correctamente');
    }

    public function listarTodos()
    {
        $tipos = TipoEncomienda::all();
        return response()->json($tipos);
    }
}
