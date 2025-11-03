<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distrito extends Model
{
    protected $table = "distritos";

    public function provincia(){
        return $this->belongsTo(Provincia::class, "provincia_id");
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }
}
