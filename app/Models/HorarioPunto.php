<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioPunto extends Model
{
    use HasFactory;

    protected $table = "horario_puntos";
    protected $fillable = [
        'horario_id',
        'sucursal_id',
        'orden',
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
