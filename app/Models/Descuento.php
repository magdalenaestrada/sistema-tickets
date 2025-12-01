<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Descuento extends Model
{
    use HasFactory;

    protected $table = 'descuentos';

    protected $fillable = [
        'codigo',
        'persona_id',
        'cantidad_usos',
        'fecha_maxima',
        'monto_efectivo',
        'porcentaje',
        'activo',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    // Chequeo si el cupón está activo
    public function isActivo(): bool
    {
        return $this->activo &&
            (is_null($this->fecha_maxima) || $this->fecha_maxima >= now());
    }
}
