<?php

namespace App\Http\Controllers;

use App\Models\HorarioPunto;
use App\Models\Horario;
use App\Models\HorarioTramo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HorarioPuntoController extends Controller
{
    public function index(Horario $horario)
    {
        $puntos = $horario->puntos()
            ->with(['sucursal:id,nombre_comercial'])
            ->orderBy('orden')
            ->get();

        return response()->json($puntos);
    }

    public function store(Request $request, Horario $horario)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'duracion_minutos' => 'required|integer|min:1',
            'costo_tramo' => 'required|numeric|min:0',
        ]);

        $ultimoOrden = $horario->puntos()->max('orden') ?? 0;

        $punto = HorarioPunto::create([
            'horario_id'  => $horario->id,
            'sucursal_id' => $request->sucursal_id,
            'orden'       => $ultimoOrden + 1,
        ]);

        $puntoAnterior = $horario->puntos()
            ->where('orden', $ultimoOrden)
            ->first();

        if ($puntoAnterior) {
            $horaActual = Carbon::parse($horario->hora_salida);

            $duracionAcumulada = $horario->tramos()->sum('duracion_minutos');
            $horaActual->addMinutes($duracionAcumulada + $request->duracion_minutos);

            HorarioTramo::create([
                'horario_id'       => $horario->id,
                'punto_origen_id'  => $puntoAnterior->id,
                'punto_destino_id' => $punto->id,
                'duracion_minutos' => $request->duracion_minutos,
                'costo_tramo'      => $request->costo_tramo,
                'hora_llegada'     => $horaActual->format('H:i'),
            ]);
        }

        return response()->json(['success' => true, 'punto' => $punto]);
    }

    public function update(Request $request, Horario $horario, HorarioPunto $punto)
    {
        $request->validate([
            'sucursal_id'      => 'required|exists:sucursales,id',
            'duracion_minutos' => 'required|integer|min:1',
            'costo_tramo'      => 'required|numeric|min:0',
        ]);

        $punto->update(['sucursal_id' => $request->sucursal_id]);

        $tramo = HorarioTramo::where('horario_id', $horario->id)
            ->where('punto_destino_id', $punto->id)
            ->first();

        if ($tramo) {
            $tramo->update([
                'duracion_minutos' => $request->duracion_minutos,
                'costo_tramo'      => $request->costo_tramo,
            ]);

            $this->recalcularHorasLlegada($horario);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Horario $horario, HorarioPunto $punto)
    {
        HorarioTramo::where('horario_id', $horario->id)
            ->where(function ($q) use ($punto) {
                $q->where('punto_origen_id', $punto->id)
                    ->orWhere('punto_destino_id', $punto->id);
            })->delete();

        $punto->delete();

        $horario->puntos()->orderBy('orden')->each(function ($p, $index) {
            $p->update(['orden' => $index + 1]);
        });

        $this->recalcularHorasLlegada($horario);

        return response()->json(['success' => true]);
    }

    public function lote(Request $request)
    {
        $horarios = $request->input('horarios');

        $puntos = HorarioPunto::with('sucursal')
            ->whereIn('horario_id', $horarios)
            ->get()
            ->groupBy('horario_id')
            ->map(function ($items) {
                return $items->map(function ($p) {
                    return [
                        'id' => $p->sucursal_id,
                        'nombre' => strtolower($p->sucursal->nombre_comercial)
                    ];
                });
            });
        return response()->json($puntos);
    }

    private function recalcularHorasLlegada(Horario $horario)
    {
        $tramos = HorarioTramo::where('horario_id', $horario->id)
            ->with('origen')
            ->get()
            ->sortBy(fn($t) => $t->origen->orden); // ← ordenar en PHP está bien

        $horaActual = Carbon::parse($horario->hora_salida);

        foreach ($tramos as $tramo) {
            $horaActual->addMinutes($tramo->duracion_minutos);
            $tramo->update(['hora_llegada' => $horaActual->format('H:i')]);
        }
    }
    public function show(Horario $horario, HorarioPunto $punto)
    {
        return response()->json($punto);
    }
}
