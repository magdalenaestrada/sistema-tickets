<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Persona;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientesController extends Controller
{
    public function index()
    {
        $departamentos = Departamento::select('id', 'nombre')->get();
        $provincias = Provincia::select('id', 'nombre')->get();
        $distritos = Distrito::select('id', 'nombre')->get();
        $clientes = Cliente::with(['user', 'persona'])->get();
        $usuarios = User::all();

        return view('clientes.index', compact('clientes', 'usuarios', 'departamentos', 'provincias', 'distritos'));
    }

    public function datatable(Request $request)
    {
        $query = Cliente::select('clientes.*')
            ->join('personas', 'personas.id', '=', 'clientes.persona_id')
            ->leftJoin('tipo_documento_personas', 'tipo_documento_personas.id', '=', 'personas.tipo_documento_id')
            ->whereNull('personas.deleted_at')
            ->orderBy('clientes.id', 'desc');

        if ($request->filled('tipo_documento_id')) {
            $query->where('personas.tipo_documento_id', $request->tipo_documento_id);
        }

        if ($request->filled('documento')) {
            $query->where('personas.documento', 'like', $request->documento . '%');
        }

        if ($request->filled('nombres')) {
            $busqueda = '%' . $request->nombres . '%';
            $query->where(function ($q) use ($busqueda) {
                $q->where('personas.nombres', 'like', $busqueda)
                    ->orWhere('personas.apellidos', 'like', $busqueda)
                    ->orWhere('personas.razon_social', 'like', $busqueda);
            });
        }

        return datatables()->of($query)
            ->addIndexColumn()

            ->addColumn('tipo_doc', fn($c) => $c->persona->tipoDocumento->codigo ?? '-')
            ->addColumn('documento', fn($c) => $c->persona->documento ?? '-')
            ->addColumn(
                'nombre',
                fn($c) => $c->persona->razon_social
                    ?? trim(($c->persona->nombres ?? '') . ' ' . ($c->persona->apellidos ?? ''))
            )
            ->addColumn('telefono', fn($c) => $c->persona->telefono ?? '-')
            ->addColumn('celular', fn($c) => $c->persona->celular ?? '-')
            ->addColumn('correo', fn($c) => $c->persona->correo ?? '-')
            ->addColumn('acciones', function ($c) {
                $botones = '
                    <button class="btn btn-info btn-xs ver" data-id="' . $c->id . '" title="Ver detalle">
                        <i data-lucide="info"></i>
                    </button>
                ';

                if ((int)$c->id !== 3) {
                    $botones .= '
                        <button class="btn btn-primary btn-xs editar" data-id="' . $c->id . '" title="Editar">
                            <i data-lucide="edit"></i>
                        </button>
                        <button class="btn btn-danger btn-xs eliminar" data-id="' . $c->id . '" title="Eliminar">
                            <i data-lucide="trash-2"></i>
                        </button>
                    ';
                }

                return $botones;
            })
            ->orderColumn('id', 'id $1')

            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:20',
            'correo' => 'nullable|email|max:150',
        ]);

        $persona = Persona::updateOrCreate(
            [
                'documento' => $request->input('documento'),
                'tipo_documento_id' => $request->input('tipo_documento_id'),
            ],
            [
                'tipo_documento_id' => $request->input('tipo_documento_id', 1),
                'distrito_id' => $request->input('distrito_id', 1),
                'nombres' => $request->input('nombres'),
                'razon_social' => $request->input('razon_social'),
                'apellidos' => $request->input('apellidos'),
                'telefono' => $request->input('telefono'),
                'celular' => $request->input('celular'),
                'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                'correo' => $request->input('correo'),
                'direccion' => $request->input('direccion'),
                'estado' => 'A',
                'fecha_creacion' => now(),
            ]
        );

        $user = Auth::id();

        Cliente::updateOrCreate(
            ['persona_id' => $persona->id],
            ['user_id' => $user]
        );

        return response()->json([
            'success' => true,
            'message' => 'Cliente registrado correctamente.'
        ]);
    }

    public function edit(Cliente $cliente)
    {
        $cliente->load('persona.distrito.provincia.departamento');

        $persona = $cliente->persona;
        if ($persona && $persona->distrito) {
            $persona->provincia_id = $persona->distrito->provincia_id;
            $persona->departamento_id = $persona->distrito->provincia->departamento_id;
        }

        return response()->json($cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        if ((int)$cliente->id === 3) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente con ID 3 no puede ser modificado.'
            ], 403);
        }

        $user = Auth::id();

        $request->validate([
            'documento' => 'required|string|max:20',
            'correo' => 'nullable|email|max:150',
        ]);

        $cliente->persona->update([
            'tipo_documento_id' => $request->input('tipo_documento_id', 1),
            'distrito_id' => $request->input('distrito_id', 1),
            'documento' => $request->input('documento'),
            'nombres' => $request->input('nombres'),
            'apellidos' => $request->input('apellidos'),
            'telefono' => $request->input('telefono'),
            'razon_social' => $request->input('razon_social'),
            'celular' => $request->input('celular'),
            'fecha_nacimiento' => $request->input('fecha_nacimiento'),
            'correo' => $request->input('correo'),
            'direccion' => $request->input('direccion'),
            'estado' => 'A',
        ]);

        $cliente->update(['user_id' => $user]);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente.'
        ]);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);

        if ((int)$cliente->id === 3) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente con ID 3 no puede ser eliminado.'
            ], 403);
        }

        $cliente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente.'
        ]);
    }
}
