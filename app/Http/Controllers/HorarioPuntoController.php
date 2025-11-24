<?php

namespace App\Http\Controllers;

use App\Models\HorarioPunto;
use App\Models\HorarioTramo;
use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioPuntoController extends Controller
{
    public function index(Horario $horario)
    {
        $puntos = $horario->puntos()
            ->with(['origen:id,nombre_comercial', 'destino:id,nombre_comercial'])
            ->orderBy('orden')
            ->get();

        return response()->json($puntos);
    }

    public function store(Request $request, Horario $horario)
    {
        $request->validate([
            'destino_id' => 'required|exists:sucursales,id',
            'costo_acumulado' => 'required|numeric|min:0',
        ]);

        $ultimoOrden = $horario->puntos()->max('orden') ?? 0;

        $punto = $horario->puntos()->create([
            'origen_id' => $horario->punto_origen_id,
            'destino_id' => $request->destino_id,
            'orden' => $ultimoOrden + 1,
            'costo_acumulado' => $request->costo_acumulado,
        ]);

        $this->actualizarTramos($horario);

        return response()->json(['success' => true, 'punto' => $punto]);
    }


    public function update(Request $request, Horario $horario, HorarioPunto $punto)
    {
        $request->validate([
            'destino_id' => 'required|exists:sucursales,id',
            'costo_acumulado' => 'required|numeric|min:0',
        ]);

        $punto->update([
            'destino_id' => $request->destino_id,
            'costo_acumulado' => $request->costo_acumulado,
        ]);

        $this->actualizarTramos($horario);

        return response()->json(['success' => true, 'punto' => $punto]);
    }

    public function destroy(Horario $horario, HorarioPunto $punto)
    {
        $punto->delete();
        $this->actualizarTramos($horario);

        return response()->json(['success' => true]);
    }

    private function actualizarTramos(Horario $horario)
    {
        $horario->tramos()->delete();

        $puntos = $horario->puntos()->orderBy('orden')->get();

        for ($i = 0; $i < $puntos->count(); $i++) {
            $destino = $puntos[$i];

            $origen_id = $i === 0 ? $horario->punto_origen_id : $puntos[$i - 1]->destino_id;

            HorarioTramo::create([
                'horario_id' => $horario->id,
                'punto_origen_id' => $origen_id,
                'punto_destino_id' => $destino->destino_id,
                'costo' => $destino->costo_acumulado - ($i === 0 ? 0 : $puntos[$i - 1]->costo_acumulado),
                'orden' => $i + 1,
            ]);
        }
    }
    public function show(Horario $horario, HorarioPunto $punto)
    {
        return response()->json($punto);
    }
}
