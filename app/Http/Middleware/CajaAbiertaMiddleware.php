<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CajaAbiertaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!auth()->user()->hasRole('Administrador')) {

            $tieneCaja = Caja::where('usuario_id', auth()->id())
                ->where('estado', 'A')
                ->exists();

            if (!$tieneCaja) {
                return redirect()
                    ->route('caja.index')
                    ->with('abrir_caja', true);
            }
        }

        return $next($request);
    }
}
