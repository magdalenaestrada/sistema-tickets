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
      
        'amount',
        'description',
        'numero_ticket',
        'anulado',
        'venta_id'
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'anulado' => 'boolean',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
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

}
