<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoFactura extends Model
{
    protected $table = "tipo_documentos_factura";
    protected $fillable = [
        "codigo",
        "descripcion",
        "codigo_sunat",
    ];
}
