<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoVehiculo extends Model
{
    protected $table = "tipo_vehiculos";
    protected $fillable = [
        "descripcion",
        "ruta_svg",
        "capacidad",
        "peso_bodega",
    ];
    
    public function vehiculos()  {
        return $this->hasMany(Vehiculo::class, "tipo_vehiculo_id");
    }
}
