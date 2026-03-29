<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = "horarios";

    protected $fillable = [
        "ruta_id",
        "tipo_viaje_id",
        "tipo_vehiculo_id",
        "hora_salida",
        "costo_base",
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function tipo_viaje()
    {
        return $this->belongsTo(TipoViaje::class, "tipo_viaje_id");
    }

    public function tipo_vehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, "tipo_vehiculo_id");
    }

    public function salidas()
    {
        return $this->hasMany(Salida::class);
    }

    public function pasajes()
    {
        return $this->hasMany(Pasaje::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(AsignarHorario::class);
    }

    public function getDuracionTotalAttribute()
    {
        return $this->ruta?->tramos()->sum('duracion_minutos') ?? 0;
    }

    public function getDuracionTotalFormateadaAttribute()
    {
        $min = $this->duracion_total;
        return floor($min / 60) . 'h ' . ($min % 60) . 'm';
    }

    public function getHoraLlegadaAttribute()
    {
        if (!$this->hora_salida) return null;

        return Carbon::parse($this->hora_salida)
            ->addMinutes($this->duracion_total)
            ->format('H:i');
    }

    public function getHoraFormateadaAttribute()
    {
        return $this->hora_salida
            ? Carbon::parse($this->hora_salida)->format('H:i')
            : null;
    }

    public function horaEnPunto($rutaPuntoId)
    {
        $hora = Carbon::parse($this->hora_salida);

        $ordenDestino = $this->ruta->puntos()
            ->where('id', $rutaPuntoId)
            ->value('orden');

        $tramos = $this->ruta->tramos()
            ->with(['origen', 'destino'])
            ->get()
            ->filter(fn($t) => $t->origen->orden < $ordenDestino)
            ->sortBy(fn($t) => $t->origen->orden);

        foreach ($tramos as $tramo) {
            $hora->addMinutes($tramo->duracion_minutos);
        }

        return $hora->format('H:i');
    }
}
