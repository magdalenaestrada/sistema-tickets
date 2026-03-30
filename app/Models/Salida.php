<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    protected $table = 'salidas';

    protected $fillable = [
        'horario_id',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function pasajes()
    {
        return $this->hasMany(Pasaje::class);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha
            ? Carbon::parse($this->fecha)->format('d/m/Y')
            : null;
    }

    public function getHoraSalidaAttribute()
    {
        return $this->horario?->hora_formateada;
    }

    public function getHoraLlegadaAttribute()
    {
        return $this->horario?->hora_llegada;
    }
}
