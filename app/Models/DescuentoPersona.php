<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescuentoPersona extends Model
{
    protected $table = "descuentos_persona";
    protected $fillable = [
        "descuento_id",
        "empleado_id",
    ];

    public function descuento()
    {
        return $this->belongsTo(Descuento::class, "descuento_id");
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, "empleado_id");
    }
}
