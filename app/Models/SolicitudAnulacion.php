<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudAnulacion extends Model
{
    protected $table = "solicitud_anulaciones";

    protected $fillable = [
        'venta_id',
        'usuario_solicitante_id',
        'usuario_aprobador_id',
        'motivo',
        'motivo_rechazo',
        'estado',
        'fecha_solicitud',
        'fecha_respuesta'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_respuesta' => 'datetime'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'usuario_solicitante_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'usuario_aprobador_id');
    }
}
