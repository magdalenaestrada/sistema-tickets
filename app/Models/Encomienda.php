<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Encomienda extends Model
{
    use HasFactory;

    protected $table = 'encomienda';

    protected $fillable = [
        'sucursal_id',
        'usuario_id',
        'emisor_persona_id',
        'receptor_persona_id',
        'distrito_id',
        'venta_id',
        'estado',
        'total',
        'fecha_creacion',
        'fecha_procesado',
        'origen',
        'destino',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_procesado' => 'datetime',
    ];

    public function emisor()
    {
        return $this->belongsTo(Persona::class, 'emisor_persona_id');
    }

    public function receptor()
    {
        return $this->belongsTo(Persona::class, 'receptor_persona_id');
    }

    public function detalles()
    {
        return $this->hasMany(EncomiendaDetalle::class, 'encomienda_id');
    }

    protected static function booted()
    {
        static::deleting(function ($encomienda) {
            $encomienda->detalles()->delete();
        });
    }
    public function origen()
    {
        return $this->hasMany(Sucursal::class, 'origen');
    }

    public function destino()
    {
        return $this->hasMany(Sucursal::class, 'destino');
    }
}
