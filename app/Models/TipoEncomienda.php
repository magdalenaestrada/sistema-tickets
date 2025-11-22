<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEncomienda extends Model
{
    protected $table = 'tipo_encomienda';

    protected $fillable = [
        'descripcion',
        'precio_base',
        'peso_limite',
        'costo_kilo_extra'
    ];
}
