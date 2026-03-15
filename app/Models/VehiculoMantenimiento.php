<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoMantenimiento extends Model
{
    protected $table = "vehiculos_mantenimiento";
    protected $fillable = [
        "fecha_inicio",
        "hora_inicio",
        "razon_id",
        "fecha_fin",
        "hora_fin",
        "vehiculo_id",
        "descripcion",
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, "vehiculo_id");
    }

    public function razon()
    {
        return $this->belongsTo(RazonMantenimiento::class, "razon_id");
    }
}
