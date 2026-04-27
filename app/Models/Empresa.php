<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = "empresas";
    protected $fillable = [
        'documento',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'usuario_facturacion',
        'contrasena_facturacion',
        'certificado_path',
        'icon', 
        'igv'
    ];

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
