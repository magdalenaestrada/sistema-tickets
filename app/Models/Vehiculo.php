<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiculo extends Model
{
    protected $table = "vehiculos";
    protected $fillable = [
        "tipo_vehiculo_id",
        "numero_placa",
        "fecha_creacion",
        "estado",
        "marca",
        "habilitacion_vehicular",
    ];

    public function tipo_vehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, "tipo_vehiculo_id", "id");
    }
}
