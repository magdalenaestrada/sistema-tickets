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
use Yajra\DataTables\DataTables;

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
        $query = Cliente::with(['persona', 'user']);

        if ($request->documento) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('documento', 'like', '%' . $request->documento . '%');
            });
        }

        if ($request->nombre) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('nombres', 'like', '%' . $request->nombre . '%');
            });
        }

        if ($request->apellido) {
            $query->whereHas('persona', function ($q) use ($request) {
                $q->where('apellidos', 'like', '%' . $request->apellido . '%');
            });
        }

        return datatables()->of($query)
            ->addColumn('documento', fn($c) => $c->persona->documento)
            ->addColumn('nombre', fn($c) => $c->persona->nombres)
            ->addColumn('apellido', fn($c) => $c->persona->apellidos)
            ->addColumn('acciones', fn($c) => '
            <button class="btn btn-danger btn-xs eliminar" data-id="' . $c->id . '">
                <i class="link-icon" data-lucide="trash-2"></i>
            </button>
        ')
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'documento' => 'required|string|max:20',
            'nombres' => 'required|string|max:200',
            'apellidos' => 'nullable|string|max:200',
            'correo' => 'nullable|email|max:150',
        ]);

        $persona = Persona::updateOrCreate(
            ['documento' => $request->input('documento')],
            [
                'tipo_documento_id' => $request->input('tipo_documento_id', 1),
                'distrito_id' => $request->input('distrito_id', 1),
                'nombres' => $request->input('nombres'),
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
    }

    public function edit(Cliente $cliente)
    {
        $cliente->load('persona');
        return response()->json($cliente);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'documento' => 'required|string|max:20',
            'nombres' => 'required|string|max:200',
            'apellidos' => 'nullable|string|max:200',
            'correo' => 'nullable|email|max:150',
        ]);

        $persona = Persona::updateOrCreate(
            ['id' => $cliente->persona_id],
            [
                'tipo_documento_id' => $request->input('tipo_documento_id', 1),
                'distrito_id' => $request->input('distrito_id', 1),
                'nombres' => $request->input('nombres'),
                'apellidos' => $request->input('apellidos'),
                'telefono' => $request->input('telefono'),
                'celular' => $request->input('celular'),
                'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                'correo' => $request->input('correo'),
                'direccion' => $request->input('direccion'),
                'estado' => 'A',
            ]
        );

        $cliente->update(['user_id' => $request->input('user_id')]);

        return redirect()->back()->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente.'
        ]);
    }
}
