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
        'sucursal_id',
        'cargo_id',
        'tipo_licencia_id',
        'licencia_conducir',
        'fecha_vencimiento_licencia',
        'fecha_ingreso',
        'estado',
    ];

    protected $dates = ['fecha_nacimiento'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, "persona_id");
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, "sucursal_id");
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, "cargo_id");
    }
    public function tipoLicencia()
    {
        return $this->belongsTo(TipoLicencia::class, "tipo_licencia_id");
    }

    public function usuario()
    {
        return $this->hasOne(User::class, 'persona_id', 'persona_id');
    }
}
