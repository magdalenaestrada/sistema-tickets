<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'descripcion',
        'tipo_servicio_id',
        'descuento',
        'cantidad',
        'precio_venta',
        'total',
        'codigo',
        'unidad',
        'valor_unitario',
        'precio_unitario',
        'base_igv',
        'porcentaje_igv',
        'igv',
        'valor_venta',
        'tipo_afectacion_igv',
        'referencia_type',
        'referencia_id'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function devoluciones()
    {
        return $this->hasMany(NotaVentaAnuladaDetalle::class);
    }
    
    public function referencia()
    {
        return $this->morphTo();
    }
}
