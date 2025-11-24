<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    use HasFactory;

    protected $table = 'tipo_servicio';

    protected $fillable = [
        'descripcion',
    ];

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'tipo_servicio_id');
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class, 'tipo_servicio_id');
    }
}
