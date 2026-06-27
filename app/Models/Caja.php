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
        'monto_apertura' => 'decimal:2',
        'monto_cierre'   => 'decimal:2',
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

    public function detallesActivos()
    {
        return $this->hasMany(CajaDetalle::class, 'caja_id')
            ->where(function ($q) {
                $q->whereNull('anulado')->orWhere('anulado', false);
            });
    }

    public function getTotalIngresosAttribute(): float
    {
        return (float) $this->detallesActivos()
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function getTotalSalidasAttribute(): float
    {
        return abs((float) $this->detallesActivos()
            ->where('amount', '<', 0)
            ->sum('amount'));
    }

    public function getMontoActualAttribute(): float
    {
        return (float) $this->monto_apertura + $this->total_ingresos - $this->total_salidas;
    }

    public function ingresosPorMetodoNombre(string $metodo): float
    {
        return (float) $this->detallesActivos()
            ->where('amount', '>', 0)
            ->whereHas('metodoPago', function ($q) use ($metodo) {
                $q->whereRaw('LOWER(descripcion) = ?', [mb_strtolower($metodo)]);
            })
            ->sum('amount');
    }

    public function egresosPorMetodoNombre(string $metodo): float
    {
        return abs((float) $this->detallesActivos()
            ->where('amount', '<', 0)
            ->whereHas('metodoPago', function ($q) use ($metodo) {
                $q->whereRaw('LOWER(descripcion) = ?', [mb_strtolower($metodo)]);
            })
            ->sum('amount'));
    }

    public function getEgresosEfectivoAttribute(): float
    {
        return $this->egresosPorMetodoNombre('Efectivo');
    }

    public function getEfectivoEsperadoAttribute(): float
    {
        return (float) $this->monto_apertura
            + $this->ingresos_efectivo
            - $this->egresos_efectivo;
    }

    public function detalles()
    {
        return $this->hasMany(CajaDetalle::class, 'caja_id');
    }

    public function getIngresosYapeAttribute(): float
    {
        return (float) $this->detallesActivos()
            ->where('billetera_digital_id', 1)
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function getIngresosPlinAttribute(): float
    {
        return (float) $this->detallesActivos()
            ->where('billetera_digital_id', 2)
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function getIngresosTarjetaAttribute(): float
    {
        return (float) $this->detallesActivos()
            ->where('billetera_digital_id', 3)
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function getIngresosTransferenciaAttribute(): float
    {
        return (float) $this->detallesActivos()
            ->where('billetera_digital_id', 4)
            ->where('amount', '>', 0)
            ->sum('amount');
    }
    public function getIngresosEfectivoAttribute(): float
    {
        return (float) $this->detallesActivos()
            ->where('metodo_pago_id', 1)
            ->where('subtipo_movimiento_caja_id', '!=', 10)
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function getMontoCajaAttribute()
    {
        return $this->monto_apertura
            + $this->total_ingresos
            - $this->total_salidas;
    }
}
