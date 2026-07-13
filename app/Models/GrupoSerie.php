<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoSerie extends Model
{
    protected $table = 'grupos_series';

    protected $fillable = [
        'codigo',
        'descripcion',
        'estado',
    ];
}
