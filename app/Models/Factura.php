<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'empresa_id',
        'serie',
        'correlativo',
        'fecha_emision',

        'cliente_tipo_doc',
        'cliente_numero_doc',
        'cliente_nombre',
        'cliente_direccion',

        'op_gravadas',
        'igv',
        'subtotal',
        'total',
        'valor_venta',

        'sunat_estado',
        'sunat_code',
        'sunat_descripcion',
        'sunat_notes',

        'xml_path',
        'cdr_path',
        'nombre_xml',
    ];

    public function detalles()
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}