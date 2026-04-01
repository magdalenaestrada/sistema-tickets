<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personas';

    protected $fillable = [
        'tipo_documento_id',
        'distrito_id',
        'documento',
        'nombres',
        'apellidos',
        'razon_social',
        'telefono',
        'celular',
        'correo',
        'direccion',
        'fecha_nacimiento',
        'estado',
        'fecha_creacion',
        'fecha_inactivacion',
    ];

    protected $dates = [
        'fecha_creacion',
        'fecha_inactivacion',
        'fecha_nacimiento',
        'deleted_at',
    ];

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumentoPersona::class, 'tipo_documento_id');
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'persona_id');
    }

    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    public function encomiendasEmitidas()
    {
        return $this->hasMany(Encomienda::class, 'emisor_persona_id');
    }

    public function encomiendasRecibidas()
    {
        return $this->hasMany(Encomienda::class, 'receptor_persona_id');
    }

    public function getNombreFacturacionAttribute()
    {
        if (!empty($this->razon_social)) {
            return $this->razon_social;
        }

        return trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? ''));
    }
}
