<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $table = "rutas";
    protected $fillable = [
        "nombre",
        "tipo_viaje_id"
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
