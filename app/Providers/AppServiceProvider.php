<?php

namespace App\Providers;

use App\Models\Empresa;
use App\Models\Evento;
use Carbon\Carbon;
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

            $hoy = Carbon::now('America/Lima')->startOfDay();

            $notificacionesCumpleaños = Evento::with('persona')
                ->where('tipo_evento_id', 1)
                ->whereMonth('fecha_inicio', $hoy->month)
                ->whereDay('fecha_inicio', $hoy->day)
                ->get();

            $view->with([
                'empresaGlobal' => $empresaGlobal,
                'notificacionesCumpleaños' => $notificacionesCumpleaños,
            ]);
        });
    }
}
