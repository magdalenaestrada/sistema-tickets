<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoMovimientoCaja extends Model
{
    protected $table = "tipo_movimiento_caja";
    protected $fillable = [
        "descripcion"
    ];
    public function subtipo_movimiento(){
        return $this->hasMany(SubtipoMovimientoCaja::class, "tipo_movimiento_caja_id");
    }
}
