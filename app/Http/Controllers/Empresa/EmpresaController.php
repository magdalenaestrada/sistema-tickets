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
use Illuminate\Support\Facades\Storage;


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
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $empresa = Empresa::create($validated + [
            'documento' => $request->documento,
            'nombre_comercial' => Str::upper($request->nombre_comercial),
            'razon_social' => $request->razon_social,
            'direccion' => $request->direccion,
            'usuario_facturacion' => $request->usuario_facturacion,
            'contrasena_facturacion' => $request->contrasena_facturacion,
            'logo' => $logoPath,
        ]);

        return response()->json(['success' => true, 'empresa' => $empresa]);
    }


    public function actualizar(Request $request, Empresa $empresa)
    {
        $validated = $request->validate([
            'documento' => 'required',
            'razon_social' => 'required',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {

            if ($empresa->logo && Storage::disk('public')->exists($empresa->logo)) {
                Storage::disk('public')->delete($empresa->logo);
            }

            $empresa->logo = $request->file('logo')->store('logos', 'public');
        }

        $empresa->update($validated + [
            'documento' => $request->documento,
            'nombre_comercial' => $request->nombre_comercial,
            'razon_social' => $request->razon_social,
            'direccion' => $request->direccion,
            'usuario_facturacion' => $request->usuario_facturacion,
            'contrasena_facturacion' => $request->contrasena_facturacion,
            'logo' => $empresa->logo,
        ]);

        return response()->json(['success' => true]);
    }

    public function mostrar($id)
    {
        $empresa = Empresa::findOrFail($id);
        return response()->json($empresa);
    }
}
