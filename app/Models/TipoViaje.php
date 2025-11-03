<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoViaje extends Model
{
    protected $table = "tipos_viajes";
    protected $fillable = [
        "descripcion"
    ];
    
    public function Horario(){
        return $this->hasMany(Horario::class, "tipo_viaje_id");
    }
}
