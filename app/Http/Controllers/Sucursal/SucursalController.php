<?php

namespace App\Http\Controllers\Sucursal;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Sucursal;
use App\Models\Empresa;
use App\Models\Distrito;
use App\Models\Provincia;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class SucursalController extends Controller
{
    public function datatable($empresa_id)
    {
        $sucursales = Sucursal::where('empresa_id', $empresa_id)
            ->with(['empresa', 'distrito'])
            ->get();

        return DataTables::of($sucursales)
            ->addColumn('empresa', fn($row) => $row->empresa->razon_social ?? '-')
            ->addColumn('distrito', fn($row) => $row->distrito->nombre ?? '-')
            ->addColumn('acciones', function ($sucursal) {

                $acciones = '
        <button class="btn btn-xs ver" data-id="' . $sucursal->id . '">
            <i class="link-icon" data-lucide="info"></i>
        </button>
    ';

                if ($sucursal->estado === 'A') {
                    $acciones .= '
            <button class="btn btn-warning btn-xs editar" data-id="' . $sucursal->id . '">
                <i class="link-icon" data-lucide="pen"></i>
            </button>

            <button class="btn btn-danger btn-xs desactivar" data-id="' . $sucursal->id . '">
                <i class="link-icon" data-lucide="power-off"></i>
            </button>
        ';
                } else {
                    $acciones .= '
            <button class="btn btn-success btn-xs activar" data-id="' . $sucursal->id . '">
                <i class="link-icon" data-lucide="circle-power"></i>
            </button>
        ';
                }

                return $acciones;
            })

            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function guardar(Request $request)
    {
        $validated = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'distrito_id' => 'required|exists:distritos,id',
            'nombre_comercial' => 'nullable|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
        ]);

        Sucursal::create($validated);

        return response()->json(['success' => true]);
    }
    public function show($id)
    {
        $sucursal = Sucursal::with(
            'empresa',
            'distrito.provincia.departamento'
        )->findOrFail($id);

        return response()->json([
            'id' => $sucursal->id,
            'nombre_comercial' => $sucursal->nombre_comercial,
            'direccion' => $sucursal->direccion,
            'telefono' => $sucursal->telefono,

            // ✅ IDs (los necesitas)
            'distrito_id' => $sucursal->distrito_id,
            'provincia_id' => $sucursal->distrito?->provincia_id,
            'departamento_id' => $sucursal->distrito?->provincia?->departamento_id,

            // ✅ Objetos para mostrar
            'empresa' => $sucursal->empresa,

            'distrito' => $sucursal->distrito ? [
                'id' => $sucursal->distrito->id,
                'nombre' => $sucursal->distrito->nombre,
            ] : null,

            'provincia' => $sucursal->distrito?->provincia ? [
                'id' => $sucursal->distrito->provincia->id,
                'nombre' => $sucursal->distrito->provincia->nombre,
            ] : null,

            'departamento' => $sucursal->distrito?->provincia?->departamento ? [
                'id' => $sucursal->distrito->provincia->departamento->id,
                'nombre' => $sucursal->distrito->provincia->departamento->nombre,
            ] : null,
        ]);
    }

    public function actualizar(Request $request, Sucursal $sucursal)
    {
        $sucursal->update($request->all());
        return response()->json(['success' => true]);
    }
    public function activar(Sucursal $sucursal)
    {
        $sucursal->update(['estado' => 'A']);
        return response()->json(['success' => true]);
    }

    public function desactivar(Sucursal $sucursal)
    {
        // Empleados activos
        $empleadosActivos = $sucursal->empleados()
            ->where('estado', 'A')
            ->exists();

        if ($empleadosActivos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede desactivar la sucursal porque tiene empleados activos.'
            ], 422);
        }

        $usuariosActivos = $sucursal->usuarios()
            ->where('estado', 'A')
            ->exists();

        if ($usuariosActivos) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede desactivar la sucursal porque tiene usuarios activos.'
            ], 422);
        }

        $sucursal->update(['estado' => 'I']);

        return response()->json([
            'success' => true,
            'message' => 'Sucursal desactivada correctamente.'
        ]);
    }
}
