<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerificarCajaActiva
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->sucursal_id) {
            return redirect()->back()->with('error', 'Tu usuario no tiene sucursal asignada.');
        }

        $caja = \App\Models\Caja::where('sucursal_id', $user->sucursal_id)
                 ->where('estado', 'A') // A = Activa
                 ->first();

        if (!$caja) {
            return redirect()->route('caja.index')
                ->with('error', 'No tienes una caja activa. Debes abrir caja antes de registrar encomiendas.');
        }

        return $next($request);
    }
}
