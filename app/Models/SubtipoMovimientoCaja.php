<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubtipoMovimientoCaja extends Model
{
    protected $table = "subtipo_movimiento_caja";
    protected $fillable = [
        "tipo_movimiento_caja_id",
        "descripcion"
    ];

    public function tipo_movimiento(){
        return $this->belongsTo(TipoMovimientoCaja::class, "tipo_movimiento_caja_id");
    }
}
