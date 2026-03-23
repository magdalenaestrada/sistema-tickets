<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignarHorario extends Model
{
    use HasFactory;

    protected $table = 'asignar_horario_conductor_vehiculo';

    protected $fillable = [
        'horario_id',
        'primer_conductor_id',
        'segundo_conductor_id',
        'vehiculo_id',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    public function primerConductor()
    {
        return $this->belongsTo(Empleado::class, 'primer_conductor_id');
    }

    public function segundoConductor()
    {
        return $this->belongsTo(Empleado::class, 'segundo_conductor_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
    public function encomiendas()
    {
        return $this->hasMany(Encomienda::class, "asignacion_id");
    }
}
