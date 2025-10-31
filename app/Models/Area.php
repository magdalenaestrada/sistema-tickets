<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = "areas";
    protected $fillable = [
        "descripcion"
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class);
    }
     public function cargos()
    {
        return $this->hasMany(Cargo::class);
    }
}
