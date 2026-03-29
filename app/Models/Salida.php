<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    protected $table = "salidas";
    protected $fillable = [
        'horario_id',
        'fecha_salida',
        'estado',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class, "horario_id");
    }
}
