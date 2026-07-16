<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaVentaAnulada extends Model
{

    protected $fillable = [
        'venta_id',
        'usuario_id',
        'fecha',
        'total',
        'motivo',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(NotaVentaAnuladaDetalle::class);
    }
}
