<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCupon extends Model
{
    protected $table = 'tipo_cupones';

    protected $fillable = [
        'descripcion',
        'estado',
    ];

    public function descuentos(){
        return $this->hasMany(Descuento::class);
    }
}
