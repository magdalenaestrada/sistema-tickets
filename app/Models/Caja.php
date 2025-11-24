<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caja extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'caja';
    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'monto_apertura',
        'monto_cierre',
        'estado',
        'fecha_creacion',
        'fecha_cierre',
        'fecha_arqueo'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_cierre'   => 'datetime',
        'fecha_arqueo'   => 'datetime',
    ];


    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function detalles()
    {
        return $this->hasMany(CajaDetalle::class, 'caja_id');
    }

    public function getTotalIngresosAttribute()
    {
        return $this->detalles()->whereHas('subtipo.tipo_movimiento', function ($q) {
            $q->where('descripcion', 'Ingreso');
        })->sum('amount');
    }

    public function getTotalSalidasAttribute()
    {
        return $this->detalles()->whereHas('subtipo.tipo_movimiento', function ($q) {
            $q->where('descripcion', 'Salida');
        })->sum('amount');
    }

    public function getMontoActualAttribute()
    {
        return $this->monto_apertura + $this->total_ingresos - $this->total_salidas;
    }
}
