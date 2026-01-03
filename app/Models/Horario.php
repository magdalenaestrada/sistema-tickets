<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = "horarios";
    protected $fillable = [
        "tipo_viaje_id",
        "punto_origen_id",
        "punto_destino_id",
        "tipo_vehiculo_id",
        'costo_pasaje',
        "hora_embarque",
        'fecha_salida',
        'repetir_hasta',
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado',
        'domingo',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
    ];

    
    public function tipo_viaje()
    {
        return $this->belongsTo(TipoViaje::class, "tipo_viaje_id");
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
    public function getFechaFormateadaAttribute()
    {
        return $this->fecha_salida
            ? Carbon::parse($this->fecha_salida)->format('d/m/Y')
            : null;
    }

    public function getHoraFormateadaAttribute()
    {
        return $this->hora_embarque
            ? Carbon::parse($this->hora_embarque)->format('H:i')
            : null;
    }
}
