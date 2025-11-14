<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitEventoRequest;
use App\Models\Evento;
use App\Models\TipoEvento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::with('persona', 'tipo_evento')->get();
        $datos_eventos = [];

        foreach ($eventos as $evento) {

            if ($evento->tipo_evento_id == 1 && $evento->persona && $evento->persona->fecha_nacimiento) {

                $fechaOriginal = Carbon::parse($evento->persona->fecha_nacimiento);

                for ($year = 2000; $year <= 2050; $year++) {
                    $cumple = $fechaOriginal->copy()->year($year);

                    $datos_eventos[] = [
                        'title'       => "🎂 " . $evento->persona->nombres . ' ' . $evento->persona->apellidos,
                        'tipo'        => "Cumpleaños",
                        'start'       => $cumple->format('Y-m-d'),
                        'end'         => $cumple->format('Y-m-d'),
                        'edad'        => $year - $fechaOriginal->year,
                        'persona'     => $evento->persona->nombres . ' ' . $evento->persona->apellidos,
                        'descripcion' => $evento->descripcion,
                    ];
                }

                continue;
            }

            $datos_eventos[] = [
                'tipo'        => $evento->tipo_evento->descripcion,
                'title'       => $evento->titulo,
                'start'       => $evento->fecha_inicio,
                'end'         => $evento->fecha_fin,
                'edad'        => null,
                'persona'     => null,
                'descripcion' => $evento->descripcion,
            ];
        }

        return view('eventos.index', compact('datos_eventos'));
    }

    public function guardar(SubmitEventoRequest $request)
    {
        $user = Auth::user();
        $evento = Evento::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'tipo_evento_id' => $request->tipo_evento_id,
        ]);

        activity()
            ->causedBy($user)
            ->withProperties(['evento_id' => $evento->id])
            ->log('Evento creado');
        return response()->json(['success' => true]);
    }

    public function actualizar(SubmitEventoRequest $request, Evento $evento)
    {
        $user = Auth::user();
        $evento_id = Evento::findOrFail($evento->id);
        if ($evento->tipo_evento_id != 1) {

            $evento->update([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'tipo_evento_id' => $request->tipo_evento_id,
            ]);
        } else {
            abort(403, 'No se puede modificar un cumpleaños, por favor editar directamente en empleados.');
        };


        activity()
            ->causedBy($user)
            ->withProperties(['evento_id' => $evento_id])
            ->log('Evento actualizado');
        return response()->json(['success' => true]);
    }

    public function eliminar(Evento $evento)
    {
        $user = Auth::user();
        $evento_id = $evento->id;
        $evento->delete();
        activity()
            ->causedBy($user)
            ->withProperties(['evento_id' => $evento_id])
            ->log('Evento eliminado');
        return response()->json(['success' => true]);
    }
}
