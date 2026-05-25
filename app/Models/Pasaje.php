<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Pasaje extends Model
{
    protected $table = 'pasajes';

    protected $fillable = [
        'venta_id',
        'usuario_id',
        'persona_id',
        'pasajero_menor',
        'autorizacion_pdf',
        'asiento_numero',
        'salida_id',
        'origen_pasaje_id',
        'destino_pasaje_id',
        'estado',
        'es_promocion',
        'precio_cobrado',
        'fecha_creacion',
        'fecha_inactivacion',
    ];

    public function tramos()
    {
        return $this->belongsToMany(
            RutaTramo::class,
            'pasaje_tramos',
            'pasaje_id',
            'tramo_id'
        );
    }

    public function salida()
    {
        return $this->belongsTo(Salida::class, "salida_id");
    }
    public function usuario()
    {
        return $this->belongsTo(User::class, "usuario_id");
    }

    public function origen()
    {
        return $this->belongsTo(Pueblito::class, 'origen_pasaje_id');
    }

    public function destino()
    {
        return $this->belongsTo(Pueblito::class, 'destino_pasaje_id');
    }

    public function persona(){
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function venta(){
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
