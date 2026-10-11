<?php

namespace App\Console\Commands;

use App\Models\Salida;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class FinalizarSalidasAutomaticamente extends Command
{
    protected $signature = 'salidas:finalizar';

    protected $description = 'Finaliza automáticamente las salidas después de 24 horas de su última parada';

    public function handle(): int
    {
        $limite = Carbon::now('America/Lima')->subHours(24);

        $cantidad = Salida::query()
            ->where('estado', 'en_ruta')
            ->whereNotNull('fecha_llegada_final_estimada')
            ->where('fecha_llegada_final_estimada', '<=', $limite)
            ->update([
                'estado' => 'finalizada',
                'updated_at' => now(),
            ]);

        $this->info("Salidas finalizadas: {$cantidad}");

        return self::SUCCESS;
    }
}
