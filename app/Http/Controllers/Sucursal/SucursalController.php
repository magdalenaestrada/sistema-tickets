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
use Illuminate\Validation\Rule;

class SucursalController extends Controller
{
    public function datatable(Request $request, $empresa_id)
    {
        $query = Sucursal::with(['empresa', 'distrito.provincia.departamento'])
            ->where('empresa_id', $empresa_id);

        if ($request->departamento_id) {
            $query->whereHas('distrito.provincia.departamento', function ($q) use ($request) {
                $q->where('id', $request->departamento_id);
            });
        }

        if ($request->provincia_id) {
            $query->whereHas('distrito.provincia', function ($q) use ($request) {
                $q->where('id', $request->provincia_id);
            });
        }

        if ($request->distrito_id) {
            $query->where('distrito_id', $request->distrito_id);
        }

        if ($request->nombre_sucursal) {
            $query->where('nombre_comercial', 'like', '%' . $request->nombre_sucursal . '%');
        }

        return DataTables::of($query)
            ->addColumn('empresa', fn($row) => $row->empresa->razon_social ?? '-')
            ->addColumn('direccion', fn($row) => $row->direccion ?? '-')
            ->addColumn('telefono', fn($row) => $row->telefono ?? '-')
            ->addColumn('distrito', function ($row) {
                return '<span class="badge bg-success-subtle text-dark">' . $row->distrito->nombre . '</span>';
            })
            ->addColumn('serie', function ($row) {
                return '<span class="badge bg-primary-subtle text-primary">' . ($row->serie->descripcion ?? 'N.A') . '</span>';
            })
            ->addColumn('venta_otras', function ($row) {
                if ($row->venta_otras == 1) {
                    return '<span class="badge bg-success-subtle text-success"> PERMITIR </span>';
                } else {
                    return '<span class="badge bg-danger-subtle text-danger"> NO PERMITIDO</span>';
                }
            })
            ->addColumn('acciones', function ($sucursal) {

                $acciones = '
            <button class="btn btn-xs ver" data-id="' . $sucursal->id . '">
                <i class="link-icon" data-lucide="info"></i>
            </button>';

                if ($sucursal->estado === 'A') {
                    $acciones .= '
                <button class="btn btn-warning btn-xs editar" data-id="' . $sucursal->id . '">
                    <i class="link-icon" data-lucide="pen"></i>
                </button>

                <button class="btn btn-danger btn-xs desactivar" data-id="' . $sucursal->id . '">
                    <i class="link-icon" data-lucide="eye-closed"></i>
                </button>';
                } else {
                    $acciones .= '
                <button class="btn btn-success btn-xs activar" data-id="' . $sucursal->id . '">
                    <i class="link-icon" data-lucide="eye"></i>
                </button>';
                }

                return $acciones;
            })
            ->rawColumns(['acciones', 'distrito', 'venta_otras', 'serie'])
            ->make(true);
    }

    public function lista()
    {
        return Sucursal::select('id', 'nombre_comercial')->where("estado", "A")->get();
    }
    public function guardar(Request $request)
    {
        $nombre = Str::lower(preg_replace('/\s+/', ' ', trim($request->nombre_comercial)));

        $validated = $request->validate([
            'empresa_id'       => 'required|exists:empresas,id',
            'distrito_id'      => 'required|exists:distritos,id',
            'nombre_comercial' => 'required|string|max:255',
            'direccion'        => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'venta_otras'         => 'nullable|boolean',
            'serie_id'         => 'required|exists:series_sucursal,id',
        ]);

        $existe = Sucursal::where('empresa_id', $request->empresa_id)
            ->where('distrito_id', $request->distrito_id)
            ->whereRaw(
                "LOWER(TRIM(REGEXP_REPLACE(nombre_comercial, '[[:space:]]+', ' '))) = ?",
                [$nombre]
            )
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'errors'  => ['nombre_comercial' => ['Ya existe una sucursal con ese nombre en esta zona.']]
            ], 422);
        }

        $validated['nombre_comercial'] = preg_replace('/\s+/', ' ', trim($request->nombre_comercial));

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
            'serie_id' => $sucursal->serie_id,
            'direccion' => $sucursal->direccion,
            'telefono' => $sucursal->telefono,
            'venta_otras' => $sucursal->venta_otras,

            'distrito_id' => $sucursal->distrito_id,
            'provincia_id' => $sucursal->distrito?->provincia_id,
            'departamento_id' => $sucursal->distrito?->provincia?->departamento_id,

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
        $nombre = Str::lower(preg_replace('/\s+/', ' ', trim($request->nombre_comercial)));

        $validated = $request->validate([
            'empresa_id'       => 'required|exists:empresas,id',
            'distrito_id'      => 'required|exists:distritos,id',
            'nombre_comercial' => 'required|string|max:255',
            'direccion'        => 'nullable|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'venta_otras' => 'nullable|boolean',
            'serie_id' => 'required|exists:series_sucursal,id',
        ]);

        $existe = Sucursal::where('empresa_id', $request->empresa_id)
            ->where('distrito_id', $request->distrito_id)
            ->where('id', '!=', $sucursal->id)
            ->whereRaw(
                "LOWER(TRIM(REGEXP_REPLACE(nombre_comercial, '[[:space:]]+', ' '))) = ?",
                [$nombre]
            )
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'errors'  => ['nombre_comercial' => ['Ya existe una sucursal con ese nombre en esta zona.']]
            ], 422);
        }

        $validated['nombre_comercial'] = preg_replace('/\s+/', ' ', trim($request->nombre_comercial));

        $sucursal->update($validated);

        return response()->json(['success' => true]);
    }
    public function activar(Sucursal $sucursal)
    {
        $sucursal->update(['estado' => 'A']);
        return response()->json(['success' => true]);
    }

    public function desactivar(Sucursal $sucursal)
    {
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
