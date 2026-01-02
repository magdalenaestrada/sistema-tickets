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
                return '
       
        <button class="btn btn-warning btn-xs editar" data-id="' . $sucursal->id . '">
            <i class="link-icon" data-lucide="pen"></i>
        </button>
         <button class="btn btn-xs ver" data-id="' . $sucursal->id . '">
            <i class="link-icon" data-lucide="info"></i>
        </button>
    ';
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
        $sucursal = Sucursal::with(['empresa', 'distrito'])->findOrFail($id);

        return response()->json($sucursal);
    }
    public function actualizar(Request $request, Sucursal $sucursal)
    {
        $sucursal->update($request->all());
        return response()->json(['success' => true]);
    }
}
