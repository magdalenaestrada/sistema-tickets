<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Str;
use Yajra\DataTables\DataTables;

class EmpresaController extends Controller
{
    public function index()
    {
        return view('empresas.index');
    }

    public function datatable(Request $request)
    {
        $empresas = Empresa::select(['id', 'documento', 'razon_social', 'nombre_comercial', 'direccion']);

        return DataTables::of($empresas)
            ->addColumn('acciones', function ($empresa) {
                return '
                    <button class="btn btn-secondary btn-xs ver" data-id="' . $empresa->id . '">
                        <i class="link-icon" data-lucide="eye"></i> 
                    </button>
                    <button class="btn btn-warning btn-xs editar" data-id="' . $empresa->id . '">
                        <i class="link-icon" data-lucide="pen"></i> 
                    </button>
                    <button class="btn btn-primary btn-xs sucursales" data-id="' . $empresa->id . '">
                        <i class="link-icon" data-lucide="building-2"></i> 
                    </button>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'documento' => 'required',
            'razon_social' => 'required',
        ]);

        $empresa = Empresa::create($validated + [
            'nombre_comercial' => Str::upper($request->nombre_comercial),
            'direccion' => $request->direccion,
            'usuario_facturacion' => $request->usuario_facturacion,
            'contrasena_facturacion' => $request->contrasena_facturacion,
        ]);

        return response()->json(['success' => true, 'empresa' => $empresa]);
    }

    public function actualizar(Request $request, Empresa $empresa)
    {
        $validated = $request->validate([
            'documento' => 'required',
            'razon_social' => 'required',
        ]);

        $empresa->update($validated + [
            'nombre_comercial' => $request->nombre_comercial,
            'direccion' => $request->direccion,
            'usuario_facturacion' => $request->usuario_facturacion,
            'contrasena_facturacion' => $request->contrasena_facturacion,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $empresa = Empresa::findOrFail($id);
        return response()->json($empresa);
    }
}
