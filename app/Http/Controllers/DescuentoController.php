<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Descuento;
use App\Models\DescuentoCargo;
use App\Models\DescuentoPersona;
use App\Models\Empleado;
use App\Models\Persona;
use App\Models\TipoCupon;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DescuentoController extends Controller
{
    public function index()
    {
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipo_cupones = TipoCupon::where('estado', "A")->get();
        $empleados = Empleado::where("estado", "A")->get();
        $cargos = Cargo::whereHas('empleados')->get();
        $hoy = Carbon::now("America/Lima")->format("Y-m-d");
        return view('descuentos.index', compact("tipos_documentos", "tipo_cupones", "empleados", "cargos", "hoy"));
    }

    public function datatable()
    {
        $data = Descuento::with('empleados')->orderBy('id', 'asc');

        return DataTables::of($data)
            ->addColumn('tipo_cupon', fn($d) => $d->tipo_cupon?->descripcion ?? '-')
            ->addColumn('persona', function ($d) {
                if ($d->empleados->count() > 1) {
                    return "Asignado a más de 1 persona";
                } elseif ($d->empleados->count() === 1) {
                    return $d->empleados->first()->persona->nombre_completo ?? '-';
                } else {
                    return '-';
                }
            })->addColumn('descuento', function ($d) {
                if ($d->monto_efectivo > 0) {
                    return  'S/ ' . $d->monto_efectivo;
                } else {
                    return $d->porcentaje . '%';
                }
            })
            ->addColumn('activo', fn($d) => $d->activo ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>')
            ->addColumn('acciones', function ($d) {
                $btnActivar = '';
                $btnDesactivar = '';
                $btnEditar = '';
                $btnEliminar = '';

                if ($d->tipo_cupon->estado === 'A') {

                    if ($d->activo === 1) {
                        $btnDesactivar = ' <button class="btn btn-xs btn-danger desactivar" data-id="' . $d->id . '">
            <i class="link-icon" data-lucide="eye-closed" style="pointer-events:none;"></i>
       </button>';

                        $btnEditar = ' <button class="btn btn-xs btn-warning editar" data-id="' . $d->id . '">
            <i class="link-icon" data-lucide="pencil"></i>
        </button>';

                        $btnEliminar = ' <button class="btn btn-xs btn-danger eliminar" data-id="' . $d->id . '">
            <i class="link-icon" data-lucide="trash-2"></i>
        </button>';
                    } else {
                        $btnActivar = '<button class="btn btn-xs btn-success activar" data-id="' . $d->id . '">
            <i class="link-icon" data-lucide="eye" style="pointer-events:none;"></i>
       </button>';
                    }
                } else {
                    $btnActivar = '<button class="btn btn-xs btn-secondary" disabled>
        <i data-lucide="lock"></i>
    </button>';
                }

                return $btnActivar . $btnDesactivar . $btnEditar . $btnEliminar;
            })

            ->rawColumns(['activo', 'acciones'])
            ->make(true);
    }

    public function mostrar($id)
    {
        $descuento = Descuento::with('empleados', 'cargos')->findOrFail($id);
        return response()->json($descuento);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'tipo_cupon_id' => 'required',
            'tipo_asignacion_id' => 'required',
            'codigo' => 'required|unique:descuentos,codigo,' . $request->id
        ]);

        try {

            if ($request->id) {
                $descuento = Descuento::findOrFail($request->id);

                $descuento->update([
                    'tipo_cupon_id' => $request->tipo_cupon_id,
                    'codigo' => $request->codigo,
                    'cantidad_usos' => $request->cantidad_usos,
                    'fecha_maxima' => $request->fecha_maxima,
                    'monto_efectivo' => $request->monto_efectivo,
                    'porcentaje' => $request->porcentaje,
                    'tipo_asignacion_id' => $request->tipo_asignacion_id,
                    'tipo_descuento_id' => $request->tipo_descuento_id,
                ]);

                DescuentoPersona::where('descuento_id', $descuento->id)->delete();
                DescuentoCargo::where('descuento_id', $descuento->id)->delete();
            } else {

                $descuento = Descuento::create([
                    'tipo_cupon_id' => $request->tipo_cupon_id,
                    'codigo' => $request->codigo,
                    'cantidad_usos' => $request->cantidad_usos,
                    'fecha_maxima' => $request->fecha_maxima,
                    'monto_efectivo' => $request->monto_efectivo,
                    'porcentaje' => $request->porcentaje,
                    'tipo_asignacion_id' => $request->tipo_asignacion_id,
                    'tipo_descuento_id' => $request->tipo_descuento_id,
                    'activo' => 1,
                ]);
            }


            if ($request->tipo_asignacion_id === 'P') {

                if ($request->has('empleados_asignados')) {
                    foreach ($request->empleados_asignados as $empleado_id) {
                        DescuentoPersona::create([
                            "descuento_id" => $descuento->id,
                            "empleado_id" => $empleado_id
                        ]);
                    }
                }
            } elseif ($request->tipo_asignacion_id === 'G') {

                if ($request->has('cargos_asignados')) {
                    foreach ($request->cargos_asignados as $cargo_id) {
                        DescuentoCargo::create([
                            "descuento_id" => $descuento->id,
                            "cargo_id" => $cargo_id
                        ]);
                    }
                }
            }

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

    public function activar(Descuento $descuento)
    {
        $descuento->update(['activo' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Descuento activado correctamente'
        ]);
    }

    public function desactivar(Descuento $descuento)
    {
        $descuento->update(['activo' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Descuento desactivado correctamente'
        ]);
    }

    public function cuponesPersona(Request $request)
    {
        $documento = $request->documento;

        if (!$documento) {
            return response()->json([]);
        }

        $empleado = Empleado::with(['persona', 'cargo'])
            ->whereHas('persona', function ($q) use ($documento) {
                $q->where('documento', $documento);
            })
            ->first();

        if (!$empleado) {
            return response()->json([]);
        }

        $cargoId = $empleado->cargo_id;

        $descuentos = Descuento::query()
            ->where('activo', 1)
            ->where(function ($q) {
                $q->whereNull('fecha_maxima')
                    ->orWhereDate('fecha_maxima', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('cantidad_usos')
                    ->orWhere('cantidad_usos', '>', 0);
            })
            ->where(function ($q) use ($empleado, $cargoId) {
                $q->where('tipo_asignacion_id', 'T');
                $q->orWhereHas('empleados', function ($sub) use ($empleado) {
                    $sub->where('empleado_id', $empleado->id);
                });
                if ($cargoId) {
                    $q->orWhereHas('cargos', function ($sub) use ($cargoId) {
                        $sub->where('cargo_id', $cargoId);
                    });
                }
            })
            ->get();

        return response()->json($descuentos);
    }
}
