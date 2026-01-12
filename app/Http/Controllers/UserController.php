<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function datatable(Request $request)
    {
        $users = User::with('persona')
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
            ->addColumn('acciones', function ($u) {
                return '
                <button class="btn btn-warning btn-xs editar"
                    data-id="' . $u->id . '">
                    <i data-lucide="edit"></i>
                </button>
            ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }


    public function mostrar(User $user)
    {
        $user->load('persona');

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
}
