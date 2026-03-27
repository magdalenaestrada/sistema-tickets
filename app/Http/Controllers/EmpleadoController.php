<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Empleado;
use App\Models\Encomienda;
use App\Models\Evento;
use App\Models\Persona;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmpleadoController extends Controller
{
    public function index()
    {
        $departamentos = Departamento::select('id', 'nombre')->get();
        $provincias = Provincia::select('id', 'nombre')->get();
        $distritos = Distrito::select('id', 'nombre')->get();
        $empleados = Empleado::all();
        $cargos = Cargo::all();
        $sucursales = Sucursal::where("estado", "A")->get();
        $roles = Role::all();
        $eventos = Evento::with('persona', 'tipo_evento')->get();
        $datos_eventos = [];
        $mesActual = date('m');
        $yearActual = date('Y');

        foreach ($eventos as $evento) {

            if ($evento->tipo_evento_id == 1 && $evento->persona && $evento->persona->fecha_nacimiento) {
                $fechaNacimiento = Carbon::parse($evento->persona->fecha_nacimiento);

                if ($fechaNacimiento->month == $mesActual) {
                    $datos_eventos[] = [
                        'title'       => "🎂 " . $evento->persona->nombres . ' ' . $evento->persona->apellidos,
                        'start'       => $yearActual . '-' . str_pad($fechaNacimiento->month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($fechaNacimiento->day, 2, '0', STR_PAD_LEFT),
                        'tipo'        => 'Cumpleaños',
                        'persona'     => $evento->persona->nombres . ' ' . $evento->persona->apellidos,
                        'edad'        => $yearActual - $fechaNacimiento->year,
                        'descripcion' => $evento->descripcion
                    ];
                }

                continue;
            }

            $datos_eventos[] = [
                'title'       => $evento->titulo ?? '',
                'start'       => $evento->fecha_inicio ? \Carbon\Carbon::parse($evento->fecha_inicio)->format('Y-m-d') : null,
                'end'         => $evento->fecha_fin ? \Carbon\Carbon::parse($evento->fecha_fin)->format('Y-m-d') : null,
                'tipo'        => optional($evento->tipo_evento)->descripcion ?? '',
                'persona'     => null,
                'edad'        => null,
                'descripcion' => $evento->descripcion ?? ''
            ];
        }

        return view('empleados.index', compact('distritos', 'cargos', 'roles', 'departamentos', 'datos_eventos', 'provincias', 'sucursales', 'empleados'));
    }

    public function datatable()
    {
        $empleados = Empleado::with(['persona', 'sucursal', 'cargo'])
            ->select('empleados.*');

        return DataTables::of($empleados)
            ->addColumn('nombre', fn($e) => $e->persona->nombres . ' ' . $e->persona->apellidos)
            ->addColumn('documento', fn($e) => $e->persona->documento ?? '-')
            ->addColumn('sucursal', fn($e) => $e->sucursal->nombre_comercial ?? '-')
            ->addColumn('cargo', fn($e) => $e->cargo->descripcion ?? '-')
            ->addColumn('acciones', fn($e) => '
                <button class="btn btn-secondary btn-xs ver" data-id="' . $e->id . '">
                    <i class="link-icon " <i data-lucide="info"></i>

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
            $persona = Persona::where('documento', $request->documento)->first();
            $request->validate([
                'documento' => [
                    'required',
                    Rule::unique('personas', 'documento')->ignore($persona->id ?? null)
                ],
                'nombres' => 'required',
                'apellidos' => 'required',
            ]);
            if ($persona) {
                $empleadoExistente = Empleado::where('persona_id', $persona->id)
                    ->where('id', '!=', $request->empleado_id ?? 0)
                    ->first();

                if ($empleadoExistente) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un empleado registrado con el documento ' . $request->documento,
                    ], 422);
                }
            }
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

                Evento::where('persona_id', $persona->id)->update([
                    'titulo' => "CUMPLEAÑOS DE " . $persona->nombres . " " . $persona->apellidos,
                    'tipo_evento_id' => 1,
                    'fecha_inicio' => $request->fecha_nacimiento,
                    'fecha_fin' => $request->fecha_nacimiento,
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

                Evento::create([
                    'persona_id' => $persona->id,
                    'titulo' => "Cumpleaños de " . $persona->nombres . " " . $persona->apellidos,
                    'tipo_evento_id' => 1,
                    'fecha_inicio' => $request->fecha_nacimiento,
                    'fecha_fin' => $request->fecha_nacimiento,
                ]);
            }

            $empleado = Empleado::updateOrCreate(
                ['id' => $request->empleado_id],
                [
                    'persona_id' => $persona->id,
                    'sucursal_id' => $request->sucursal_id,
                    'cargo_id' => $request->cargo_id,
                    'tipo_licencia_id' => $request->tipo_licencia_id,
                    'licencia_conducir' => $request->licencia_conducir,
                    'fecha_vencimiento_licencia' => $request->fecha_vencimiento_licencia,
                    'fecha_ingreso' => $request->fecha_ingreso,
                    'estado' => $request->estado ?? 'A',
                ]
            );

            if ($request->chkUsuario) {
                $userExistente = User::where('persona_id', $persona->id)->first();
                $request->validate([
                    'usuario' => [
                        'required',
                        Rule::unique('users', 'username')->ignore($userExistente->id ?? null)
                    ],
                    'password' => 'nullable|min:6',
                ]);

                $dataUser = [
                    'username' => $request->usuario,
                    'numero_licencia' => $request->licencia_conducir,
                    'tipo_licencia_id' => $request->tipo_licencia_id,
                    'sucursal_id' => $request->sucursal_id,
                    'documento' => $persona->documento,
                    'estado' => $request->estado ?? 'A',
                    'fecha_creacion' => now(),

                ];

                if ($request->filled('password')) {
                    $dataUser['password'] = bcrypt($request->password);
                }

                $user = User::updateOrCreate(
                    ['persona_id' => $persona->id],
                    $dataUser
                );

                if ($request->rol) {
                    $rol = Role::find($request->rol);

                    if ($rol) {
                        $user->syncRoles([$rol->name]);
                    }
                }
            }


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
        $empleado = Empleado::with([
            'persona.distrito.provincia.departamento',
            'cargo',
            'sucursal',
            'usuario',
            'usuario.roles'

        ])->findOrFail($id);

        if ($empleado->usuario) {
            $empleado->usuario->rol = $empleado->usuario->roles->first()->id ?? null;
        }
        return response()->json($empleado);
    }


    public function eliminar($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();

        return response()->json(['success' => true, 'message' => 'Empleado eliminado correctamente.']);
    }
}
