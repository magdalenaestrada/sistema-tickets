<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncomiendaDetalle extends Model
{
    use HasFactory;

    protected $table = 'encomienda_detalle';

    protected $fillable = [
        'encomienda_id',
        'tipo_equipaje',
        'descripcion',
        'peso',
        'costo',
    ];

    public function encomienda()
    {
        return $this->belongsTo(Encomienda::class, 'encomienda_id');
    }
}
