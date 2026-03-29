<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Pasaje extends Model
{
    protected $table = 'pasajes';
    protected $fillable = [
        "venta_id",
        "usuario_id",
        "persona_id",
        "pasajero_menor",
        "autorizacion_pdf",
        "asiento_numero",
        "salida_id",
        "estado",
        "fecha_creacion",
        "fecha_inactivacion",
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
}
