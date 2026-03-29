<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasajeTramo extends Model
{
    protected $table = "pasaje_tramos";
    protected $fillable = [
        "pasaje_id",
        "tramo_id",
    ];
    public function pasajes()
    {
        return $this->belongsToMany(
            Pasaje::class,
            'pasaje_tramos',
            'tramo_id',
            'pasaje_id'
        );
    }
}
