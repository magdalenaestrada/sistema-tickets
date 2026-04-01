<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    protected $fillable = [
        'factura_id',
        'codigo',
        'descripcion',
        'cantidad',
        'valor_unitario',
        'precio_unitario',
        'base_igv',
        'igv',
        'valor_venta'
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
}
