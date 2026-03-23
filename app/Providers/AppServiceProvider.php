<?php

namespace App\Providers;

use App\Models\Caja;
use App\Models\Descuento;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\HorarioFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {

            $empresaGlobal = Empresa::first();
            $auth = Auth::id();

            $caja_activa = Caja::where("usuario_id", $auth)
                ->where("estado", "A")
                ->get();

            $hoy = Carbon::now('America/Lima')->startOfDay();

            $notificacionesCumpleaños = Evento::with('persona')
                ->where('tipo_evento_id', 1)
                ->whereMonth('fecha_inicio', $hoy->month)
                ->whereDay('fecha_inicio', $hoy->day)
                ->get();

            $licencias_por_vencer = Empleado::with('persona')
                ->whereBetween('fecha_vencimiento_licencia', [
                    $hoy,
                    $hoy->copy()->addDays(15)
                ])->get();

            $cupones_por_vencer = Descuento::with('tipo_cupon')
                ->whereBetween('fecha_maxima', [
                    $hoy,
                    $hoy->copy()->addDays(3)
                ])->get();

            $notificaciones = collect();

            foreach ($notificacionesCumpleaños as $evento) {
                $notificaciones->push([
                    'icono' => 'cake',
                    'texto' => 'Cumpleaños de ' . ($evento->persona->nombre_completo ?? ''),
                    'fecha' => 'Hoy 🎂',
                    'url' => route('empleados.index')
                ]);
            }

            foreach ($licencias_por_vencer as $licencia) {

                $fechaVencimiento = Carbon::parse($licencia->fecha_vencimiento_licencia);
                $diasRestantes = (int) $hoy->diffInDays($fechaVencimiento, false);

                $textoDias = match (true) {
                    $diasRestantes > 1 => "En {$diasRestantes} días",
                    $diasRestantes == 1 => "Mañana",
                    $diasRestantes == 0 => "Hoy",
                    $diasRestantes < 0 => "Vencido",
                };

                $notificaciones->push([
                    'icono' => 'alert-triangle',
                    'texto' => 'Licencia por vencer: ' . ($licencia->persona->nombre_completo ?? ''),
                    'fecha' => $textoDias,
                    'url' => route('empleados.index')
                ]);
            }

            foreach ($cupones_por_vencer as $cupon) {

                $fechaVencimiento = Carbon::parse($cupon->fecha_maxima);
                $diasRestantes = (int) $hoy->diffInDays($fechaVencimiento, false);
                $textoDias = match (true) {
                    $diasRestantes > 1 => "En {$diasRestantes} días",
                    $diasRestantes == 1 => "Mañana",
                    $diasRestantes == 0 => "Hoy",
                    $diasRestantes < 0 => "Vencido",
                };

                $notificaciones->push([
                    'icono' => 'alert-triangle',
                    'texto' => 'Cupón por vencer: ' . ($cupon->tipo_cupon->descripcion ?? '') .' - '.($cupon->codigo ?? ''),
                    'fecha' => $textoDias,
                    'url' => route('descuentos.index')
                ]);
            }

            $view->with([
                'empresaGlobal' => $empresaGlobal,
                'notificaciones' => $notificaciones,
                'caja_activa' => $caja_activa,
            ]);
        });
    }
}
