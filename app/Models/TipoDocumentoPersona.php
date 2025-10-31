<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoPersona extends Model
{
    protected $table = "tipo_documento_personas";
    protected $fillable = [
        "codigo",
        "descripcion",
        "codigo_sunat",
        "estado"
    ];
}
