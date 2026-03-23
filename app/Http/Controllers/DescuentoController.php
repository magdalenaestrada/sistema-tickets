<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Descuento;
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
        $cargos = Cargo::all();
        return view('descuentos.index', compact("tipos_documentos", "tipo_cupones", "empleados", "cargos"));
    }

    public function datatable()
    {
        $data = Descuento::with('persona')->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addColumn('tipo_cupon', fn($d) => $d->tipo_cupon?->descripcion ?? '-')
            ->addColumn('persona', fn($d) => $d->persona->nombre_completo ?? '-')
            ->addColumn('descuento', function ($d) {
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
        $descuento = Descuento::with('persona')->findOrFail($id);
        return response()->json($descuento);
    }

    public function guardar(Request $request)
    {
        $now = Carbon::now("America/Lima");

        $request->validate([
            'tipo_cupon_id' => 'required',
            'tipo_asignacion_id' => 'required',
        ]);

        if ($request->tipo_asignacion_id === 'G') {
            $request->validate([
                'codigo' => 'unique:descuentos,codigo'
            ], [
                'codigo.unique' => 'Ya existe un cupón con este código'
            ]);
        }

        try {

            if ($request->tipo_asignacion_id === 'G') {

                Descuento::create([
                    'tipo_cupon_id' => $request->tipo_cupon_id,
                    'codigo' => $request->codigo,
                    'cantidad_usos' => $request->cantidad_usos,
                    'fecha_maxima' => $request->fecha_maxima,
                    'monto_efectivo' => $request->monto_efectivo,
                    'porcentaje' => $request->porcentaje,
                    'activo' => 1,
                    'persona_id' => null,
                ]);
            }


            if ($request->tipo_asignacion_id === 'P') {

                if (!$request->has('empleados_asignados')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Debes seleccionar empleados'
                    ], 400);
                }

                foreach ($request->empleados_asignados as $empleado_id) {

                    $empleado = Empleado::with('persona')->find($empleado_id);

                    if (!$empleado) continue;

                    Descuento::create([
                        'tipo_cupon_id' => $request->tipo_cupon_id,
                        'codigo' => $request->codigo,
                        'cantidad_usos' => $request->cantidad_usos,
                        'fecha_maxima' => $request->fecha_maxima,
                        'monto_efectivo' => $request->monto_efectivo,
                        'porcentaje' => $request->porcentaje,
                        'activo' => 1,
                        'persona_id' => $empleado->persona_id,
                    ]);
                }

                foreach ($request->cargos_asignados as $cargo_id) {

                    $empleado = Empleado::with('persona')->where("cargo_id", $cargo_id);

                    if (!$empleado) continue;

                    Descuento::create([
                        'tipo_cupon_id' => $request->tipo_cupon_id,
                        'codigo' => $request->codigo,
                        'cantidad_usos' => $request->cantidad_usos,
                        'fecha_maxima' => $request->fecha_maxima,
                        'monto_efectivo' => $request->monto_efectivo,
                        'porcentaje' => $request->porcentaje,
                        'activo' => 1,
                        'persona_id' => $empleado->persona_id,
                    ]);
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
}
