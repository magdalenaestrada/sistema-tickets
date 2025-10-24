<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresas extends Model
{
    protected $table = "empresas";
    protected $fillable = [
        'documento',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'usuario_facturacion',
        'contrasena_facturacion',
    ];
}
