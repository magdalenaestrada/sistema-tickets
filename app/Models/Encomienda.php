<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encomienda extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'encomienda';

    protected $fillable = [
        'sucursal_id',
        'usuario_id',
        'emisor_persona_id',
        'receptor_persona_id',
        'distrito_id',
        'venta_id',
        'estado',
        'total',
        'fecha_creacion',
        'fecha_procesado',
        'origen',
        'destino',
        'pago_instantaneo',
        'sobre_equipaje',
        'origen_pueblito_id',
        'destino_pueblito_id',
        'transbordo',
        'receptor2_persona_id',
        'observaciones',
        'entregado_por'
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_procesado' => 'datetime',
        'pago_instantaneo' => 'boolean',
        'sobre_equipaje' => 'boolean',
    ];

    public function emisor()
    {
        return $this->belongsTo(Persona::class, 'emisor_persona_id');
    }

    public function receptor()
    {
        return $this->belongsTo(Persona::class, 'receptor_persona_id');
    }

    public function detalles()
    {
        return $this->hasMany(EncomiendaDetalle::class, 'encomienda_id');
    }

    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    protected static function booted()
    {
        static::created(function ($registro) {
            $prefijo = $registro->sobre_equipaje
                ? 'SB'
                : 'EC';

            $registro->codigo = $prefijo . str_pad(
                $registro->id,
                6,
                '0',
                STR_PAD_LEFT
            );

            $registro->saveQuietly();
        });

        static::deleting(function ($encomienda) {
            $encomienda->detalles()->delete();
        });
    }

    public function sucursal_origen()
    {
        return $this->belongsTo(Sucursal::class, 'origen', 'id');
    }

    public function sucursal_destino()
    {
        return $this->belongsTo(Sucursal::class, 'destino', 'id');
    }

    public function receptor2()
    {
        return $this->belongsTo(Persona::class, 'receptor2_persona_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function entregado()
    {
        return $this->belongsTo(User::class, 'entregado_por');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function getTotalPagadoAttribute()
    {
        if (!$this->venta) {
            return 0;
        }

        return $this->venta->pagos->sum('total');
    }

    public function asignacionesSalida()
    {
        return $this->hasMany(EncomiendaSalida::class, 'encomienda_id');
    }

    public function salidaActual()
    {
        return $this->hasOne(EncomiendaSalida::class, 'encomienda_id')
            ->whereIn('estado', ['P', 'A'])
            ->latestOfMany();
    }

    public function salidas()
    {
        return $this->belongsToMany(Salida::class, 'encomienda_salida')
            ->withPivot(['usuario_id', 'fecha_asignacion', 'fecha_llegada', 'estado'])
            ->withTimestamps();
    }

    public function origenPueblito()
    {
        return $this->belongsTo(
            Pueblito::class,
            'origen_pueblito_id'
        );
    }

    public function destinoPueblito()
    {
        return $this->belongsTo(
            Pueblito::class,
            'destino_pueblito_id'
        );
    }

    
}
