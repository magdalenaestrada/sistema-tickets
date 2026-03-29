<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaPunto extends Model
{
    protected $table = "ruta_puntos";
    protected $fillable = [
        'ruta_id',
        'sucursal_id',
        'orden'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, "sucursal_id");
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, "ruta_id");
    }
}
