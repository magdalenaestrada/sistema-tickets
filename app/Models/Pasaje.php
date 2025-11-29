<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasaje extends Model
{
    use SoftDeletes;

    protected $table = 'pasajes';

    protected $fillable = [
        'venta_id',
        'usuario_id',
        'persona_id',
        'horario_id',
        'asiento_numero',
        'pasajero_menor',
        'autorizacion_pdf',
        'estado',
        'fecha_creacion',
        'fecha_inactivacion',
    ];

    protected $casts = [
        'pasajero_menor' => 'boolean',
        'fecha_creacion' => 'datetime',
        'fecha_inactivacion' => 'datetime',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
    public function scopePorHorario($query, $horarioId)
    {
        return $query->where('horario_id', $horarioId);
    }
}
