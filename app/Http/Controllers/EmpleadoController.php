<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Persona;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class EmpleadoController extends Controller
{
    public function index()
    {
        return view('empleados.index');
    }

    public function datatable()
    {
        $empleados = Empleado::with(['persona', 'area', 'sucursal', 'cargo'])
            ->select('empleados.*');

        return DataTables::of($empleados)
            ->addColumn('nombre', fn($e) => $e->persona->nombres . ' ' . $e->persona->apellidos)
            ->addColumn('documento', fn($e) => $e->persona->documento ?? '-')
            ->addColumn('area', fn($e) => $e->area->descripcion ?? '-')
            ->addColumn('sucursal', fn($e) => $e->sucursal->nombre_comercial ?? '-')
            ->addColumn('cargo', fn($e) => $e->cargo->descripcion ?? '-')
            ->addColumn('acciones', fn($e) => '
                <button class="btn btn-secondary btn-xs ver" data-id="' . $e->id . '">
                    <i class="link-icon" data-lucide="eye"></i>
                </button>
                <button class="btn btn-warning btn-xs editar" data-id="' . $e->id . '">
                    <i class="link-icon" data-lucide="pen"></i>
                </button>
                <button class="btn btn-danger btn-xs eliminar" data-id="' . $e->id . '">
                    <i class="link-icon" data-lucide="trash-2"></i>
                </button>
            ')
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        DB::beginTransaction();

        try {
            // 🔹 Validación simple
            $request->validate([
                'documento' => 'required',
                'nombres' => 'required',
                'apellidos' => 'required',
            ]);

            // 🔹 Buscar o crear persona
            $persona = Persona::where('documento', $request->documento)->first();

            if ($persona) {
                $persona->update([
                    'tipo_documento_id' => $request->tipo_documento_id,
                    'distrito_id' => $request->distrito_id,
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'telefono' => $request->telefono,
                    'celular' => $request->celular,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'estado' => 'A'
                ]);
            } else {
                $persona = Persona::create([
                    'tipo_documento_id' => $request->tipo_documento_id,
                    'distrito_id' => $request->distrito_id,
                    'documento' => $request->documento,
                    'nombres' => $request->nombres,
                    'apellidos' => $request->apellidos,
                    'telefono' => $request->telefono,
                    'celular' => $request->celular,
                    'correo' => $request->correo,
                    'direccion' => $request->direccion,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]);
            }

            // 🔹 Crear o actualizar empleado
            $empleado = Empleado::updateOrCreate(
                ['id' => $request->empleado_id],
                [
                    'persona_id' => $persona->id,
                    'area_id' => $request->area_id,
                    'sucursal_id' => $request->sucursal_id,
                    'cargo_id' => $request->cargo_id,
                    'supervisor_id' => $request->supervisor_id,
                    'tipo_licencia_id' => $request->tipo_licencia_id,
                    'licencia_conducir' => $request->licencia_conducir,
                    'fecha_vencimiento_licencia' => $request->fecha_vencimiento_licencia,
                    'fecha_ingreso' => $request->fecha_ingreso,
                    'estado' => $request->estado ?? 'A',
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empleado guardado correctamente.',
                'empleado' => $empleado,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function mostrar($id)
    {
        $empleado = Empleado::with(['persona', 'area', 'cargo', 'sucursal'])->findOrFail($id);
        return response()->json($empleado);
    }

    public function eliminar($id)
    {
        $empleado = Empleado::findOrFail($id);

        // 🔹 Validación de dependencias
        if (method_exists($empleado, 'tareas') && $empleado->tareas()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar, el empleado tiene tareas asignadas.',
            ]);
        }

        $empleado->delete();

        return response()->json(['success' => true, 'message' => 'Empleado eliminado correctamente.']);
    }
}
