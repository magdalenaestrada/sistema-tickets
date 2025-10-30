<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTercero extends Model
{
    protected $table= "tipo_terceros";
    protected $fillable = [
        "descripcion"
    ];
}
