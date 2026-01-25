<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioTramo extends Model
{
    protected $table = "horario_tramos";
    protected $fillable = [
        'horario_id',
        'punto_origen_id',
        'punto_destino_id',
        'duracion_minutos',
        'costo_tramo',
        'hora_llegada'
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function origen()
    {
        return $this->belongsTo(HorarioPunto::class, 'punto_origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(HorarioPunto::class, 'punto_destino_id');
    }

    public function pasajes()
    {
        return $this->hasMany(Pasaje::class, 'tramo_id');
    }
}
