<?php

namespace App\Models;

use App\Enums\EstadoVenta;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tipo_servicio_id',
        'sucursal_id',
        'tipo_documento_factura_id',
        'usuario_id',
        'persona_id',
        'serie',
        'numero',
        'documento_referencia',
        'direccion_alternativa',
        'subtotal_sin_igv',
        'subtotal',
        'impuesto',
        'sin_igv',
        'total',
        'total_sin_igv',
        'monto_pagado',
        'observacion',
        'ruta_xml',
        'ruta_pdf',
        'ruta_cdr',
        'hash',
        'estado',
        'fecha_emision',
        'fecha_anulacion',
        'caja_id',
        'venta_referencia_id'
    ];

    protected $casts = [
        'estado' => EstadoVenta::class,
        'fecha_emision' => 'datetime',
        'fecha_anulacion' => 'datetime',
    ];

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function pagos()
    {
        return $this->hasMany(VentaPago::class);
    }

    public function tipoServicio()
    {
        return $this->belongsTo(TipoServicio::class, 'tipo_servicio_id');
    }

    public function cajaDetalles()
    {
        return $this->hasMany(CajaDetalle::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function tipoDocumentoFactura()
    {
        return $this->belongsTo(TipoDocumentoFactura::class, 'tipo_documento_factura_id');
    }
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }
    public function pasajes()
    {
        return $this->hasMany(Pasaje::class);
    }

    public function ventaReferencia()
    {
        return $this->belongsTo(Venta::class, 'venta_referencia_id');
    }
}
