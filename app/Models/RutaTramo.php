<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaTramo extends Model
{
    protected $table = "ruta_tramos";
    protected $fillable = [
        'ruta_id',
        'punto_origen_id',
        'punto_destino_id',
        'duracion_minutos',
        'costo_tramo',
    ];

    protected $casts = [
        'duracion_minutos' => 'integer',
        'costo_tramo' => 'float',
    ];

    public function origen()
    {
        return $this->belongsTo(RutaPunto::class)->orderBy('punto_origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(RutaPunto::class)->orderBy('punto_destino_id');
    }
}
