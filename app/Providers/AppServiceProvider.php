<?php

namespace App\Providers;

use App\Models\Caja;
use App\Models\Descuento;
use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\HorarioFecha;
use App\Models\Salida;
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
            $ahora = Carbon::now('America/Lima');
            $inicioHoy = $ahora->copy()->startOfDay();
            $finHoy = $ahora->copy()->endOfDay();

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
                    $hoy->copy()->subDay(),
                    $hoy->copy()->addDays(3)
                ])
                ->get();

            $salidasCanceladas = Salida::with('horario.ruta')
                ->where('estado', 'cancelado')
                ->whereDate('fecha_salida', $ahora->toDateString())
                ->get();

            $salidasReprogramadas = Salida::with('horario.ruta')
                ->where('estado', 'reprogramado')
                ->whereDate('fecha_salida', $ahora->toDateString())
                ->get();

            $salidasHoy = Salida::with(['horario.ruta'])
                ->whereDate('fecha_salida', $ahora->toDateString())
                ->whereIn('estado', ['programado', 'en_ruta'])
                ->get();

            $salidasPorSalir = $salidasHoy->filter(function ($salida) use ($ahora) {
                if (!$salida->horario?->hora_salida && !$salida->horario?->hora_formateada) {
                    return false;
                }

                $hora = $salida->horario->hora_salida ?? $salida->horario->hora_formateada;

                try {
                    $fechaHoraSalida = Carbon::parse(
                        $salida->fecha_salida->format('Y-m-d') . ' ' . $hora,
                        'America/Lima'
                    );

                    $minutos = $ahora->diffInMinutes($fechaHoraSalida, false);

                    return $minutos >= 0 && $minutos <= 30;
                } catch (\Exception $e) {
                    return false;
                }
            });

            $notificaciones = collect();

            foreach ($notificacionesCumpleaños as $evento) {
                $notificaciones->push([
                    'key' => 'cumple_' . $evento->id,
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
                    'key' => 'licencia_' . $licencia->id,
                    'icono' => 'alert-triangle',
                    'texto' => 'Licencia por vencer: ' . ($licencia->persona->nombre_completo ?? ''),
                    'fecha' => $textoDias,
                    'url' => route('empleados.index')
                ]);
            }

            foreach ($cupones_por_vencer as $cupon) {

                $fechaVencimiento = Carbon::parse($cupon->fecha_maxima);
                $diasRestantes = (int) $hoy->diffInDays($fechaVencimiento, false);

                if ($diasRestantes < -1) {
                    continue;
                }

                $textoDias = match (true) {
                    $diasRestantes > 1 => "En {$diasRestantes} días",
                    $diasRestantes == 1 => "Mañana",
                    $diasRestantes == 0 => "Hoy",
                    $diasRestantes == -1 => "Venció ayer",
                };

                $notificaciones->push([
                    'key' => 'cupon_' . $cupon->id,
                    'icono' => 'alert-triangle',
                    'texto' => 'Cupón por vencer: ' . ($cupon->tipo_cupon->descripcion ?? '') . ' - ' . ($cupon->codigo ?? ''),
                    'fecha' => $textoDias,
                    'url' => route('descuentos.index')
                ]);
            }

            foreach ($salidasCanceladas as $salida) {
                $notificaciones->push([
                    'key' => 'salida_cancelada_' . $salida->id,
                    'icono' => 'x-circle',
                    'texto' => 'Salida cancelada: ' . ($salida->horario?->ruta?->nombre ?? 'Sin ruta'),
                    'fecha' => $salida->fecha_cambio_estado ?? 'Hoy',
                    'url' => route('salidas.index')
                ]);
            }

            foreach ($salidasReprogramadas as $salida) {
                $notificaciones->push([
                    'key' => 'salida_reprogramada_' . $salida->id,
                    'icono' => 'calendar-sync',
                    'texto' => 'Salida reprogramada: ' . ($salida->horario?->ruta?->nombre ?? 'Sin ruta'),
                    'fecha' => $salida->fecha_cambio_estado ?? 'Hoy',
                    'url' => route('salidas.index')
                ]);
            }

            foreach ($salidasPorSalir as $salida) {
                $hora = $salida->horario->hora_salida ?? $salida->horario->hora_formateada;

                $fechaHoraSalida = Carbon::parse(
                    $salida->fecha_salida->format('Y-m-d') . ' ' . $hora,
                    'America/Lima'
                );

                $minutos = (int) $ahora->diffInMinutes($fechaHoraSalida, false);

                $textoTiempo = match (true) {
                    $minutos === 0 => 'Sale ahora',
                    $minutos === 1 => 'Sale en 1 minuto',
                    default => "Sale en {$minutos} minutos",
                };

                $notificaciones->push([
                    'key' => 'salida_proxima_' . $salida->id,
                    'icono' => 'bus',
                    'texto' => 'Salida próxima: ' . ($salida->horario?->ruta?->nombre ?? 'Sin ruta'),
                    'fecha' => $textoTiempo,
                    'url' => route('salidas.index')
                ]);
            }

            $notificaciones = $notificaciones->unique('key')->values();

            $view->with([
                'empresaGlobal' => $empresaGlobal,
                'notificaciones' => $notificaciones,
                'caja_activa' => $caja_activa,
            ]);
        });
    }
}
