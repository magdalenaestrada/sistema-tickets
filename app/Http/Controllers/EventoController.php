<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
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
                'title'       => $evento->titulo,
                'start'       => $evento->fecha_inicio,
                'end'         => $evento->fecha_fin,
                'tipo'        => $evento->tipo_evento->descripcion,
                'persona'     => null,
                'edad'        => null,
                'descripcion' => $evento->descripcion
            ];
        }

        return view('eventos.index', compact('datos_eventos'));
    }

    public function getEventos()
    {
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
                'title'       => $evento->titulo,
                'start'       => $evento->fecha_inicio,
                'end'         => $evento->fecha_fin,
                'tipo'        => $evento->tipo_evento->descripcion,
                'persona'     => null,
                'edad'        => null,
                'descripcion' => $evento->descripcion
            ];
        }

        return $datos_eventos;
    }
}
