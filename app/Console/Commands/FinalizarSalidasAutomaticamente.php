<?php

namespace App\Console\Commands;

use App\Models\Salida;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FinalizarSalidasAutomaticamente extends Command
{
    protected $signature = 'salidas:finalizar';

    protected $description = 'Finaliza salidas en ruta 24 h después de la hora estimada de llegada a la última parada';

    public function handle(): int
    {
        $tz       = 'America/Lima';
        $ahora    = Carbon::now($tz);
        $cantidad = 0;

        Salida::query()
            ->where('estado', 'en_ruta')
            ->with(['horario.ruta.puntos', 'horario.ruta.tramos'])
            ->chunkById(100, function ($salidas) use ($tz, $ahora, &$cantidad) {
                foreach ($salidas as $salida) {
                    $horario = $salida->horario;
                    $ruta    = $horario?->ruta;

                    if (!$horario || !$ruta) {
                        continue;
                    }

                    $puntos = $ruta->puntos->sortBy('orden')->values();
                    $tramos = $ruta->tramos->keyBy(
                        fn($t) => $t->punto_origen_id . '-' . $t->punto_destino_id
                    );

                    // Hora de salida desde A
                    $llegada = Carbon::parse(
                        $salida->fecha_salida->format('Y-m-d') . ' ' . $horario->getRawOriginal('hora_salida'),
                        $tz
                    );

                    // Sumar cada tramo A→B, B→C, C→D (misma lógica que PasajeController@index)
                    foreach ($puntos as $i => $p) {
                        if ($i === 0) {
                            continue;
                        }
                        $tramo = $tramos->get($puntos[$i - 1]->id . '-' . $p->id);
                        if ($tramo) {
                            $llegada->addMinutes($tramo->duracion_minutos);
                        }
                    }

                    // Llegada estimada a D + 24 h
                    if ($ahora->gte($llegada->copy()->addHours(24))) {
                        $salida->update(['estado' => 'finalizado']);
                        $cantidad++;
                    }
                }
            });

        $this->info("Salidas finalizadas: {$cantidad}");

        return self::SUCCESS;
    }
}