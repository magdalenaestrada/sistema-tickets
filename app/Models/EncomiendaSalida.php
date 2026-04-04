<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncomiendaSalida extends Model
{
    protected $table = 'encomienda_salida';

    protected $fillable = [
        'encomienda_id',
        'salida_id',
        'usuario_id',
        'fecha_asignacion',
        'fecha_llegada',
        'estado',
    ];

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class);
    }

    public function salida()
    {
        return $this->belongsTo(Salida::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
