<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazonMantenimiento extends Model
{
    protected $table = "razones_mantenimiento";
    protected $fillable = [
        "descripcion"
    ];
}
