<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoLicencia extends Model
{
    protected $table = "tipo_licencias";
    protected $fillable = [
        "descripcion"
    ];
}
