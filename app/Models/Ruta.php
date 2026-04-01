<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $table = "rutas";
    protected $fillable = [
        "nombre",
        "tipo_viaje_id",
        "estado"
    ];

    public function puntos()
    {
        return $this->hasMany(RutaPunto::class)->orderBy('orden');
    }

    public function tramos()
    {
        return $this->hasMany(RutaTramo::class);
    }
}
