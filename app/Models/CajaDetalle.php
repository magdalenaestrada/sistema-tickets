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
        'table_name',
        'table_id',
        'amount',
        'description',
        'numero_ticket',
        'anulado'
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class);
    }

    public function subtipo()
    {
        return $this->belongsTo(SubtipoMovimientoCaja::class, 'subtipo_movimiento_caja_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }
    public function servicio()
    {
        return $this->morphTo(null, 'table_name', 'table_id');
    }
}
