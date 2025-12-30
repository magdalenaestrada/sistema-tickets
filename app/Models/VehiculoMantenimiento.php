<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoMantenimiento extends Model
{
    protected $table = "vehiculos_mantenimiento";
    protected $fillable = [
        "fecha_inicio",
        "hora_inicio",
        "fecha_fin",
        "hora_fin",
        "vehiculo_id",
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, "vehiculo_id");
    }
}
