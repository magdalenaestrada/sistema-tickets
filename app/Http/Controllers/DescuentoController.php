<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Descuento;
use App\Models\DescuentoCargo;
use App\Models\DescuentoPersona;
use App\Models\Empleado;
use App\Models\Persona;
use App\Models\TipoCupon;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DescuentoController extends Controller
{
    public function index()
    {
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipo_cupones     = TipoCupon::where('estado', 'A')->get();

        $clientes = Cliente::with('persona')
            ->get()
            ->sortBy(fn($c) => $c->persona->nombres . ' ' . $c->persona->apellidos);

        $empleados = Empleado::with('persona')
            ->get()
            ->sortBy(fn($e) => $e->persona->nombres . ' ' . $e->persona->apellidos);

        $cargos = Cargo::whereHas('empleados')->get();

        $personas         = Persona::orderByRaw("CONCAT(nombres, ' ', apellidos) ASC")->get();


        $hoy = Carbon::now('America/Lima')->format('Y-m-d');

        return view('descuentos.index', compact(
            'tipos_documentos',
            'tipo_cupones',
            'clientes',
            'empleados',
            'cargos',
            'personas',
            'hoy'
        ));
    }
    public function datatable()
    {
        $data = Descuento::with('personas', 'cargos')->orderBy('id', 'asc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('tipo_cupon', fn($d) => $d->tipo_cupon?->descripcion ?? '-')
            ->addColumn('descuento', function ($d) {
                return $d->monto_efectivo > 0
                    ? 'S/ ' . $d->monto_efectivo
                    : $d->porcentaje . '%';
            })
            ->addColumn('asignado_a', function ($d) {
                $badges = [];

                if ($d->tipo_asignacion_id === 'T' && $d->personas->isEmpty() && $d->cargos->isEmpty()) {
                    return '<span class="badge bg-info text-dark">Todos</span>';
                }
                foreach ($d->cargos as $dc) {
                    $desc = $dc->cargo->descripcion ?? '-';
                    $badges[] = '<span class="badge bg-secondary">Cargo: ' . e($desc) . '</span>';
                }

                $conteoPersonas = $d->personas->count();

                if ($conteoPersonas > 1) {
                    $badges[] = '<span class="badge bg-primary"> +2 PERSONAS</span>';
                } else {
                    foreach ($d->personas as $dp) {
                        $nombre = $dp->persona->nombres ?? '-';
                        $badges[] = '<span class="badge bg-primary">' . e($nombre) . '</span>';
                    }
                }

                return $badges
                    ? implode(' ', $badges)
                    : '<span class="text-muted">Sin asignar</span>';
            })
            ->addColumn('activo', fn($d) => $d->activo
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>')
            ->addColumn('acciones', function ($d) {
                $btnActivar = $btnDesactivar = $btnEditar = $btnEliminar = '';

                if ($d->tipo_cupon->estado === 'A') {
                    if ($d->activo === 1) {
                        $btnDesactivar = '<button class="btn btn-xs btn-danger desactivar" data-id="' . $d->id . '">
                        <i class="link-icon" data-lucide="eye-closed" style="pointer-events:none;"></i>
                    </button>';
                        $btnEditar = '<button class="btn btn-xs btn-warning editar" data-id="' . $d->id . '">
                        <i class="link-icon" data-lucide="pencil"></i>
                    </button>';
                        $btnEliminar = '<button class="btn btn-xs btn-danger eliminar" data-id="' . $d->id . '">
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
            ->orderColumn('id', 'id $1')

            ->rawColumns(['activo', 'acciones', 'asignado_a'])
            ->make(true);
    }

    public function mostrar($id)
    {
        $descuento = Descuento::with([
            'personas.persona.cliente',
            'personas.persona.empleado',
            'cargos'
        ])->findOrFail($id);

        $reglas = [];

        if ($descuento->cargos->isNotEmpty()) {
            $reglas[] = [
                'tipo'   => 'G',
                'cargos' => $descuento->cargos->pluck('cargo_id'),
            ];
        }

        $clientes = [];
        $empleados = [];

        foreach ($descuento->personas as $dp) {

            if ($dp->persona?->cliente) {
                $clientes[] = $dp->persona_id;
            }

            if ($dp->persona?->empleado) {
                $empleados[] = $dp->persona_id;
            }
        }

        if (!empty($clientes)) {
            $reglas[] = [
                'tipo' => 'P',
                'personas' => $clientes,
            ];
        }

        if (!empty($empleados)) {
            $reglas[] = [
                'tipo' => 'E',
                'personas' => $empleados,
            ];
        }

        if (empty($reglas)) {
            $reglas[] = ['tipo' => 'T'];
        }

        return response()->json([
            'id' => $descuento->id,
            'tipo_cupon_id' => $descuento->tipo_cupon_id,
            'codigo' => $descuento->codigo,
            'cantidad_usos' => $descuento->cantidad_usos,
            'fecha_maxima' => $descuento->fecha_maxima,
            'monto_efectivo' => $descuento->monto_efectivo,
            'porcentaje' => $descuento->porcentaje,
            'tipo_descuento_id' => $descuento->tipo_descuento_id,
            'reglas' => $reglas,
        ]);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'tipo_cupon_id'     => 'required',
            'tipo_descuento_id' => 'required',
            'codigo'            => 'required|unique:descuentos,codigo,' . $request->id,
        ]);

        try {
            $datos = [
                'tipo_cupon_id'      => $request->tipo_cupon_id,
                'codigo'             => $request->codigo,
                'cantidad_usos'      => $request->cantidad_usos,
                'fecha_maxima'       => $request->fecha_maxima,
                'monto_efectivo'     => $request->monto_efectivo,
                'porcentaje'         => $request->porcentaje,
                'tipo_descuento_id'  => $request->tipo_descuento_id,
                'tipo_asignacion_id' => $this->derivarTipoAsignacion($request),
            ];

            if ($request->id) {
                $descuento = Descuento::findOrFail($request->id);
                $descuento->update($datos);
            } else {
                $descuento = Descuento::create(array_merge($datos, ['activo' => 1]));
            }

            DescuentoPersona::where('descuento_id', $descuento->id)->delete();
            DescuentoCargo::where('descuento_id', $descuento->id)->delete();

            foreach ($request->clientes_asignados ?? [] as $persona_id) {
                DescuentoPersona::create([
                    'descuento_id' => $descuento->id,
                    'persona_id'   => $persona_id,
                ]);
            }

            foreach ($request->empleados_asignados ?? [] as $persona_id) {
                DescuentoPersona::create([
                    'descuento_id' => $descuento->id,
                    'persona_id'   => $persona_id,
                ]);
            }

            foreach ($request->cargos_asignados ?? [] as $cargo_id) {
                DescuentoCargo::create([
                    'descuento_id' => $descuento->id,
                    'cargo_id'     => $cargo_id,
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    private function derivarTipoAsignacion(Request $request): string
    {
        $tipos = $request->reglas_tipo ?? [];

        if (in_array('T', $tipos)) return 'T';
        if (in_array('C', $tipos)) return 'C';
        if (!empty($request->cargos_asignados)) return 'G';
        if (!empty($request->clientes_asignados)) return 'P';
        if (!empty($request->empleados_asignados)) return 'E';

        return 'T';
    }
    public function eliminar($id)
    {
        try {

            DB::transaction(function () use ($id) {

                $descuento = Descuento::findOrFail($id);

                $descuento->personas()->delete();
                $descuento->cargos()->delete();

                $descuento->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Cupón eliminado correctamente.'
            ]);
        } catch (QueryException $e) {

            if ($e->errorInfo[1] == 1451) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lo sentimos, este cupón está siendo utilizado y no puede eliminarse.'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al eliminar el cupón.'
            ], 500);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado.'
            ], 500);
        }
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

        $persona = Persona::where('documento', $documento)->first();
        $cliente = Cliente::where('persona_id', $persona?->id)->exists();
        $cargoId = null;

        if ($persona) {
            $empleado = Empleado::where('persona_id', $persona->id)->first();
            $cargoId = $empleado?->cargo_id;
        }



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
            ->where(function ($q) use ($persona, $cargoId, $cliente) {

                // TODOS
                $q->where('tipo_asignacion_id', 'T');

                // PERSONA
                if ($persona) {
                    $q->orWhereHas(
                        'personas',
                        fn($sub) => $sub->where('persona_id', $persona->id)
                    );
                }

                // CARGO
                if ($cargoId) {
                    $q->orWhereHas(
                        'cargos',
                        fn($sub) => $sub->where('cargo_id', $cargoId)
                    );
                }

                if ($cliente) {
                    $q->orWhere('tipo_asignacion_id', 'C');
                }
            })
            ->get();

        return response()->json($descuentos);
    }
}
