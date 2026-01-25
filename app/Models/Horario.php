<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = "horarios";
    protected $fillable = [
        "tipo_viaje_id",
        "tipo_vehiculo_id",
        "punto_origen_id",
        "punto_destino_id",
        "hora_salida",
        'costo_base',
    ];

    public function horaEnPunto($horarioPuntoId)
    {
        $hora = Carbon::parse($this->hora_salida);

        $ordenDestino = HorarioPunto::findOrFail($horarioPuntoId)->orden;

        $tramos = $this->tramos()
            ->whereHas(
                'origen',
                fn($q) =>
                $q->where('orden', '<', $ordenDestino)
            )
            ->get()
            ->sortBy(fn($t) => $t->origen->orden);

        foreach ($tramos as $tramo) {
            $hora->addMinutes($tramo->duracion_minutos);
        }

        return $hora->format('H:i');
    }


    public function getDuracionTotalAttribute()
    {
        return $this->tramos()->sum('duracion_minutos');
    }

    public function getDuracionTotalFormateadaAttribute()
    {
        $min = $this->duracion_total;
        return floor($min / 60) . 'h ' . ($min % 60) . 'm';
    }

    public function tipo_viaje()
    {
        return $this->belongsTo(TipoViaje::class, "tipo_viaje_id");
    }

    public function getHoraLlegadaAttribute()
    {
        if (!$this->hora_salida) return null;

        return Carbon::parse($this->hora_salida)
            ->addMinutes($this->duracion_total)
            ->format('H:i');
    }

    public function punto_origen()
    {
        return $this->belongsTo(Sucursal::class, "punto_origen_id");
    }
    public function punto_destino()
    {
        return $this->belongsTo(Sucursal::class, "punto_destino_id");
    }
    public function tipo_vehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, "tipo_vehiculo_id");
    }
    public function puntos()
    {
        return $this->hasMany(HorarioPunto::class)->orderBy('orden');
    }
    public function fechas()
    {
        return $this->hasMany(HorarioFecha::class);
    }

    public function getFechaSalidaFormateadaAttribute()
    {
        $fecha = optional($this->fechas->first())->fecha_salida;

        return $fecha
            ? Carbon::parse($fecha)->format('d-m-Y')
            : null;
    }


    public function tramos()
    {
        return $this->hasMany(HorarioTramo::class);
    }
    public function pasajes()
    {
        return $this->hasMany(Pasaje::class);
    }
    public function asignaciones()
    {
        return $this->hasMany(AsignarHorario::class);
    }
    public function getHoraFormateadaAttribute()
    {
        return $this->hora_salida
            ? Carbon::parse($this->hora_salida)->format('H:i')
            : null;
    }
}
