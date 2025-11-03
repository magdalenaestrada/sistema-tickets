<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = "vehiculos";
    protected $fillable = [
        "tipo_vehiculo_id",
        "numero_placa",
        "cantidad_conductores",
        "fecha_creacion"
    ];

    public function tipo_vehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, "tipo_vehiculo_id", "id");
    }
}
