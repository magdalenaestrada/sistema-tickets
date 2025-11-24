<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentaPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venta_id',
        'metodo_pago_id',
        'billetera_id',
        'total',
        'estado',
        'fecha_pago',
        'fecha_creacion'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function billetera()
    {
        return $this->belongsTo(BilleteraDigital::class, 'billetera_id');
    }
}
