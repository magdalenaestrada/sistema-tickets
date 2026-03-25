<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DescuentoCargo extends Model
{
    protected $table = "descuentos_cargos";
    protected $fillable = [
        "descuento_id",
        "cargo_id",
    ];

    public function descuento()
    {
        return $this->belongsTo(Descuento::class, "descuento_id");
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, "cargo_id");
    }
}
