<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioFecha extends Model
{
    protected $table = "horario_fechas";
    protected $fillable = [
        'horario_id',
        'fecha_salida',
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado',
        'domingo'
    ];

    protected $casts = [
        'fecha_salida' => 'datetime:Y-m-d',
    ];
    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
}
