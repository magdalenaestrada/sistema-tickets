<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasajeSobreEquipaje extends Model
{
    protected $table = 'pasaje_sobre_equipajes';

    protected $fillable = [
        'pasaje_id',
        'tipo_encomienda_id',
        'descripcion',
        'peso',
        'costo',
    ];

    public function pasaje()
    {
        return $this->belongsTo(Pasaje::class);
    }

    public function tipoEncomienda()
    {
        return $this->belongsTo(TipoEncomienda::class);
    }
}
