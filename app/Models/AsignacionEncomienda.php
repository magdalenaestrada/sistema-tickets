<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionEncomienda extends Model
{
    protected $table = 'asignacion_encomiendas';

    protected $fillable = [
        'asignacion_id',
        'encomienda_id'
    ];

    public function asignacion()
    {
        return $this->belongsTo(AsignarHorario::class, 'asignacion_id');
    }

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class, 'encomienda_id');
    }
}
