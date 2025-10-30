<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'persona_id',
        'area_id',
        'sucursal_id',
        'cargo_id',
        'supervisor_id',
        'tipo_licencia_id',
        'licencia_conducir',
        'fecha_vencimiento_licencia',
        'fecha_ingreso',
        'estado',
    ];

    // Relaciones
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Empleado::class, 'supervisor_id');
    }

    public function tipoLicencia()
    {
        return $this->belongsTo(TipoLicencia::class);
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'empleado_id');
    }
}
