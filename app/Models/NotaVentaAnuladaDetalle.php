<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaVentaAnuladaDetalle extends Model
{
    protected  $table = 'nota_venta_anuladas_detalles';
    protected $fillable = [
        'anulacion_id',
        'venta_detalle_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    public function devolucion()
    {
        return $this->belongsTo(NotaVentaAnulada::class);
    }

    public function ventaDetalle()
    {
        return $this->belongsTo(VentaDetalle::class);
    }
}
