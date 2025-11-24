<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioTramo extends Model
{
    protected $table = "horarios_tramos";
    protected $fillable = [
        'horario_id',
        'punto_origen_id',
        'punto_destino_id',
        'costo',
        'orden'
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function origen()
    {
        return $this->belongsTo(Sucursal::class, 'punto_origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(Sucursal::class, 'punto_destino_id');
    }
}
