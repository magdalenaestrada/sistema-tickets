<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Empresa;
use App\Models\Provincia;
use Illuminate\Http\Request;
use Str;
use Yajra\DataTables\DataTables;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresa = Empresa::first();
        $departamentos = Departamento::select('id', 'nombre')->get();
        $provincias = Provincia::select('id', 'nombre')->get();
        $distritos = Distrito::select('id', 'nombre')->get();

        return view('empresas.index', compact('empresa', 'departamentos', 'provincias', 'distritos'));
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
