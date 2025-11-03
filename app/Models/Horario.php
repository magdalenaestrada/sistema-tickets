<?php

namespace App\Models;

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
}
