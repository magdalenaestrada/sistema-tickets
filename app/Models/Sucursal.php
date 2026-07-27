<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;
    protected $table = "sucursales";
    protected $fillable = [
        'empresa_id',
        'distrito_id',
        'nombre_comercial',
        'direccion',
        'telefono',
        'estado',
        'venta_otras',
        'serie_id',
        'codigo_sucursal',
        'grupo_serie_id'
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class);
    }
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'sucursal_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'sucursal_id');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function serie()
    {
        return $this->hasMany(SerieSucursal::class, "sucursal_id");
    }

    public function grupoSerie()
    {
        return $this->belongsTo(GrupoSerie::class);
    }

    public function pueblitos()
    {
        return $this->hasMany(Pueblito::class);
    }
}
