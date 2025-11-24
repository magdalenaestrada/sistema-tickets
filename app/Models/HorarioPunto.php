<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioPunto extends Model
{
    use HasFactory;

    protected $table = "horarios_puntos";
    protected $fillable = [
        'horario_id',
        'origen_id',
        'destino_id',
        'costo_acumulado',
        'orden'
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function origen()
    {
        return $this->belongsTo(Sucursal::class, 'origen_id');
    }

    public function destino()
    {
        return $this->belongsTo(Sucursal::class, 'destino_id');
    }
}
