<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        $departamentos = Departamento::select('id', 'nombre')->get();
        $provincias = Provincia::select('id', 'nombre')->get();
        $distritos = Distrito::select('id', 'nombre')->get();
        $roles = Role::all();

        return view('users.index', compact('distritos',  'departamentos', 'roles','provincias'));
    }

    public function datatable(Request $request)
    {
        $users = User::with('persona', 'roles')
            ->select('users.*');

        if ($request->filled('empleado')) {
            $users->whereHas('persona', function ($q) use ($request) {
                $q->where('nombres', 'like', '%' . $request->empleado . '%')
                    ->orWhere('apellidos', 'like', '%' . $request->empleado . '%')
                    ->orWhere('razon_social', 'like', '%' . $request->empleado . '%');
            });
        }

        if ($request->filled('username')) {
            $users->where('username', 'like', '%' . $request->username . '%');
        }

        return DataTables::of($users)
            ->addColumn('empleado', function ($u) {
                return $u->persona->razon_social
                    ?? trim($u->persona->nombres . ' ' . $u->persona->apellidos);
            })
            ->addColumn('estado', function ($u) {
                if ($u->estado == "A") {
                    return '<span class="badge rounded-pill bg-success"> Activo </span>';
                } else {
                    return '<span class="badge  rounded-pill bg-danger"> Inactivo </span>';
                }
            })->addColumn('acciones', function ($u) {

                $acciones = '
        <button class="btn btn-warning btn-xs editar"
            data-id="' . $u->id . '">
            <i data-lucide="edit"></i>
        </button>
    ';

                if ($u->estado === 'A') {

                    $acciones .= '
            <button class="btn btn-danger btn-xs desactivar"
                data-id="' . $u->id . '">
                <i data-lucide="eye-off"></i>
            </button>
        ';
                } else {

                    $acciones .= '
            <button class="btn btn-success btn-xs activar"
                data-id="' . $u->id . '">
                <i data-lucide="eye"></i>
            </button>
        ';
                }

                return $acciones;
            })
            ->rawColumns(['acciones', 'estado'])
            ->make(true);
    }


    public function mostrar(User $user)
    {
        $user->load('persona', 'roles');

        if ($user->roles->isNotEmpty()) {
            $user->rol = $user->roles->pluck('name')->first();
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'persona' => [
                'documento' => $user->persona->documento,
                'nombre' => $user->persona->razon_social
                    ?? trim($user->persona->nombres . ' ' . $user->persona->apellidos),
            ]
        ]);
    }


    public function actualizar(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|confirmed|min:6',
        ]);

        $data = [
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function activar($user)
    {
        $actual = Auth::id();
        if ($actual != $user) {
            $user = User::findOrFail($user);
            $user->update([
                "estado" => "A",
            ]);
            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu propio usuario'
            ]);
        }
    }

    public function desactivar($user)
    {
        $actual = Auth::id();
        if ($actual != $user) {
            $user = User::findOrFail($user);
            $user->update([
                "estado" => "I",
            ]);
            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu propio usuario'
            ]);
        }
    }
}
