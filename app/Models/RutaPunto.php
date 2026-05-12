<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaPunto extends Model
{
    protected $table = "ruta_puntos";

    protected $fillable = [
        'ruta_id',
        'sucursal_id',
        'distrito_id',
        'pueblito_id',
        'orden'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }

    public function pueblito()
    {
        return $this->belongsTo(Pueblito::class);
    }
}
