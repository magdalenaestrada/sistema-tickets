<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrelativoVenta extends Model
{
    use HasFactory;

    protected $table = 'correlativos_venta';

    protected $fillable = [
        'sucursal_id',
        'tipo_documento_factura_id',
        'serie',
        'ultimo_numero',
    ];
}
