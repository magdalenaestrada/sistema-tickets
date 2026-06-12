<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaDetalle extends Model
{
    use HasFactory;

    protected $table = 'caja_detalle';

    protected $fillable = [
        'caja_id',
        'subtipo_movimiento_caja_id',
        'metodo_pago_id',
        'billetera_digital_id',
        'table_name',
        'table_id',
        'amount',
        'description',
        'numero_ticket',
        'anulado'
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'anulado' => 'boolean',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function subtipo()
    {
        return $this->belongsTo(SubtipoMovimientoCaja::class, 'subtipo_movimiento_caja_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

     public function billetera_digital()
    {
        return $this->belongsTo(BilleteraDigital::class, 'billetera_digital_id');
    }

    public function servicio()
    {
        return $this->morphTo(null, 'table_name', 'table_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detalle) {
            if (!empty($detalle->numero_ticket)) {
                return;
            }

            $prefijo = match ($detalle->table_name) {
                'App\Models\Pasaje'     => 'P',
                'App\Models\Encomienda' => 'E',
                default                 => 'X',
            };

            $fecha = now()->format('dmy');

            $count = self::where('table_name', $detalle->table_name)
                ->whereDate('created_at', today())
                ->count();

            $correlativo = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $detalle->numero_ticket = "{$prefijo}-{$fecha}-{$correlativo}";
        });
    }
}
