<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerieSucursal extends Model
{
    protected $table = "series_sucursal";
    protected $fillable = [
        "sucursal_id",
        "tipo_documento_factura_id",
        "serie",
    ];

    public const PREFIJOS = [
        1 => 'FF',
        2 => 'BB',
        3 => 'NN',
        4 => 'BC',
        5 => 'FC',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
    public function tipoDocumentoFactura()
    {
        return $this->belongsTo(TipoDocumentoFactura::class, 'tipo_documento_factura_id');
    }
}
