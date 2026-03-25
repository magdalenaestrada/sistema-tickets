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
        'cantidad_usos',
        'fecha_maxima',
        'monto_efectivo',
        'porcentaje',
        'tipo_cupon_id',
        'activo',
        'tipo_asignacion_id',
        'tipo_descuento_id',
    ];

    public function tipo_cupon()
    {
        return $this->belongsTo(TipoCupon::class, 'tipo_cupon_id');
    }

    public function isActivo(): bool
    {
        return $this->activo &&
            $this->tipo_cupon?->estado &&
            (is_null($this->fecha_maxima) || $this->fecha_maxima >= now());
    }

    public function personas()
    {
        return $this->hasMany(DescuentoPersona::class, "descuento_id");
    }

    public function cargos()
    {
        return $this->hasMany(DescuentoCargo::class, "descuento_id");
    }
}
