<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComunicacionBaja extends Model
{
    protected $table = 'comunicacion_bajas';

    protected $fillable = [
        'serie',
        'correlativo',
        'venta_id',
        'filename',
        'ticket'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
